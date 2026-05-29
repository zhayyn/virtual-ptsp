<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\WebChatSession;
use App\Models\WebChatWidget;
use App\Models\AiConfig;
use App\Services\AiServiceFactory;
use App\Services\KnowledgeBaseService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Virtual PTSP - WebChat API Controller
 * Built with ❤️ by zhayyn (+6281317361689)
 *
 * Handles:
 * - Web chat widget embedding
 * - Visitor sessions
 * - Real-time chat via WebSocket (Laravel Reverb)
 */
class WebChatController extends Controller
{
    /**
     * Get or create a visitor session
     */
    public function getSession(Request $request): JsonResponse
    {
        $sessionId = $request->header('X-Session-ID') ?? $request->input('session_id');

        // If no session, create new one
        if (!$sessionId) {
            $sessionId = Str::uuid()->toString();

            $session = WebChatSession::create([
                'session_id' => $sessionId,
                'visitor_ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'page_url' => $request->input('page_url'),
                'referrer' => $request->input('referrer'),
            ]);

            return response()->json([
                'success' => true,
                'session_id' => $sessionId,
                'is_new' => true,
            ]);
        }

        // Find existing session
        $session = WebChatSession::where('session_id', $sessionId)->first();

        if (!$session) {
            // Create new if not found
            $session = WebChatSession::create([
                'session_id' => $sessionId,
                'visitor_ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json([
                'success' => true,
                'session_id' => $sessionId,
                'is_new' => true,
            ]);
        }

        return response()->json([
            'success' => true,
            'session_id' => $sessionId,
            'is_new' => false,
            'has_active_conversation' => !is_null($session->conversation_id),
        ]);
    }

    /**
     * Send a chat message
     */
    public function chat(Request $request): JsonResponse
    {
        $request->validate([
            'session_id' => 'required|string',
            'message' => 'required|string|max:5000',
            'visitor_name' => 'nullable|string|max:255',
        ]);

        $sessionId = $request->session_id;
        $messageText = $request->message;

        // Get or create session
        $session = WebChatSession::where('session_id', $sessionId)->first();

        if (!$session) {
            $session = WebChatSession::create([
                'session_id' => $sessionId,
                'visitor_ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'visitor_name' => $request->visitor_name,
            ]);
        }

        // Update visitor info
        if ($request->visitor_name) {
            $session->visitor_name = $request->visitor_name;
            $session->save();
        }

        // Get or create conversation
        $channel = \App\Models\Channel::where('type', 'webchat')->first();

        if (!$channel) {
            // Create default webchat channel
            $channel = \App\Models\Channel::create([
                'type' => 'webchat',
                'name' => 'Web Chat',
                'is_active' => true,
            ]);
        }

        $conversation = $session->conversation;

        if (!$conversation) {
            $conversation = Conversation::create([
                'channel_id' => $channel->id,
                'channel_type' => 'webchat',
                'channel_conversation_id' => $sessionId,
                'contact_name' => $session->visitor_name ?? 'Web Visitor',
                'contact_number' => $session->visitor_ip,
                'status' => 'open',
            );

            $session->conversation_id = $conversation->id;
            $session->save();
        }

        // Save user message
        $userMessage = Message::create([
            'conversation_id' => $conversation->id,
            'direction' => 'inbound',
            'content' => $messageText,
            'content_type' => 'text',
            'status' => 'delivered',
            'sent_via' => 'webchat',
        ]);

        // Update conversation
        $conversation->update([
            'last_message_at' => now(),
            'last_message_preview' => Str::limit($messageText, 100),
        ]);

        // Check if AI auto-reply is enabled
        $settings = \App\Models\WhatsAppSetting::active()->first();
        $aiResponse = null;

        if ($settings && $settings->auto_reply_enabled && $settings->ai_config_id) {
            $aiResponse = $this->generateAIResponse($messageText, $settings);
        }

        // If no AI response, return waiting status
        if (!$aiResponse) {
            return response()->json([
                'success' => true,
                'message_id' => $userMessage->id,
                'status' => 'waiting',
                'message' => 'Pesan Anda telah diterima. Tim kami akan segera merespons.',
            ]);
        }

        // Save AI response
        $aiMessage = Message::create([
            'conversation_id' => $conversation->id,
            'direction' => 'outbound',
            'content' => $aiResponse['answer'],
            'content_type' => 'text',
            'status' => 'sent',
            'sent_via' => 'ai_auto',
        ]);

        return response()->json([
            'success' => true,
            'message_id' => $userMessage->id,
            'ai_response' => [
                'id' => $aiMessage->id,
                'content' => $aiResponse['answer'],
                'sources' => $aiResponse['sources'] ?? [],
            ],
        ]);
    }

    /**
     * Generate AI response with RAG
     */
    private function generateAIResponse(string $question, $settings): ?array
    {
        try {
            $aiConfig = \App\Models\AiConfig::find($settings->ai_config_id);
            $knowledgeBase = \App\Models\KnowledgeBase::find($settings->knowledge_base_id);

            if (!$aiConfig || !$knowledgeBase) {
                return null;
            }

            // Create AI service instance
            $aiService = new AiServiceFactory();
            $aiService->setProvider($aiConfig->provider->slug)
                ->setApiKey(decrypt($aiConfig->api_key_encrypted))
                ->setModel($aiConfig->selected_model)
                ->setSettings($aiConfig->settings);

            // Use knowledge base service for RAG
            $kbService = new KnowledgeBaseService($aiService);
            $result = $kbService->generateWithRag($knowledgeBase, $question, $aiService);

            if ($result['success']) {
                return [
                    'answer' => $result['answer'],
                    'sources' => $result['sources'] ?? [],
                ];
            }

            return null;

        } catch (\Exception $e) {
            Log::error('AI response generation failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get conversation messages
     */
    public function getMessages(Request $request, string $sessionId): JsonResponse
    {
        $session = WebChatSession::where('session_id', $sessionId)->first();

        if (!$session || !$session->conversation) {
            return response()->json([
                'success' => false,
                'error' => 'Session not found',
            ]);
        }

        $messages = Message::where('conversation_id', $session->conversation_id)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($msg) {
                return [
                    'id' => $msg->id,
                    'content' => $msg->content,
                    'direction' => $msg->direction,
                    'created_at' => $msg->created_at->toIso8601String(),
                ];
            });

        return response()->json([
            'success' => true,
            'messages' => $messages,
        ]);
    }

    /**
     * Get widget configuration
     */
    public function getWidgetConfig(Request $request): JsonResponse
    {
        $tenantId = $request->input('tenant_id');

        $widget = WebChatWidget::where('tenant_id', $tenantId)
            ->orWhereNull('tenant_id')
            ->where('is_active', true)
            ->first();

        if (!$widget) {
            // Return default config
            return response()->json([
                'success' => true,
                'config' => [
                    'theme' => 'dark',
                    'position' => 'bottom-right',
                    'primary_color' => '#6366F1',
                    'welcome_message' => 'Halo! Ada yang bisa kami bantu?',
                    'ai_enabled' => true,
                ],
            ]);
        }

        return response()->json([
            'success' => true,
            'config' => $widget->config,
        ]);
    }
}
