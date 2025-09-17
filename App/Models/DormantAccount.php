<?php

namespace Jiny\Auth\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DormantAccount extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dormant_accounts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'account_id',
        'last_activity_at',
        'dormant_at',
        'notified_at',
        'notification_count',
        'scheduled_deletion_at',
        'status',
        'reason',
        'backup_data',
        'reactivated_at',
        'reactivated_by',
        'reactivation_reason',
        'meta',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'last_activity_at' => 'datetime',
        'dormant_at' => 'datetime',
        'notified_at' => 'datetime',
        'notification_count' => 'integer',
        'scheduled_deletion_at' => 'datetime',
        'backup_data' => 'array',
        'reactivated_at' => 'datetime',
        'meta' => 'array',
    ];

    /**
     * Get the account that owns the dormant account record.
     */
    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Get the account who reactivated this account.
     */
    public function reactivatedByAccount()
    {
        return $this->belongsTo(Account::class, 'reactivated_by');
    }

    /**
     * Scope a query to only include dormant accounts.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDormant($query)
    {
        return $query->where('status', 'dormant');
    }

    /**
     * Scope a query to only include notified accounts.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNotified($query)
    {
        return $query->where('status', 'notified');
    }

    /**
     * Scope a query to only include reactivated accounts.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeReactivated($query)
    {
        return $query->where('status', 'reactivated');
    }

    /**
     * Scope a query to get accounts scheduled for deletion.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeScheduledForDeletion($query)
    {
        return $query->whereNotNull('scheduled_deletion_at')
            ->where('scheduled_deletion_at', '<=', now())
            ->whereIn('status', ['dormant', 'notified']);
    }

    /**
     * Scope a query to get accounts that need notification.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $daysBefore
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNeedsNotification($query, int $daysBefore = 30)
    {
        return $query->where('status', 'dormant')
            ->where(function ($q) use ($daysBefore) {
                $q->whereNull('notified_at')
                    ->orWhere('notified_at', '<', now()->subDays($daysBefore));
            });
    }

    /**
     * Mark account as dormant.
     *
     * @param int $accountId
     * @param string $reason
     * @param int $daysUntilDeletion
     * @return static
     */
    public static function markAsDormant(int $accountId, string $reason = 'inactivity', int $daysUntilDeletion = 365): self
    {
        $account = Account::findOrFail($accountId);

        return self::updateOrCreate(
            ['account_id' => $accountId],
            [
                'last_activity_at' => $account->last_login_at ?? $account->created_at,
                'dormant_at' => now(),
                'scheduled_deletion_at' => now()->addDays($daysUntilDeletion),
                'status' => 'dormant',
                'reason' => $reason,
                'notification_count' => 0,
            ]
        );
    }

    /**
     * Send notification to dormant account.
     *
     * @return bool
     */
    public function sendNotification(): bool
    {
        // Here you would implement the actual notification logic
        // For now, we'll just update the notification fields

        $this->update([
            'notified_at' => now(),
            'notification_count' => $this->notification_count + 1,
            'status' => 'notified',
        ]);

        return true;
    }

    /**
     * Reactivate the account.
     *
     * @param string $reason
     * @param int|null $reactivatedBy
     * @return bool
     */
    public function reactivate(string $reason = 'user_request', ?int $reactivatedBy = null): bool
    {
        $this->update([
            'status' => 'reactivated',
            'reactivated_at' => now(),
            'reactivated_by' => $reactivatedBy ?? auth()->id(),
            'reactivation_reason' => $reason,
            'scheduled_deletion_at' => null,
        ]);

        // Update the account status
        $this->account->update([
            'status' => 'active',
            'last_login_at' => now(),
        ]);

        return true;
    }

    /**
     * Backup account data before deletion.
     *
     * @return void
     */
    public function backupData(): void
    {
        $account = $this->account;

        $backupData = [
            'account' => $account->toArray(),
            'roles' => $account->roles->toArray(),
            'grade' => $account->grade ? $account->grade->toArray() : null,
            'backed_up_at' => now()->toDateTimeString(),
        ];

        $this->update(['backup_data' => $backupData]);
    }

    /**
     * Check if account can be reactivated.
     *
     * @return bool
     */
    public function canBeReactivated(): bool
    {
        return in_array($this->status, ['dormant', 'notified']) && 
               (!$this->scheduled_deletion_at || $this->scheduled_deletion_at->isFuture());
    }

    /**
     * Check if account should be deleted.
     *
     * @return bool
     */
    public function shouldBeDeleted(): bool
    {
        return $this->scheduled_deletion_at && 
               $this->scheduled_deletion_at->isPast() &&
               in_array($this->status, ['dormant', 'notified']);
    }

    /**
     * Get the number of days until deletion.
     *
     * @return int|null
     */
    public function getDaysUntilDeletion(): ?int
    {
        if (!$this->scheduled_deletion_at) {
            return null;
        }

        if ($this->scheduled_deletion_at->isPast()) {
            return 0;
        }

        return now()->diffInDays($this->scheduled_deletion_at);
    }

    /**
     * Get the number of days since became dormant.
     *
     * @return int
     */
    public function getDaysSinceDormant(): int
    {
        if (!$this->dormant_at) {
            return 0;
        }

        return $this->dormant_at->diffInDays(now());
    }
}