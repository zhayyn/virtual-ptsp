<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * Virtual PTSP - WhatsApp Baileys Service
 * Built with ❤️ by zhayyn (+6281317361689)
 *
 * Manages WhatsApp connection using Baileys protocol
 * Works as a bridge between your app and WhatsApp Web
 */
class WhatsAppBaileysService
{
    private string $gatewayUrl;
    private string $apiKey;
    private string $sessionId;

    /**
     * Supported WhatsApp events
     */
    const EVENT_QR = 'qr';
    const EVENT_CONNECTED = 'connected';
    const EVENT_DISCONNECTED = 'disconnected';
    const EVENT_MESSAGE = 'message';
    const EVENT_MESSAGE_ACK = 'message_ack';

    /**
     * Message status
     */
    const STATUS_PENDING = 'pending';
    const STATUS_SENT = 'sent';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_READ = 'read';
    const STATUS_ERROR = 'error';

    /**
     * Create new instance
     */
    public function __construct(string $gatewayUrl, string $apiKey, string $sessionId = 'default')
    {
        $this->gatewayUrl = rtrim($gatewayUrl, '/');
        $this->apiKey = $apiKey;
        $this->sessionId = $sessionId;
    }

    /**
     * Get auth headers
     */
    private function getHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    /**
     * Check connection status
     */
    public function getConnectionStatus(): array
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders($this->getHeaders())
                ->get("{$this->gatewayUrl}/status/{$this->sessionId}");

            if ($response->successful()) {
                return [
                    'success' => true,
                    'connected' => $response->json('connected', false),
                    'device' => $response->json('device', null),
                    'logged_in' => $response->json('logged_in', false),
                    'battery' => $response->json('battery', null),
                ];
            }

