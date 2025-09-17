<?php

namespace Jiny\Auth\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Account extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'accounts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'avatar',
        'status',
        'is_active',
        'email_verified_at',
        'last_login_at',
        'last_login_ip',
        'login_count',
        'failed_login_count',
        'password_changed_at',
        'password_expires_at',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
        'grade_id',
        'country_id',
        'language',
        'timezone',
        'meta',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'last_login_at' => 'datetime',
        'password_changed_at' => 'datetime',
        'password_expires_at' => 'datetime',
        'two_factor_confirmed_at' => 'datetime',
        'is_active' => 'boolean',
        'login_count' => 'integer',
        'failed_login_count' => 'integer',
        'two_factor_recovery_codes' => 'array',
        'meta' => 'array',
    ];

    /**
     * Get the roles for the account.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_account', 'account_id', 'role_id')
            ->withTimestamps();
    }

    /**
     * Get the grade for the account.
     */
    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }

    /**
     * Get the country for the account.
     */
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Get the activity logs for the account.
     */
    public function activityLogs()
    {
        return $this->hasMany(AccountLog::class);
    }

    /**
     * Get the login history for the account.
     */
    public function loginHistory()
    {
        return $this->hasMany(LoginHistory::class);
    }

    /**
     * Get the two-factor authentication settings.
     */
    public function twoFactorAuth()
    {
        return $this->hasOne(TwoFactorAuth::class);
    }

    /**
     * Get the dormant account record.
     */
    public function dormantAccount()
    {
        return $this->hasOne(DormantAccount::class);
    }

    /**
     * Check if the account has a specific role.
     *
     * @param string $role
     * @return bool
     */
    public function hasRole(string $role): bool
    {
        return $this->roles()->where('name', $role)->exists();
    }

    /**
     * Check if the account has any of the specified roles.
     *
     * @param array $roles
     * @return bool
     */
    public function hasAnyRole(array $roles): bool
    {
        return $this->roles()->whereIn('name', $roles)->exists();
    }

    /**
     * Check if the account is active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->is_active && $this->status === 'active';
    }

    /**
     * Check if the account is dormant.
     *
     * @return bool
     */
    public function isDormant(): bool
    {
        return $this->status === 'dormant';
    }

    /**
     * Check if two-factor authentication is enabled.
     *
     * @return bool
     */
    public function hasTwoFactorEnabled(): bool
    {
        return !is_null($this->two_factor_secret);
    }

    /**
     * Increment login count and update last login.
     *
     * @return void
     */
    public function recordLogin(): void
    {
        $this->increment('login_count');
        $this->update([
            'last_login_at' => now(),
            'last_login_ip' => request()->ip(),
            'failed_login_count' => 0,
        ]);
    }

    /**
     * Increment failed login count.
     *
     * @return void
     */
    public function recordFailedLogin(): void
    {
        $this->increment('failed_login_count');
    }
}