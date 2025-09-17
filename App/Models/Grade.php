<?php

namespace Jiny\Auth\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'grades';

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
        'min_points',
        'max_points',
        'badge',
        'color',
        'benefits',
        'restrictions',
        'discount_rate',
        'is_active',
        'is_default',
        'priority',
        'meta',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'level' => 'integer',
        'min_points' => 'integer',
        'max_points' => 'integer',
        'benefits' => 'array',
        'restrictions' => 'array',
        'discount_rate' => 'decimal:2',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'priority' => 'integer',
        'meta' => 'array',
    ];

    /**
     * Get the accounts that have this grade.
     */
    public function accounts()
    {
        return $this->hasMany(Account::class);
    }

    /**
     * Scope a query to only include active grades.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to get the default grade.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Get grade by points.
     *
     * @param int $points
     * @return static|null
     */
    public static function getByPoints(int $points): ?self
    {
        return self::active()
            ->where('min_points', '<=', $points)
            ->where(function ($query) use ($points) {
                $query->whereNull('max_points')
                    ->orWhere('max_points', '>=', $points);
            })
            ->orderBy('level', 'desc')
            ->first();
    }

    /**
     * Get the next grade.
     *
     * @return static|null
     */
    public function getNextGrade(): ?self
    {
        return self::active()
            ->where('level', '>', $this->level)
            ->orderBy('level', 'asc')
            ->first();
    }

    /**
     * Get the previous grade.
     *
     * @return static|null
     */
    public function getPreviousGrade(): ?self
    {
        return self::active()
            ->where('level', '<', $this->level)
            ->orderBy('level', 'desc')
            ->first();
    }

    /**
     * Check if this grade has a specific benefit.
     *
     * @param string $benefit
     * @return bool
     */
    public function hasBenefit(string $benefit): bool
    {
        return in_array($benefit, $this->benefits ?? []);
    }

    /**
     * Check if this grade has a specific restriction.
     *
     * @param string $restriction
     * @return bool
     */
    public function hasRestriction(string $restriction): bool
    {
        return in_array($restriction, $this->restrictions ?? []);
    }

    /**
     * Get points needed for the next grade.
     *
     * @param int $currentPoints
     * @return int|null
     */
    public function getPointsToNextGrade(int $currentPoints): ?int
    {
        $nextGrade = $this->getNextGrade();
        
        if (!$nextGrade) {
            return null;
        }

        return max(0, $nextGrade->min_points - $currentPoints);
    }

    /**
     * Get the default grade for new accounts.
     *
     * @return static
     */
    public static function getDefault(): self
    {
        return self::default()->first() ?? self::active()->orderBy('level', 'asc')->first();
    }

    /**
     * Check if this grade is higher than another grade.
     *
     * @param Grade $grade
     * @return bool
     */
    public function isHigherThan(Grade $grade): bool
    {
        return $this->level > $grade->level;
    }
}