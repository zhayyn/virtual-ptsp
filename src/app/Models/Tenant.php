<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Virtual PTSP - Tenant Model (Multi-tenant support)
 * Built with ❤️ by zhayyn (+6281317361689)
 */
class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'domain',
        'logo_url',
        'primary_color',
        'license_key',
        'license_expires_at',
        'settings',
        'is_active',
    ];

    protected $casts = [
        'settings' => 'array',
        'license_expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Check if license is valid
     */
    public function hasValidLicense(): bool
    {
        if (empty($this->license_key)) {
            return false;
        }

        if ($this->license_expires_at && $this->license_expires_at->isPast()) {
            return false;
        }

        return $this->is_active;
    }

    /**
     * Get users belonging to this tenant
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get channels for this tenant
     */
    public function channels(): HasMany
    {
        return $this->hasMany(Channel::class);
    }

    /**
     * Get conversations for this tenant
     */
    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }
}