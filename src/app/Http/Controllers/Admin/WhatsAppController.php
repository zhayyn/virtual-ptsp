<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhatsAppSetting;
use App\Models\WhatsAppLog;
use App\Services\WhatsAppBaileysService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

/**
 * Virtual PTSP - WhatsApp Admin Controller
 * Built with ❤️ by zhayyn (+6281317361689)
 *
 * Handles:
 * - WhatsApp connection management
 * - QR code scanning interface
 * - Device status monitoring
 * - Message sending dashboard
 */
class WhatsAppController extends Controller
{
    private ?WhatsAppBaileysService $waService = null;

    /**
     * Get WhatsApp service instance
     */
    private function getWaService(): ?WhatsAppBaileysService
    {
        $settings = WhatsAppSetting::active()->first();

        if (!$settings || !$settings->gateway_url) {
            return null;
        }

        return new WhatsAppBaileysService(
            $settings->gateway_url,
            $settings->getDecryptedApiKey(),
            $settings->session_id ?? 'default'
        );
    }

    /**
     * Display WhatsApp dashboard
     */
    public function index(): View
    {
        $settings = WhatsAppSetting::active()->first();
        $service = $this->getWaService();
        $status = $service ? $service->getConnectionStatus() : null;

        // Get recent logs
        $recentLogs = WhatsAppLog::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return view('admin.whatsapp.index', compact(
            'settings',
            'status',
            'recentLogs'
        ));
    }

    /**
     * WhatsApp settings page
     */
    public function settings(): View
    {
        $settings = WhatsAppSetting::active()->first();
        return view('admin.whatsapp.settings', compact('settings'));
    }

    /**
     * Save WhatsApp settings
     */
    public function saveSettings(Request $request): JsonResponse
    {
        $request->validate([
            'gateway_url' => 'required|url',
            'api_key' => 'required|string|min:10',
            'webhook_url' => 'nullable|url',
            'auto_reply_enabled' => 'boolean',
            'ai_config_id' => 'nullable|exists:ai_configs,id',
            'knowledge_base_id' => 'nullable|exists:knowledge_bases,id',
        ]);

        $settings = WhatsAppSetting::active()->first() ?? new WhatsAppSetting();

        $settings->fill([
            'gateway_url' => rtrim($request->gateway_url, '/'),
            'setEncryptedApiKey($request->api_key),
            'webhook_url' => $request->webhook_url,
            'webhook_secret' => $request->webhook_secret ? encrypt($request->webhook_secret) : null,
            'auto_reply_enabled' => $request->boolean('auto_reply_enabled'),
            'ai_config_id' => $request->ai_config_id,
            'knowledge_base_id' => $request->knowledge_base_id,
            'is_active' => true,
        ]);

        $settings->save();

        return response()->json([
            'success' => true,
            'message' => 'WhatsApp settings saved successfully',
        ]);
    }

    /**
     * Get connection status (for polling)
     */
    public function status(): JsonResponse
    {
        $service = $this->getWaService();

        if (!$service) {
            return response()->json([
                'success' => false,
                'error' => 'WhatsApp gateway not configured',
                'connected' => false,
            ]);
        }

        $status = $service->getConnectionStatus();

        return response()->json([
            'success' => $status['success'],
            'connected' => $status['connected'] ?? false,
            'logged_in' => $status['logged_in'] ?? false,
            'device' => $status['device'] ?? null,
            'battery' => $status['battery'] ?? null,
            'error' => $status['error'] ?? null,
        ]);
    }

    /**
     * Get QR code for pairing
     */
    public function qrCode(): JsonResponse
    {
        $service = $this->getWaService();

        if (!$service) {
            return response()->json([
                'success' => false,
                'error' => 'WhatsApp gateway not configured',
            ]);
        }

        // Check if already connected
        $status = $service->getConnectionStatus();

        if ($status['logged_in'] ?? false) {
            return response()->json([
                'success' => true,
                'already_connected' => true,
                'device' => $status['device'] ?? null,
                'qr_code' => null,
            ]);
        }

        // Get new QR code
        $result = $service->startPairing();

        // Cache QR code for refresh
        Cache::put('wa_qr_pending', true, now()->addSeconds(60));

        return response()->json([
            'success' => $result['success'],
            'qr_code' => $result['qr_code'] ?? null,
            'qr_data_url' => $result['qr_data_url'] ?? null,
            'expires_at' => $result['expires_at'] ?? null,
            'already_connected' => false,
        ]);
    }

    /**
     * Disconnect WhatsApp
     */
    public function disconnect(): JsonResponse
    {
        $service = $this->getWaService();

        if (!$service) {
            return response()->json([
                'success' => false,
                'error' => 'WhatsApp gateway not configured',
            ]);
        }

        $result = $service->logout();

        // Log the action
        WhatsAppLog::create([
            'event' => 'disconnect',
            'message' => 'WhatsApp disconnected by admin',
            'ip_address' => request()->ip(),
            'user_id' => auth()->id(),
        ]);

        // Clear QR cache
        Cache::forget('wa_qr_pending');

        return response()->json([
            'success' => $result['success'],
            'message' => 'WhatsApp disconnected successfully',
        ]);
    }

