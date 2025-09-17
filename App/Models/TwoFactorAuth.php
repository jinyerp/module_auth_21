<?php

namespace Jiny\Auth\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TwoFactorAuth extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'two_factor_auth';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'account_id',
        'method',
        'secret',
        'recovery_codes',
        'is_enabled',
        'enabled_at',
        'last_used_at',
        'failed_attempts',
        'locked_until',
        'phone_number',
        'email',
        'backup_email',
        'trusted_devices',
        'settings',
        'meta',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'recovery_codes' => 'array',
        'is_enabled' => 'boolean',
        'enabled_at' => 'datetime',
        'last_used_at' => 'datetime',
        'locked_until' => 'datetime',
        'failed_attempts' => 'integer',
        'trusted_devices' => 'array',
        'settings' => 'array',
        'meta' => 'array',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'secret',
        'recovery_codes',
    ];

    /**
     * Get the account that owns the two-factor authentication.
     */
    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Generate new recovery codes.
     *
     * @param int $count
     * @return array
     */
    public function generateRecoveryCodes(int $count = 8): array
    {
        $codes = [];
        
        for ($i = 0; $i < $count; $i++) {
            $codes[] = Str::random(10) . '-' . Str::random(10);
        }

        $this->recovery_codes = array_map('bcrypt', $codes);
        $this->save();

        return $codes;
    }

    /**
     * Verify a recovery code.
     *
     * @param string $code
     * @return bool
     */
    public function verifyRecoveryCode(string $code): bool
    {
        if (empty($this->recovery_codes)) {
            return false;
        }

        foreach ($this->recovery_codes as $key => $hashedCode) {
            if (password_verify($code, $hashedCode)) {
                // Remove used recovery code
                $codes = $this->recovery_codes;
                unset($codes[$key]);
                $this->recovery_codes = array_values($codes);
                $this->save();
                
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the authentication is locked.
     *
     * @return bool
     */
    public function isLocked(): bool
    {
        return $this->locked_until && $this->locked_until->isFuture();
    }

    /**
     * Lock the authentication for a specified time.
     *
     * @param int $minutes
     * @return void
     */
    public function lock(int $minutes = 30): void
    {
        $this->update([
            'locked_until' => now()->addMinutes($minutes),
        ]);
    }

    /**
     * Unlock the authentication.
     *
     * @return void
     */
    public function unlock(): void
    {
        $this->update([
            'locked_until' => null,
            'failed_attempts' => 0,
        ]);
    }

    /**
     * Record a failed attempt.
     *
     * @return void
     */
    public function recordFailedAttempt(): void
    {
        $this->increment('failed_attempts');

        // Lock after 5 failed attempts
        if ($this->failed_attempts >= 5) {
            $this->lock();
        }
    }

    /**
     * Record a successful verification.
     *
     * @return void
     */
    public function recordSuccessfulVerification(): void
    {
        $this->update([
            'last_used_at' => now(),
            'failed_attempts' => 0,
            'locked_until' => null,
        ]);
    }

    /**
     * Add a trusted device.
     *
     * @param string $deviceId
     * @param array $deviceInfo
     * @return void
     */
    public function addTrustedDevice(string $deviceId, array $deviceInfo = []): void
    {
        $devices = $this->trusted_devices ?? [];
        
        $devices[$deviceId] = array_merge($deviceInfo, [
            'trusted_at' => now()->toDateTimeString(),
            'last_used_at' => now()->toDateTimeString(),
        ]);

        // Keep only the last 10 devices
        if (count($devices) > 10) {
            $devices = array_slice($devices, -10, null, true);
        }

        $this->trusted_devices = $devices;
        $this->save();
    }

    /**
     * Check if a device is trusted.
     *
     * @param string $deviceId
     * @return bool
     */
    public function isTrustedDevice(string $deviceId): bool
    {
        if (empty($this->trusted_devices)) {
            return false;
        }

        if (!isset($this->trusted_devices[$deviceId])) {
            return false;
        }

        // Update last used time
        $devices = $this->trusted_devices;
        $devices[$deviceId]['last_used_at'] = now()->toDateTimeString();
        $this->trusted_devices = $devices;
        $this->save();

        return true;
    }

    /**
     * Remove a trusted device.
     *
     * @param string $deviceId
     * @return void
     */
    public function removeTrustedDevice(string $deviceId): void
    {
        $devices = $this->trusted_devices ?? [];
        unset($devices[$deviceId]);
        $this->trusted_devices = $devices;
        $this->save();
    }

    /**
     * Clear all trusted devices.
     *
     * @return void
     */
    public function clearTrustedDevices(): void
    {
        $this->trusted_devices = [];
        $this->save();
    }
}