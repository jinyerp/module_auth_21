<?php

namespace Jiny\Auth\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'roles';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'display_name',
        'description',
        'level',
        'permissions',
        'is_active',
        'is_system',
        'priority',
        'meta',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'permissions' => 'array',
        'is_active' => 'boolean',
        'is_system' => 'boolean',
        'level' => 'integer',
        'priority' => 'integer',
        'meta' => 'array',
    ];

    /**
     * Get the accounts that have this role.
     */
    public function accounts()
    {
        return $this->belongsToMany(Account::class, 'role_account', 'role_id', 'account_id')
            ->withPivot('assigned_at', 'expires_at', 'assigned_by', 'reason', 'meta')
            ->withTimestamps();
    }

    /**
     * Check if role has a specific permission.
     *
     * @param string $permission
     * @return bool
     */
    public function hasPermission(string $permission): bool
    {
        if (empty($this->permissions)) {
            return false;
        }

        // Check for wildcard permissions
        if (in_array('*', $this->permissions)) {
            return true;
        }

        // Check for specific permission or wildcard patterns
        foreach ($this->permissions as $perm) {
            if ($perm === $permission) {
                return true;
            }

            // Check for wildcard patterns like 'users.*'
            if (str_ends_with($perm, '.*')) {
                $prefix = substr($perm, 0, -2);
                if (str_starts_with($permission, $prefix . '.')) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Grant a permission to this role.
     *
     * @param string|array $permissions
     * @return void
     */
    public function grantPermission($permissions): void
    {
        if (is_string($permissions)) {
            $permissions = [$permissions];
        }

        $currentPermissions = $this->permissions ?? [];
        $this->permissions = array_unique(array_merge($currentPermissions, $permissions));
        $this->save();
    }

    /**
     * Revoke a permission from this role.
     *
     * @param string|array $permissions
     * @return void
     */
    public function revokePermission($permissions): void
    {
        if (is_string($permissions)) {
            $permissions = [$permissions];
        }

        $currentPermissions = $this->permissions ?? [];
        $this->permissions = array_values(array_diff($currentPermissions, $permissions));
        $this->save();
    }

    /**
     * Sync permissions for this role.
     *
     * @param array $permissions
     * @return void
     */
    public function syncPermissions(array $permissions): void
    {
        $this->permissions = array_values(array_unique($permissions));
        $this->save();
    }

    /**
     * Scope a query to only include active roles.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include system roles.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    /**
     * Scope a query to exclude system roles.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNonSystem($query)
    {
        return $query->where('is_system', false);
    }

    /**
     * Check if this role is higher level than another role.
     *
     * @param Role $role
     * @return bool
     */
    public function isHigherThan(Role $role): bool
    {
        return $this->level > $role->level;
    }

    /**
     * Check if this role can be deleted.
     *
     * @return bool
     */
    public function canBeDeleted(): bool
    {
        return !$this->is_system && $this->accounts()->count() === 0;
    }
}