            return [
                'success' => false,
                'error' => 'Gateway error: ' . $response->status(),
            ];

        } catch (\Exception $e) {
            Log::error('WhatsApp status check failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'connected' => false,
            ];
        }
    }

    /**
     * Start pairing process (get QR code)
     */
    public function startPairing(): array
    {
        try {
            // Check if already connected
            $status = $this->getConnectionStatus();
            if ($status['success'] && ($status['logged_in'] ?? false)) {
                return [
                    'success' => true,
                    'already_connected' => true,
                    'device' => $status['device'] ?? null,
                ];
            }

            // Request new QR code
            $response = Http::timeout(30)
                ->withHeaders($this->getHeaders())
                ->post("{$this->gatewayUrl}/auth/pair", [
                    'session_id' => $this->sessionId,
                ]);

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'success' => true,
                    'qr_code' => $data['qr'] ?? null,
                    'qr_data_url' => $data['qr_data_url'] ?? $this->generateDataUrl($data['qr'] ?? ''),
                    'expires_at' => $data['expires_at'] ?? now()->addSeconds(60)->toIso8601String(),
                ];
            }

            return [
                'success' => false,
                'error' => $response->json('message', 'Failed to generate QR code'),
            ];

        } catch (\Exception $e) {
            Log::error('WhatsApp pairing failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Generate data URL for QR code image
     */
    private function generateDataUrl(string $qr): string
    {
        if (empty($qr)) {
            return '';
        }

        // Generate QR code image as base64
        // This would normally use a QR library, but we'll return the raw QR for frontend to render
        return 'data:image/svg+xml;base64,' . base64_encode(
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200">' .
            '<rect fill="white" width="200" height="200"/>' .
            '<text x="100" y="100" text-anchor="middle" font-size="12">QR: ' . substr($qr, 0, 20) . '...</text>' .
            '</svg>'
        );
    }

    /**
     * Send text message
     */
    public function sendMessage(string $phone, string $text): array
    {
        try {
            // Format phone number (ensure country code)
            $phone = $this->formatPhoneNumber($phone);

            $response = Http::timeout(30)
                ->withHeaders($this->getHeaders())
                ->post("{$this->gatewayUrl}/messages/send", [
                    'session_id' => $this->sessionId,
                    'to' => $phone,
                    'text' => $text,
                ]);

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'success' => true,
                    'message_id' => $data['message_id'] ?? $data['id'] ?? null,
                    'to' => $phone,
                    'status' => self::STATUS_PENDING,
                ];
            }

            return [
                'success' => false,
                'error' => $response->json('message', 'Failed to send message'),
                'error_code' => $response->json('error_code'),
            ];

        } catch (\Exception $e) {
            Log::error('WhatsApp send failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send image message
     */
    public function sendImage(string $phone, string $imageUrl, ?string $caption = null): array
    {
        try {
            $phone = $this->formatPhoneNumber($phone);

            $response = Http::timeout(60)
                ->withHeaders($this->getHeaders())
                ->post("{$this->gatewayUrl}/messages/send", [
                    'session_id' => $this->sessionId,
                    'to' => $phone,
                    'type' => 'image',
                    'image' => [
                        'url' => $imageUrl,
                        'caption' => $caption,
                    ],
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message_id' => $response->json('message_id'),
                    'to' => $phone,
                ];
            }

            return [
                'success' => false,
                'error' => $response->json('message', 'Failed to send image'),
            ];

        } catch (\Exception $e) {
            Log::error('WhatsApp image send failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send reply with quoted message
     */
    public function sendReply(string $phone, string $text, string $quotedMessageId): array
    {
        try {
            $phone = $this->formatPhoneNumber($phone);

            $response = Http::timeout(30)
                ->withHeaders($this->getHeaders())
                ->post("{$this->gatewayUrl}/messages/send", [
                    'session_id' => $this->sessionId,
                    'to' => $phone,
                    'text' => $text,
                    'quoted' => [
                        'id' => $quotedMessageId,
                    ],
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message_id' => $response->json('message_id'),
                ];
            }

            return [
                'success' => false,
                'error' => $response->json('message', 'Failed to send reply'),
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Mark message as read
     */
    public function markAsRead(string $messageId, string $chatId): array
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders($this->getHeaders())
                ->post("{$this->gatewayUrl}/messages/read", [
                    'session_id' => $this->sessionId,
                    'message_id' => $messageId,
                    'chat_id' => $chatId,
                ]);

            return [
                'success' => response()->successful(),
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get chat history
     */
    public function getChatHistory(string $phone, int $limit = 50): array
    {
        try {
            $phone = $this->formatPhoneNumber($phone);

            $response = Http::timeout(30)
                ->withHeaders($this->getHeaders())
                ->get("{$this->gatewayUrl}/chats/{$phone}/messages", [
                    'session_id' => $this->sessionId,
                    'limit' => $limit,
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'messages' => $response->json('messages', []),
                ];
            }

            return [
                'success' => false,
                'error' => 'Failed to get chat history',
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get all contacts
     */
    public function getContacts(): array
    {
        try {
            $response = Http::timeout(30)
                ->withHeaders($this->getHeaders())
                ->get("{$this->gatewayUrl}/contacts", [
                    'session_id' => $this->sessionId,
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'contacts' => $response->json('contacts', []),
                ];
            }

            return [
                'success' => false,
                'error' => 'Failed to get contacts',
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Logout and disconnect
     */
    public function logout(): array
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders($this->getHeaders())
                ->post("{$this->gatewayUrl}/auth/logout", [
                    'session_id' => $this->sessionId,
                ]);

            return [
                'success' => $response->successful(),
                'message' => $response->json('message', 'Logged out'),
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Restart the connection
     */
    public function restart(): array
    {
        try {
            // First logout
            $this->logout();

            // Wait a moment
            sleep(2);

            // Start pairing again
            return $this->startPairing();

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Format phone number to WhatsApp format
     */
    private function formatPhoneNumber(string $phone): string
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // If starts with 0, replace with 62 (Indonesia)
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }

        // If doesn't start with +, add it
        if (!str_starts_with($phone, '62')) {
            $phone = '62' . $phone;
        }

        // Add @s.whatsapp.net for JID format
        return $phone . '@s.whatsapp.net';
    }

    /**
     * Parse phone from JID format
     */
    public static function parsePhoneFromJid(string $jid): string
    {
        return preg_replace('/@.*$/', '', $jid);
    }

    /**
     * Set webhook for incoming events
     */
    public function setWebhook(string $webhookUrl, array $events = []): array
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders($this->getHeaders())
                ->post("{$this->gatewayUrl}/webhooks/set", [
                    'session_id' => $this->sessionId,
                    'url' => $webhookUrl,
                    'events' => $events ?: [
                        self::EVENT_MESSAGE,
                        self::EVENT_CONNECTED,
                        self::EVENT_DISCONNECTED,
                    ],
                ]);

            return [
                'success' => $response->successful(),
                'webhook_id' => $response->json('webhook_id'),
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get QR code as base64 image
     */
    public function getQrAsBase64(): ?string
    {
        // Cache the QR code for polling
        $cacheKey = "wa_qr_{$this->sessionId}";
        $qr = Cache::get($cacheKey);

        if ($qr) {
            return $qr;
        }

        // If connected, no QR needed
        $status = $this->getConnectionStatus();
        if ($status['logged_in'] ?? false) {
            return null;
        }

        // Request new QR
        $result = $this->startPairing();

        if ($result['success'] && !empty($result['qr_code'])) {
            Cache::put($cacheKey, $result['qr_code'], now()->addSeconds(60));
            return $result['qr_code'];
        }

        return null;
    }
}
