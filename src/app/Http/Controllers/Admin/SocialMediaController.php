<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Channel;
use App\Services\SocialMediaService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Cache;

/**
 * Virtual PTSP - Social Media Controller
 * Built with ❤️ by zhayyn (+6281317361689)
 *
 * Handles:
 * - Instagram connection and DM management
 * - Facebook Messenger connection
 * - Telegram bot configuration
 * - TikTok DM integration
 */
class SocialMediaController extends Controller
{
    private SocialMediaService $socialService;

    public function __construct()
    {
        $this->socialService = new SocialMediaService();
    }

    /**
     * Display social media dashboard
     */
    public function index(): View
    {
        // Get all social media channels
        $channels = Channel::whereIn('type', ['instagram', 'facebook', 'telegram', 'tiktok'])
            ->with('tenant')
            ->get()
            ->groupBy('type');

        $stats = [
            'instagram' => $channels->get('instagram', collect())->count(),
            'facebook' => $channels->get('facebook', collect())->count(),
            'telegram' => $channels->get('telegram', collect())->count(),
            'tiktok' => $channels->get('tiktok', collect())->count(),
        ];

        return view('admin.social.index', compact('channels', 'stats'));
    }

    /**
     * Social media settings page
     */
    public function settings(Request $request): View
    {
        $platform = $request->get('platform', 'instagram');
        $channel = Channel::where('type', $platform)->first();

        return view('admin.social.settings', compact('platform', 'channel'));
    }

    /**
     * Save social media settings
     */
    public function saveSettings(Request $request): JsonResponse
    {
        $request->validate([
            'platform' => 'required|in:instagram,facebook,telegram,tiktok',
            'config' => 'required|array',
        ]);

        $platform = $request->platform;
        $config = $request->config;

        // Find or create channel
        $channel = Channel::firstOrCreate(
            ['type' => $platform],
            ['name' => ucfirst($platform), 'is_active' => false]
        );

        // Store encrypted credentials
        $channel->setCredentials($config);
        $channel->config = $config;
        $channel->is_active = $request->boolean('is_active', false);
        $channel->save();

        return response()->json([
            'success' => true,
            'message' => "{$platform} settings saved successfully",
        ]);
    }

    // ============================================================
    // INSTAGRAM
    // ============================================================

    /**
     * Initiate Instagram OAuth connection
     */
    public function instagramConnect(): JsonResponse
    {
        $config = [
            'client_id' => config('services.instagram.client_id'),
            'client_secret' => config('services.instagram.client_secret'),
            'redirect_uri' => route('social.instagram.callback'),
        ];

        if (empty($config['client_id'])) {
            return response()->json([
                'success' => false,
                'error' => 'Instagram app credentials not configured',
                'action' => 'Configure in Settings → Social Media → Instagram',
            ], 400);
        }

        $authUrl = $this->socialService->getInstagramAuthUrl(
            $config['client_id'],
            $config['redirect_uri']
        );

        return response()->json([
            'success' => true,
            'auth_url' => $authUrl,
        ]);
    }

