<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Virtual PTSP - Channel Model
 * Built with ❤️ by zhayyn (+6281317361689)
 */
class Channel extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'type',
        'name',
        'description',
        'config',
        'credentials_encrypted',
        'is_active',
    ];

    protected $casts = [
        'config' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Channel types
     */
    public const TYPE_WHATSAPP = 'whatsapp';
    public const TYPE_WEBCHAT = 'webchat';
    public const TYPE_INSTAGRAM = 'instagram';
    public const TYPE_FACEBOOK = 'facebook';
    public const TYPE_TIKTOK = 'tiktok';
    public const TYPE_TELEGRAM = 'telegram';

    /**
     * Get channel type options
     */
    public static function getTypes(): array
    {
        return [
            self::TYPE_WHATSAPP => 'WhatsApp',
            self::TYPE_WEBCHAT => 'Web Chat',
            self::TYPE_INSTAGRAM => 'Instagram',
            self::TYPE_FACEBOOK => 'Facebook Messenger',
            self::TYPE_TIKTOK => 'TikTok',
            self::TYPE_TELEGRAM => 'Telegram',
        ];
    }

    /**
     * Get channel type icon
     */
    public static function getTypeIcon(string $type): string
    {
        return match($type) {
            self::TYPE_WHATSAPP => '💬',
            self::TYPE_WEBCHAT => '💻',
            self::TYPE_INSTAGRAM => '📸',
            self::TYPE_FACEBOOK => '👤',
            self::TYPE_TIKTOK => '🎵',
            self::TYPE_TELEGRAM => '✈️',
            default => '💬',
        };
    }

    /**
     * Tenant relationship
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Conversations relationship
     */
    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    /**
     * Get decrypted credentials
     */
    public function getCredentials(): array
    {
        if (empty($this->credentials_encrypted)) {
            return [];
        }

        try {
            return json_decode(decrypt($this->credentials_encrypted), true) ?? [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Set encrypted credentials
     */
    public function setCredentials(array $credentials): void
    {
        $this->credentials_encrypted = encrypt(json_encode($credentials));
    }
}