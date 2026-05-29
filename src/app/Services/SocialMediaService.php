<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Virtual PTSP - Social Media Service
 * Built with ❤️ by zhayyn (+6281317361689)
 *
 * Handles connection to various social media platforms:
 * - Instagram Direct Messages
 * - Facebook Messenger
 * - TikTok Direct Messages
 */
class SocialMediaService
{
    /**
     * Supported platforms
     */
    const PLATFORM_INSTAGRAM = 'instagram';
    const PLATFORM_FACEBOOK = 'facebook';
    const PLATFORM_TIKTOK = 'tiktok';

    /**
     * Platform configurations
     */
    private array $config;

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * Set platform configuration
     */
    public function setConfig(string $platform, array $config): self
    {
        $this->config[$platform] = $config;
        return $this;
    }

    /**
     * Get platform config
     */
    private function getPlatformConfig(string $platform): ?array
    {
        return $this->config[$platform] ?? null;
    }

    /**
     * ============================================================
     * INSTAGRAM
     * ============================================================
     */

    /**
     * Get Instagram OAuth URL
     */
    public function getInstagramAuthUrl(string $clientId, string $redirectUri, array $scopes = []): string
    {
        $defaultScopes = ['instagram_basic', 'instagram_manage_messages', 'pages_read_engagement'];
        $scopes = empty($scopes) ? $defaultScopes : $scopes;

        $params = [
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'scope' => implode(',', $scopes),
            'response_type' => 'code',
        ];

        return 'https://www.facebook.com/v18.0/dialog/oauth?' . http_build_query($params);
    }

