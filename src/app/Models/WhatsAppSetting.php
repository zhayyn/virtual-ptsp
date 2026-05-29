<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Virtual PTSP - WhatsApp Settings Model
 * Built with ❤️ by zhayyn (+6281317361689)
 */
class WhatsAppSetting extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_settings';

    protected $fillable = [
        'tenant_id',
        'gateway_url',
        'api_key_encrypted',
        'webhook_url',
        'webhook_secret',
        'session_id',
        'default_channel_id',
        'auto_reply_enabled',
        'ai_config_id',
        'knowledge_base_id',
        'is_active',
    ];

    protected $casts = [
        'auto_reply_enabled' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected $hidden = [
        'api_key_encrypted',
        'webhook_secret',
    ];

    /**
     * Scope for active settings
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get decrypted API key
     */
    public function getDecryptedApiKey(): string
    {
        if (empty($this->api_key_encrypted)) {
            return '';
        }

        try {
            return decrypt($this->api_key_encrypted);
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * Set encrypted API key
     */
    public function setEncryptedApiKey(string $apiKey): void
    {
        $this->api_key_encrypted = encrypt($apiKey);
    }

    /**
     * Tenant relationship
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * AI config relationship
     */
    public function aiConfig(): BelongsTo
    {
        return $this->belongsTo(AiConfig::class, 'ai_config_id');
    }

    /**
     * Knowledge base relationship
     */
    public function knowledgeBase(): BelongsTo
    {
        return $this->belongsTo(KnowledgeBase::class);
    }

    /**
     * Default channel relationship
     */
    public function defaultChannel(): BelongsTo
    {
        return $this->belongsTo(Channel::class, 'default_channel_id');
    }
}

/**
 * Virtual PTSP - WhatsApp Log Model
 * Built with ❤️ by zhayyn (+6281317361689)
 */
class WhatsAppLog extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_logs';

    const UPDATED_AT = null; // This table doesn't have updated_at

    protected $fillable = [
        'tenant_id',
        'user_id',
        'event',
        'phone',
        'message',
        'message_id',
        'status',
        'response',
        'payload',
        'ip_address',
    ];

    protected $casts = [
        'payload' => 'array',
        'response' => 'array',
    ];

    /**
     * User relationship
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Tenant relationship
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get event type badge color
     */
    public function getEventBadgeColor(): string
    {
        return match($this->event) {
            'connected' => 'green',
            'disconnected' => 'red',
            'incoming_message' => 'blue',
            'outgoing_message', 'test_message' => 'purple',
            'error' => 'red',
            default => 'gray',
        };
    }
}