<?php

namespace Jiny\Auth\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'countries';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'code3',
        'numeric_code',
        'name',
        'native_name',
        'capital',
        'region',
        'subregion',
        'currency_code',
        'currency_name',
        'currency_symbol',
        'phone_code',
        'languages',
        'flag_emoji',
        'flag_svg',
        'latitude',
        'longitude',
        'timezone',
        'timezones',
        'is_active',
        'display_order',
        'meta',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'languages' => 'array',
        'timezones' => 'array',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_active' => 'boolean',
        'display_order' => 'integer',
        'meta' => 'array',
    ];

    /**
     * Get the accounts for the country.
     */
    public function accounts()
    {
        return $this->hasMany(Account::class);
    }

    /**
     * Scope a query to only include active countries.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to order by display order.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order', 'asc')
            ->orderBy('name', 'asc');
    }

    /**
     * Get country by ISO code.
     *
     * @param string $code
     * @return static|null
     */
    public static function findByCode(string $code): ?self
    {
        return self::where('code', strtoupper($code))
            ->orWhere('code3', strtoupper($code))
            ->first();
    }

    /**
     * Get countries by region.
     *
     * @param string $region
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getByRegion(string $region)
    {
        return self::active()
            ->where('region', $region)
            ->ordered()
            ->get();
    }

    /**
     * Get countries by subregion.
     *
     * @param string $subregion
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getBySubregion(string $subregion)
    {
        return self::active()
            ->where('subregion', $subregion)
            ->ordered()
            ->get();
    }

    /**
     * Get countries that use a specific currency.
     *
     * @param string $currencyCode
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getByCurrency(string $currencyCode)
    {
        return self::active()
            ->where('currency_code', strtoupper($currencyCode))
            ->ordered()
            ->get();
    }

    /**
     * Get countries that use a specific language.
     *
     * @param string $language
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getByLanguage(string $language)
    {
        return self::active()
            ->whereJsonContains('languages', $language)
            ->ordered()
            ->get();
    }

    /**
     * Get the full phone number with country code.
     *
     * @param string $phoneNumber
     * @return string
     */
    public function formatPhoneNumber(string $phoneNumber): string
    {
        if (!$this->phone_code) {
            return $phoneNumber;
        }

        // Remove any existing country code
        $phoneNumber = preg_replace('/^\+?\d{1,3}\s?/', '', $phoneNumber);
        
        return '+' . $this->phone_code . ' ' . $phoneNumber;
    }

    /**
     * Get the primary language.
     *
     * @return string|null
     */
    public function getPrimaryLanguage(): ?string
    {
        if (empty($this->languages)) {
            return null;
        }

        return is_array($this->languages) ? $this->languages[0] : null;
    }

    /**
     * Get the primary timezone.
     *
     * @return string|null
     */
    public function getPrimaryTimezone(): ?string
    {
        if ($this->timezone) {
            return $this->timezone;
        }

        if (!empty($this->timezones)) {
            return is_array($this->timezones) ? $this->timezones[0] : null;
        }

        return null;
    }

    /**
     * Check if country is in a specific region.
     *
     * @param string $region
     * @return bool
     */
    public function isInRegion(string $region): bool
    {
        return strcasecmp($this->region, $region) === 0;
    }

    /**
     * Check if country uses a specific currency.
     *
     * @param string $currencyCode
     * @return bool
     */
    public function usesCurrency(string $currencyCode): bool
    {
        return strcasecmp($this->currency_code, $currencyCode) === 0;
    }

    /**
     * Get popular countries (commonly used for forms).
     *
     * @param array $codes
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getPopular(array $codes = ['US', 'GB', 'CA', 'AU', 'DE', 'FR', 'JP', 'KR'])
    {
        return self::active()
            ->whereIn('code', $codes)
            ->ordered()
            ->get();
    }

    /**
     * Get neighboring countries based on region/subregion.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getNeighbors()
    {
        return self::active()
            ->where('id', '!=', $this->id)
            ->where(function ($query) {
                $query->where('region', $this->region)
                    ->orWhere('subregion', $this->subregion);
            })
            ->ordered()
            ->get();
    }
}