    /**
     * Restart WhatsApp connection
     */
    public function restart(): JsonResponse
    {
        $service = $this->getWaService();

        if (!$service) {
            return response()->json([
                'success' => false,
                'error' => 'WhatsApp gateway not configured',
            ]);
        }

        $result = $service->restart();

        // Log the action
        WhatsAppLog::create([
            'event' => 'restart',
            'message' => 'WhatsApp connection restarted',
            'ip_address' => request()->ip(),
            'user_id' => auth()->id(),
        ]);

        return response()->json([
            'success' => $result['success'],
            'qr_code' => $result['qr_code'] ?? null,
            'error' => $result['error'] ?? null,
        ]);
    }

    /**
     * Send test message
     */
    public function sendTest(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => 'required|string|min:8',
            'message' => 'required|string|max:1000',
        ]);

        $service = $this->getWaService();

        if (!$service) {
            return response()->json([
                'success' => false,
                'error' => 'WhatsApp gateway not configured',
            ]);
        }

        // Check connection first
        $status = $service->getConnectionStatus();

        if (!($status['logged_in'] ?? false)) {
            return response()->json([
                'success' => false,
                'error' => 'WhatsApp not connected. Please scan QR code first.',
            ]);
        }

        $result = $service->sendMessage($request->phone, $request->message);

        // Log the action
        WhatsAppLog::create([
            'event' => 'test_message',
            'phone' => $request->phone,
            'message' => $request->message,
            'status' => $result['success'] ? 'success' : 'failed',
            'response' => json_encode($result),
            'ip_address' => request()->ip(),
            'user_id' => auth()->id(),
        ]);

        return response()->json([
            'success' => $result['success'],
            'message_id' => $result['message_id'] ?? null,
            'error' => $result['error'] ?? null,
        ]);
    }

    /**
     * Get chat list
     */
    public function chats(): JsonResponse
    {
        $service = $this->getWaService();

        if (!$service) {
            return response()->json([
                'success' => false,
                'error' => 'WhatsApp gateway not configured',
            ]);
        }

        $contacts = $service->getContacts();

        return response()->json([
            'success' => $contacts['success'],
            'contacts' => $contacts['contacts'] ?? [],
        ]);
    }

    /**
     * Get chat history
     */
    public function chatHistory(Request $request, string $phone): JsonResponse
    {
        $request->validate([
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        $service = $this->getWaService();

        if (!$service) {
            return response()->json([
                'success' => false,
                'error' => 'WhatsApp gateway not configured',
            ]);
        }

        $messages = $service->getChatHistory($phone, $request->limit ?? 50);

        return response()->json([
            'success' => $messages['success'],
            'messages' => $messages['messages'] ?? [],
        ]);
    }

    /**
     * Webhook handler for incoming messages
     */
    public function webhook(Request $request): JsonResponse
    {
        $settings = WhatsAppSetting::active()->first();

        // Verify webhook secret if set
        if ($settings && $settings->webhook_secret) {
            $secret = decrypt($settings->webhook_secret);
            if ($request->header('X-Webhook-Secret') !== $secret) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        }

        $payload = $request->all();

        // Log incoming webhook
        WhatsAppLog::create([
            'event' => 'webhook',
            'payload' => json_encode($payload),
            'ip_address' => $request->ip(),
        ]);

        // Process based on event type
        if (isset($payload['event'])) {
            match($payload['event']) {
                'message' => $this->handleIncomingMessage($payload),
                'connected' => $this->handleConnected($payload),
                'disconnected' => $this->handleDisconnected($payload),
                default => null,
            };
        }

        return response()->json(['success' => true]);
    }

    /**
     * Handle incoming message webhook
     */
    private function handleIncomingMessage(array $payload): void
    {
        // Create conversation if not exists
        // This would integrate with the Conversation model
        // For now, just log it

        WhatsAppLog::create([
            'event' => 'incoming_message',
            'phone' => $payload['from'] ?? null,
            'message' => $payload['message'] ?? null,
            'message_id' => $payload['message_id'] ?? null,
        ]);

        // TODO: Create Conversation and Message records
        // TODO: Trigger AI auto-reply if enabled
    }

    /**
     * Handle connected event
     */
    private function handleConnected(array $payload): void
    {
        WhatsAppLog::create([
            'event' => 'connected',
            'message' => 'WhatsApp connected: ' . ($payload['device'] ?? 'Unknown'),
            'payload' => json_encode($payload),
        ]);
    }

    /**
     * Handle disconnected event
     */
    private function handleDisconnected(array $payload): void
    {
        WhatsAppLog::create([
            'event' => 'disconnected',
            'message' => 'WhatsApp disconnected: ' . ($payload['reason'] ?? 'Unknown'),
            'payload' => json_encode($payload),
        ]);
    }
}
