<?php

namespace Jiny\Auth\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoginHistory extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'login_histories';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'account_id',
        'ip_address',
        'user_agent',
        'browser',
        'browser_version',
        'platform',
        'device',
        'device_type',
        'location',
        'country',
        'city',
        'latitude',
        'longitude',
        'status',
        'failure_reason',
        'session_id',
        'login_at',
        'logout_at',
        'duration',
        'meta',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'login_at' => 'datetime',
        'logout_at' => 'datetime',
        'duration' => 'integer',
        'meta' => 'array',
    ];

    /**
     * Get the account that owns the login history.
     */
    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Scope a query to only include successful logins.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * Scope a query to only include failed logins.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope a query to only include blocked logins.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBlocked($query)
    {
        return $query->where('status', 'blocked');
    }

    /**
     * Calculate and set the session duration.
     *
     * @return void
     */
    public function calculateDuration(): void
    {
        if ($this->login_at && $this->logout_at) {
            $this->duration = $this->logout_at->diffInSeconds($this->login_at);
            $this->save();
        }
    }

    /**
     * Check if the login is from a suspicious location.
     *
     * @return bool
     */
    public function isSuspiciousLocation(): bool
    {
        // Check if login is from a different country than usual
        $recentLogins = self::where('account_id', $this->account_id)
            ->where('id', '!=', $this->id)
            ->where('status', 'success')
            ->orderBy('login_at', 'desc')
            ->limit(5)
            ->pluck('country')
            ->unique();

        if ($recentLogins->isNotEmpty() && !$recentLogins->contains($this->country)) {
            return true;
        }

        return false;
    }

    /**
     * Record a login attempt.
     *
     * @param int $accountId
     * @param string $status
     * @param array $data
     * @return static
     */
    public static function recordLogin(int $accountId, string $status = 'success', array $data = []): self
    {
        $userAgent = request()->userAgent();
        $browserInfo = self::parseBrowser($userAgent);

        return self::create([
            'account_id' => $accountId,
            'ip_address' => request()->ip(),
            'user_agent' => $userAgent,
            'browser' => $browserInfo['browser'] ?? null,
            'browser_version' => $browserInfo['version'] ?? null,
            'platform' => $browserInfo['platform'] ?? null,
            'device' => $browserInfo['device'] ?? null,
            'device_type' => $browserInfo['device_type'] ?? null,
            'location' => $data['location'] ?? null,
            'country' => $data['country'] ?? null,
            'city' => $data['city'] ?? null,
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'status' => $status,
            'failure_reason' => $data['failure_reason'] ?? null,
            'session_id' => session()->getId(),
            'login_at' => now(),
            'meta' => $data['meta'] ?? null,
        ]);
    }

    /**
     * Parse browser information from user agent.
     *
     * @param string $userAgent
     * @return array
     */
    protected static function parseBrowser(string $userAgent): array
    {
        // Simple browser detection - can be enhanced with a proper library
        $browser = 'Unknown';
        $version = '';
        $platform = 'Unknown';
        $device = 'Unknown';
        $device_type = 'desktop';

        // Detect browser
        if (preg_match('/Chrome\/(\d+)/', $userAgent, $matches)) {
            $browser = 'Chrome';
            $version = $matches[1];
        } elseif (preg_match('/Firefox\/(\d+)/', $userAgent, $matches)) {
            $browser = 'Firefox';
            $version = $matches[1];
        } elseif (preg_match('/Safari\/(\d+)/', $userAgent, $matches)) {
            $browser = 'Safari';
            $version = $matches[1];
        }

        // Detect platform
        if (stripos($userAgent, 'Windows') !== false) {
            $platform = 'Windows';
        } elseif (stripos($userAgent, 'Mac') !== false) {
            $platform = 'MacOS';
        } elseif (stripos($userAgent, 'Linux') !== false) {
            $platform = 'Linux';
        }

        // Detect device type
        if (stripos($userAgent, 'Mobile') !== false) {
            $device_type = 'mobile';
        } elseif (stripos($userAgent, 'Tablet') !== false) {
            $device_type = 'tablet';
        }

        return compact('browser', 'version', 'platform', 'device', 'device_type');
    }
}