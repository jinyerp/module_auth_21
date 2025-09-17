<?php

namespace Jiny\Auth\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Blacklist extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'blacklists';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'type',
        'value',
        'account_id',
        'reason',
        'description',
        'added_by',
        'expires_at',
        'is_active',
        'hit_count',
        'last_hit_at',
        'meta',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'expires_at' => 'datetime',
        'last_hit_at' => 'datetime',
        'is_active' => 'boolean',
        'hit_count' => 'integer',
        'meta' => 'array',
    ];

    /**
     * Get the account associated with the blacklist entry.
     */
    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Get the account who added this blacklist entry.
     */
    public function addedBy()
    {
        return $this->belongsTo(Account::class, 'added_by');
    }

    /**
     * Scope a query to only include active blacklist entries.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    /**
     * Scope a query to only include expired blacklist entries.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeExpired($query)
    {
        return $query->whereNotNull('expires_at')
            ->where('expires_at', '<=', now());
    }

    /**
     * Scope a query to filter by type.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Check if a value is blacklisted.
     *
     * @param string $type
     * @param string $value
     * @return bool
     */
    public static function isBlacklisted(string $type, string $value): bool
    {
        $entry = self::active()
            ->where('type', $type)
            ->where('value', $value)
            ->first();

        if ($entry) {
            // Record the hit
            $entry->recordHit();
            return true;
        }

        return false;
    }

    /**
     * Check if an email is blacklisted.
     *
     * @param string $email
     * @return bool
     */
    public static function isEmailBlacklisted(string $email): bool
    {
        // Check exact email
        if (self::isBlacklisted('email', $email)) {
            return true;
        }

        // Check domain
        $domain = substr(strrchr($email, "@"), 1);
        if ($domain && self::isBlacklisted('domain', $domain)) {
            return true;
        }

        return false;
    }

    /**
     * Check if an IP is blacklisted.
     *
     * @param string $ip
     * @return bool
     */
    public static function isIpBlacklisted(string $ip): bool
    {
        return self::isBlacklisted('ip', $ip);
    }

    /**
     * Check if a phone number is blacklisted.
     *
     * @param string $phone
     * @return bool
     */
    public static function isPhoneBlacklisted(string $phone): bool
    {
        return self::isBlacklisted('phone', $phone);
    }

    /**
     * Record a hit on this blacklist entry.
     *
     * @return void
     */
    public function recordHit(): void
    {
        $this->increment('hit_count');
        $this->update(['last_hit_at' => now()]);
    }

    /**
     * Add a value to the blacklist.
     *
     * @param string $type
     * @param string $value
     * @param string $reason
     * @param array $options
     * @return static
     */
    public static function add(string $type, string $value, string $reason, array $options = []): self
    {
        return self::create([
            'type' => $type,
            'value' => $value,
            'reason' => $reason,
            'description' => $options['description'] ?? null,
            'account_id' => $options['account_id'] ?? null,
            'added_by' => $options['added_by'] ?? auth()->id(),
            'expires_at' => $options['expires_at'] ?? null,
            'is_active' => $options['is_active'] ?? true,
            'meta' => $options['meta'] ?? null,
        ]);
    }

    /**
     * Remove a value from the blacklist.
     *
     * @param string $type
     * @param string $value
     * @return bool
     */
    public static function remove(string $type, string $value): bool
    {
        return self::where('type', $type)
            ->where('value', $value)
            ->update(['is_active' => false]) > 0;
    }

    /**
     * Check if this entry is expired.
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Deactivate expired entries.
     *
     * @return int
     */
    public static function deactivateExpired(): int
    {
        return self::expired()
            ->where('is_active', true)
            ->update(['is_active' => false]);
    }
}