    /**
     * Exchange Instagram code for access token
     */
    public function getInstagramAccessToken(string $code, string $clientId, string $clientSecret, string $redirectUri): array
    {
        try {
            $response = Http::timeout(30)
                ->post('https://graph.facebook.com/v18.0/oauth/access_token', [
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                    'grant_type' => 'authorization_code',
                    'redirect_uri' => $redirectUri,
                    'code' => $code,
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'access_token' => $response->json('access_token'),
                    'expires_in' => $response->json('expires_in'),
                    'token_type' => $response->json('token_type'),
                ];
            }

            return [
                'success' => false,
                'error' => $response->json('error_message', 'Failed to get access token'),
            ];

        } catch (\Exception $e) {
            Log::error('Instagram token exchange failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get Instagram conversations
     */
    public function getInstagramConversations(string $accessToken, string $igUserId): array
    {
        try {
            $response = Http::timeout(30)
                ->withHeaders(['Authorization' => "Bearer {$accessToken}"])
                ->get("https://graph.facebook.com/v18.0/{$igUserId}/conversations", [
                    'fields' => 'id,updated_time,snippet,snippetSender,participants',
                    'limit' => 20,
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'conversations' => $response->json('data', []),
                    'pagination' => $response->json('paging'),
                ];
            }

            return [
                'success' => false,
                'error' => 'Failed to get conversations',
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get Instagram messages
     */
    public function getInstagramMessages(string $accessToken, string $conversationId): array
    {
        try {
            $response = Http::timeout(30)
                ->withHeaders(['Authorization' => "Bearer {$accessToken}"])
                ->get("https://graph.facebook.com/v18.0/{$conversationId}/messages", [
                    'fields' => 'id,created_time,from,to,message,attachments',
                    'limit' => 50,
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'messages' => $response->json('data', []),
                ];
            }

            return [
                'success' => false,
                'error' => 'Failed to get messages',
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send Instagram message
     */
    public function sendInstagramMessage(string $accessToken, string $igUserId, string $recipientId, string $message): array
    {
        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => "Bearer {$accessToken}",
                    'Content-Type' => 'application/json',
                ])
                ->post("https://graph.facebook.com/v18.0/{$igUserId}/messages", [
                    'recipient' => ['id' => $recipientId],
                    'message' => ['text' => $message],
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message_id' => $response->json('message_id'),
                ];
            }

            return [
                'success' => false,
                'error' => $response->json('error_message', 'Failed to send message'),
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * ============================================================
     * FACEBOOK MESSENGER
     * ============================================================
     */

    /**
     * Get Facebook OAuth URL
     */
    public function getFacebookAuthUrl(string $clientId, string $redirectUri, array $scopes = []): string
    {
        $defaultScopes = ['pages_read_engagement', 'pages_manage_messages', 'pages_manage_metadata'];
        $scopes = empty($scopes) ? $defaultScopes : $scopes;

        $params = [
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'scope' => implode(',', $scopes),
            'response_type' => 'code',
        ];

        return 'https://www.facebook.com/v18.0/dialog/oauth?' . http_build_query($params);
    }

    /**
     * Get Facebook page access token
     */
    public function getFacebookPageToken(string $accessToken, string $pageId): array
    {
        try {
            $response = Http::timeout(30)
                ->withHeaders(['Authorization' => "Bearer {$accessToken}"])
                ->get("https://graph.facebook.com/v18.0/{$pageId}", [
                    'fields' => 'access_token,name,id',
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'page_token' => $response->json('access_token'),
                    'page_name' => $response->json('name'),
                ];
            }

            return [
                'success' => false,
                'error' => 'Failed to get page token',
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send Facebook message
     */
    public function sendFacebookMessage(string $pageAccessToken, string $recipientId, string $message): array
    {
        try {
            $response = Http::timeout(30)
                ->withHeaders(['Authorization' => "Bearer {$pageAccessToken}"])
                ->post('https://graph.facebook.com/v18.0/me/messages', [
                    'recipient' => ['id' => $recipientId],
                    'message' => ['text' => $message],
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message_id' => $response->json('message_id'),
                ];
            }

            return [
                'success' => false,
                'error' => $response->json('error.message', 'Failed to send'),
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * ============================================================
     * TIKTOK
     * ============================================================
     */

    /**
     * Get TikTok OAuth URL
     */
    public function getTikTokAuthUrl(string $clientKey, string $redirectUri, string $state = ''): string
    {
        $params = [
            'client_key' => $clientKey,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'video.create,video.upload',
            'state' => $state,
        ];

        return 'https://www.tiktok.com/v2/auth/authorize?' . http_build_query($params);
    }

    /**
     * Exchange TikTok code for access token
     */
    public function getTikTokAccessToken(string $code, string $clientKey, string $clientSecret, string $redirectUri): array
    {
        try {
            $response = Http::timeout(30)
                ->asForm()
                ->post('https://open.tiktokapis.com/v2/oauth/token/', [
                    'client_key' => $clientKey,
                    'client_secret' => $clientSecret,
                    'code' => $code,
                    'grant_type' => 'authorization_code',
                    'redirect_uri' => $redirectUri,
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'access_token' => $response->json('access_token'),
                    'refresh_token' => $response->json('refresh_token'),
                    'open_id' => $response->json('open_id'),
                    'expires_in' => $response->json('expires_in'),
                ];
            }

            return [
                'success' => false,
                'error' => $response->json('error_description', 'Failed to get token'),
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get TikTok conversations
     */
    public function getTikTokConversations(string $accessToken, string $openId): array
    {
        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => "Bearer {$accessToken}",
                    'Content-Type' => 'application/json',
                ])
                ->post('https://open.tiktokapis.com/v2/im/messages/', [
                    'open_id' => $openId,
                    'max_count' => 20,
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'conversations' => $response->json('data.messages', []),
                ];
            }

            return [
                'success' => false,
                'error' => 'Failed to get conversations',
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send TikTok message
     */
    public function sendTikTokMessage(string $accessToken, string $openId, string $recipientOpenId, string $message): array
    {
        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => "Bearer {$accessToken}",
                    'Content-Type' => 'application/json',
                ])
                ->post('https://open.tiktokapis.com/v2/im/messages/send/', [
                    'open_id' => $openId,
                    'recipient' => ['open_id' => $recipientOpenId],
                    'message_type' => 'text',
                    'content' => $message,
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message_id' => $response->json('message_id'),
                ];
            }

            return [
                'success' => false,
                'error' => $response->json('error_description', 'Failed to send'),
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * ============================================================
     * TELEGRAM
     * ============================================================
     */

    /**
     * Set Telegram bot token
     */
    private string $telegramToken = '';

    public function setTelegramToken(string $token): self
    {
        $this->telegramToken = $token;
        return $this;
    }

    /**
     * Get Telegram updates (messages)
     */
    public function getTelegramUpdates(int $offset = 0, int $limit = 100): array
    {
        try {
            $response = Http::timeout(30)
                ->get("https://api.telegram.org/bot{$this->telegramToken}/getUpdates", [
                    'offset' => $offset,
                    'limit' => $limit,
                    'timeout' => 0,
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'updates' => $response->json('result', []),
                ];
            }

            return [
                'success' => false,
                'error' => 'Failed to get updates',
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send Telegram message
     */
    public function sendTelegramMessage(string $chatId, string $text, array $options = []): array
    {
        try {
            $payload = array_merge([
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'HTML',
            ], $options);

            $response = Http::timeout(30)
                ->post("https://api.telegram.org/bot{$this->telegramToken}/sendMessage", $payload);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message_id' => $response->json('result.message_id'),
                    'chat_id' => $response->json('result.chat.id'),
                ];
            }

            return [
                'success' => false,
                'error' => 'Failed to send message',
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Set Telegram webhook
     */
    public function setTelegramWebhook(string $webhookUrl): array
    {
        try {
            $response = Http::timeout(30)
                ->post("https://api.telegram.org/bot{$this->telegramToken}/setWebhook", [
                    'url' => $webhookUrl,
                ]);

            return [
                'success' => $response->successful(),
                'response' => $response->json(),
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}