    /**
     * Handle Instagram OAuth callback
     */
    public function instagramCallback(Request $request): JsonResponse
    {
        if ($request->has('error')) {
            return response()->json([
                'success' => false,
                'error' => $request->get('error_description', 'Authorization failed'),
            ], 400);
        }

        $code = $request->get('code');

        $result = $this->socialService->getInstagramAccessToken(
            $code,
            config('services.instagram.client_id'),
            config('services.instagram.client_secret'),
            route('social.instagram.callback')
        );

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'error' => $result['error'],
            ], 400);
        }

        // Store the access token
        $channel = Channel::firstOrCreate(
            ['type' => 'instagram'],
            ['name' => 'Instagram', 'is_active' => true]
        );

        $channel->setCredentials([
            'access_token' => $result['access_token'],
            'token_type' => $result['token_type'],
            'expires_in' => $result['expires_in'] ?? null,
        ]);

        // Get IG User ID
        $userId = $this->getInstagramUserId($result['access_token']);
        if ($userId) {
            $channel->config = array_merge($channel->config ?? [], ['ig_user_id' => $userId]);
        }

        $channel->is_active = true;
        $channel->save();

        return response()->json([
            'success' => true,
            'message' => 'Instagram connected successfully!',
        ]);
    }

    /**
     * Disconnect Instagram
     */
    public function instagramDisconnect(): JsonResponse
    {
        $channel = Channel::where('type', 'instagram')->first();

        if ($channel) {
            $channel->is_active = false;
            $channel->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Instagram disconnected',
        ]);
    }

    /**
     * Get Instagram User ID
     */
    private function getInstagramUserId(string $accessToken): ?string
    {
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(10)
                ->withHeaders(['Authorization' => "Bearer {$accessToken}"])
                ->get('https://graph.facebook.com/v18.0/me/accounts');

            if ($response->successful()) {
                $pages = $response->json('data', []);
                if (!empty($pages)) {
                    return $pages[0]['id'];
                }
            }
        } catch (\Exception $e) {
            // Ignore
        }

        return null;
    }

    /**
     * Instagram webhook handler
     */
    public function instagramWebhook(Request $request): JsonResponse
    {
        // Verify webhook
        $mode = $request->get('hub_mode');
        $token = $request->get('hub_verify_token');
        $challenge = $request->get('hub_challenge');

        if ($mode === 'subscribe' && $token === config('services.instagram.verify_token')) {
            return response()->setContent($challenge)->send();
        }

        // Handle incoming messages
        $entry = $request->get('entry', []);
        foreach ($entry as $e) {
            $messaging = $e['messaging'] ?? [];
            foreach ($messaging as $msg) {
                $this->handleIncomingMessage('instagram', $msg);
            }
        }

        return response()->json(['success' => true]);
    }

    // ============================================================
    // FACEBOOK MESSENGER
    // ============================================================

    /**
     * Initiate Facebook OAuth connection
     */
    public function facebookConnect(): JsonResponse
    {
        $config = [
            'client_id' => config('services.facebook.client_id'),
            'client_secret' => config('services.facebook.client_secret'),
            'redirect_uri' => route('social.facebook.callback'),
        ];

        if (empty($config['client_id'])) {
            return response()->json([
                'success' => false,
                'error' => 'Facebook app credentials not configured',
                'action' => 'Configure in Settings → Social Media → Facebook',
            ], 400);
        }

        $authUrl = $this->socialService->getFacebookAuthUrl(
            $config['client_id'],
            $config['redirect_uri']
        );

        return response()->json([
            'success' => true,
            'auth_url' => $authUrl,
        ]);
    }

    /**
     * Handle Facebook OAuth callback
     */
    public function facebookCallback(Request $request): JsonResponse
    {
        if ($request->has('error')) {
            return response()->json([
                'success' => false,
                'error' => $request->get('error_description', 'Authorization failed'),
            ], 400);
        }

        $code = $request->get('code');

        // Exchange code for access token
        $response = \Illuminate\Support\Facades\Http::timeout(30)
            ->post('https://graph.facebook.com/v18.0/oauth/access_token', [
                'client_id' => config('services.facebook.client_id'),
                'client_secret' => config('services.facebook.client_secret'),
                'redirect_uri' => route('social.facebook.callback'),
                'code' => $code,
                'grant_type' => 'authorization_code',
            ]);

        if (!$response->successful()) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to get access token',
            ], 400);
        }

        $data = $response->json();

        // Get page access token
        $pageResult = $this->socialService->getFacebookPageToken(
            $data['access_token'],
            config('services.facebook.page_id')
        );

        if (!$pageResult['success']) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to get page token',
            ], 400);
        }

        // Save channel
        $channel = Channel::firstOrCreate(
            ['type' => 'facebook'],
            ['name' => 'Facebook Messenger', 'is_active' => true]
        );

        $channel->setCredentials([
            'page_access_token' => $pageResult['page_token'],
            'page_name' => $pageResult['page_name'],
        ]);

        $channel->is_active = true;
        $channel->save();

        return response()->json([
            'success' => true,
            'message' => 'Facebook Messenger connected successfully!',
        ]);
    }

    /**
     * Disconnect Facebook
     */
    public function facebookDisconnect(): JsonResponse
    {
        $channel = Channel::where('type', 'facebook')->first();

        if ($channel) {
            $channel->is_active = false;
            $channel->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Facebook Messenger disconnected',
        ]);
    }

    /**
     * Facebook webhook handler
     */
    public function facebookWebhook(Request $request): JsonResponse
    {
        // Verify webhook
        $mode = $request->get('hub_mode');
        $token = $request->get('hub_verify_token');
        $challenge = $request->get('hub_challenge');

        if ($mode === 'subscribe' && $token === config('services.facebook.verify_token')) {
            return response()->setContent($challenge)->send();
        }

        // Handle incoming messages
        $entry = $request->get('entry', []);
        foreach ($entry as $e) {
            $messaging = $e['messaging'] ?? [];
            foreach ($messaging as $msg) {
                $this->handleIncomingMessage('facebook', $msg);
            }
        }

        return response()->json(['success' => true]);
    }

    // ============================================================
    // TELEGRAM
    // ============================================================

    /**
     * Connect Telegram bot
     */
    public function telegramConnect(Request $request): JsonResponse
    {
        $request->validate([
            'bot_token' => 'required|string',
        ]);

        $token = $request->bot_token;

        // Verify bot token by getting bot info
        $response = \Illuminate\Support\Facades\Http::timeout(10)
            ->get("https://api.telegram.org/bot{$token}/getMe");

        if (!$response->successful()) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid bot token',
            ], 400);
        }

        $botInfo = $response->json();

        // Save channel
        $channel = Channel::firstOrCreate(
            ['type' => 'telegram'],
            ['name' => "Telegram: @{$botInfo['result']['username']}"]
        );

        $channel->setCredentials([
            'bot_token' => $token,
            'bot_name' => $botInfo['result']['first_name'],
            'bot_username' => $botInfo['result']['username'],
        ]);

        $channel->is_active = true;
        $channel->save();

        // Set webhook
        $this->socialService->setTelegramToken($token);
        $webhookUrl = route('webhooks.telegram');
        $this->socialService->setTelegramWebhook($webhookUrl);

        return response()->json([
            'success' => true,
            'message' => "Telegram bot @{$botInfo['result']['username']} connected!",
        ]);
    }

    /**
     * Disconnect Telegram
     */
    public function telegramDisconnect(): JsonResponse
    {
        $channel = Channel::where('type', 'telegram')->first();

        if ($channel) {
            $channel->is_active = false;
            $channel->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Telegram disconnected',
        ]);
    }

    /**
     * Telegram webhook handler
     */
    public function telegramWebhook(Request $request): JsonResponse
    {
        $update = $request->all();

        if (isset($update['message'])) {
            $message = $update['message'];
            $chatId = $message['chat']['id'];
            $text = $message['text'] ?? '';

            // Handle incoming message
            $this->handleIncomingMessage('telegram', [
                'from' => ['id' => $chatId],
                'message' => ['text' => $text],
            ]);
        }

        return response()->json(['success' => true]);
    }

    // ============================================================
    // SHARED METHODS
    // ============================================================

    /**
     * Handle incoming message from any platform
     */
    private function handleIncomingMessage(string $platform, array $message): void
    {
        $channel = Channel::where('type', $platform)->first();

        if (!$channel) {
            return;
        }

        $senderId = $message['sender']['id'] ?? $message['from']['id'] ?? null;
        $messageText = $message['message']['text'] ?? $message['text'] ?? '';

        if (!$senderId || !$messageText) {
            return;
        }

        // Create or get conversation
        $conversation = \App\Models\Conversation::firstOrCreate([
            'channel_id' => $channel->id,
            'channel_conversation_id' => $senderId,
        ], [
            'channel_type' => $platform,
            'contact_name' => "{$platform}_user_{$senderId}",
            'contact_number' => $senderId,
            'status' => 'open',
        ]);

        // Save message
        \App\Models\Message::create([
            'conversation_id' => $conversation->id,
            'direction' => 'inbound',
            'content' => $messageText,
            'content_type' => 'text',
            'status' => 'delivered',
            'sent_via' => $platform,
        ]);

        // Update conversation
        $conversation->update([
            'last_message_at' => now(),
            'last_message_preview' => $messageText,
        ]);
    }

    /**
     * Send message via platform
     */
    public function sendMessage(Request $request): JsonResponse
    {
        $request->validate([
            'platform' => 'required|in:instagram,facebook,telegram',
            'recipient_id' => 'required|string',
            'message' => 'required|string',
        ]);

        $channel = Channel::where('type', $request->platform)->first();

        if (!$channel || !$channel->is_active) {
            return response()->json([
                'success' => false,
                'error' => "{$request->platform} is not connected",
            ], 400);
        }

        $credentials = $channel->getCredentials();

        $result = match($request->platform) {
            'instagram' => $this->socialService
                ->setConfig('instagram', $credentials)
                ->sendInstagramMessage(
                    $credentials['access_token'],
                    $credentials['ig_user_id'],
                    $request->recipient_id,
                    $request->message
                ),
            'facebook' => $this->socialService
                ->setConfig('facebook', $credentials)
                ->sendFacebookMessage(
                    $credentials['page_access_token'],
                    $request->recipient_id,
                    $request->message
                ),
            'telegram' => $this->socialService
                ->setTelegramToken($credentials['bot_token'])
                ->sendTelegramMessage($request->recipient_id, $request->message),
            default => ['success' => false, 'error' => 'Unsupported platform'],
        };

        if ($result['success']) {
            // Save outbound message
            $conversation = \App\Models\Conversation::where('channel_conversation_id', $request->recipient_id)->first();

            if ($conversation) {
                \App\Models\Message::create([
                    'conversation_id' => $conversation->id,
                    'direction' => 'outbound',
                    'content' => $request->message,
                    'content_type' => 'text',
                    'status' => 'sent',
                    'sent_via' => 'dashboard',
                ]);
            }
        }

        return response()->json($result);
    }
}