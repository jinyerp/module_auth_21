<?php

namespace Jiny\Auth\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountLog extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'account_logs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'account_id',
        'action',
        'description',
        'ip_address',
        'user_agent',
        'old_values',
        'new_values',
        'meta',
        'status',
        'error_message',
        'performed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'meta' => 'array',
        'performed_at' => 'datetime',
    ];

    /**
     * Get the account that owns the log.
     */
    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Scope a query to only include logs for a specific action.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $action
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope a query to only include successful logs.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * Scope a query to only include failed logs.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Create a log entry for an action.
     *
     * @param int $accountId
     * @param string $action
     * @param array $data
     * @return static
     */
    public static function logAction(int $accountId, string $action, array $data = []): self
    {
        return self::create([
            'account_id' => $accountId,
            'action' => $action,
            'description' => $data['description'] ?? null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'old_values' => $data['old_values'] ?? null,
            'new_values' => $data['new_values'] ?? null,
            'meta' => $data['meta'] ?? null,
            'status' => $data['status'] ?? 'success',
            'error_message' => $data['error_message'] ?? null,
            'performed_at' => now(),
        ]);
    }
}