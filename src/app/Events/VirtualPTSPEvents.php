<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Virtual PTSP - New Message Event
 * Built with ❤️ by zhayyn (+6281317361689)
 */
class NewMessageReceived implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $message;
    public string $conversationId;
    public string $channelType;

    /**
     * Create a new event instance.
     */
    public function __construct(array $message, string $conversationId, string $channelType)
    {
        $this->message = $message;
        $this->conversationId = $conversationId;
        $this->channelType = $channelType;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('conversation.' . $this->conversationId),
            new Channel('channel.' . $this->channelType),
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'message' => $this->message,
            'conversation_id' => $this->conversationId,
            'channel_type' => $this->channelType,
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'new_message';
    }
}

/**
 * Virtual PTSP - Conversation Updated Event
 * Built with ❤️ by zhayyn (+6281317361689)
 */
class ConversationUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $conversationId;
    public array $data;

    public function __construct(string $conversationId, array $data)
    {
        $this->conversationId = $conversationId;
        $this->data = $data;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('conversation.' . $this->conversationId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'conversation_updated';
    }

    public function broadcastWith(): array
    {
        return $this->data;
    }
}

/**
 * Virtual PTSP - WhatsApp Status Changed Event
 * Built with ❤️ by zhayyn (+6281317361689)
 */
class WhatsAppStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public bool $connected;
    public ?string $device;
    public ?int $battery;

    public function __construct(bool $connected, ?string $device = null, ?int $battery = null)
    {
        $this->connected = $connected;
        $this->device = $device;
        $this->battery = $battery;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('whatsapp-status'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'wa_status_changed';
    }

    public function broadcastWith(): array
    {
        return [
            'connected' => $this->connected,
            'device' => $this->device,
            'battery' => $this->battery,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}

/**
 * Virtual PTSP - WebChat New Visitor Event
 * Built with ❤️ by zhayyn (+6281317361689)
 */
class WebChatNewVisitor implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $visitor;
    public string $sessionId;

    public function __construct(array $visitor, string $sessionId)
    {
        $this->visitor = $visitor;
        $this->sessionId = $sessionId;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('operators'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'new_visitor';
    }

    public function broadcastWith(): array
    {
        return [
            'visitor' => $this->visitor,
            'session_id' => $this->sessionId,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}

/**
 * Virtual PTSP - AI Response Ready Event
 * Built with ❤️ by zhayyn (+6281317361689)
 */
class AIResponseReady implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $conversationId;
    public string $response;
    public array $sources;

    public function __construct(string $conversationId, string $response, array $sources = [])
    {
        $this->conversationId = $conversationId;
        $this->response = $response;
        $this->sources = $sources;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('conversation.' . $this->conversationId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'ai_response';
    }

    public function broadcastWith(): array
    {
        return [
            'response' => $this->response,
            'sources' => $this->sources,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}