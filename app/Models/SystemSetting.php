<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'label',
        'description',
        'is_public',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    /**
     * Type constants
     */
    const TYPE_STRING = 'string';
    const TYPE_INTEGER = 'integer';
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_JSON = 'json';
    const TYPE_ARRAY = 'array';

    /**
     * Group constants
     */
    const GROUP_GENERAL = 'general';
    const GROUP_SECURITY = 'security';
    const GROUP_DOCUMENTS = 'documents';
    const GROUP_EMAIL = 'email';
    const GROUP_APPEARANCE = 'appearance';

    /**
     * Get a setting value
     */
    public static function get(string $key, $default = null)
    {
        return Cache::remember("setting.{$key}", 3600, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();
            
            if (!$setting) {
                return $default;
            }
            
            return $setting->getTypedValue();
        });
    }

    /**
     * Set a setting value
     */
    public static function set(string $key, $value, ?string $type = null): void
    {
        $setting = self::firstOrNew(['key' => $key]);
        
        if ($type) {
            $setting->type = $type;
        }
        
        $setting->value = is_array($value) || is_object($value) 
            ? json_encode($value) 
            : (string) $value;
        
        $setting->save();
        
        Cache::forget("setting.{$key}");
    }

    /**
     * Get typed value
     */
    public function getTypedValue()
    {
        return match ($this->type) {
            self::TYPE_INTEGER => (int) $this->value,
            self::TYPE_BOOLEAN => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            self::TYPE_JSON, self::TYPE_ARRAY => json_decode($this->value, true),
            default => $this->value,
        };
    }

    /**
     * Scope: By group
     */
    public function scopeGroup($query, string $group)
    {
        return $query->where('group', $group);
    }

    /**
     * Scope: Public settings only
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Get all settings as array
     */
    public static function getAllSettings(): array
    {
        return Cache::remember('settings.all', 3600, function () {
            return self::pluck('value', 'key')->toArray();
        });
    }

    /**
     * Get settings by group
     */
    public static function getByGroup(string $group): array
    {
        return self::where('group', $group)->get()->mapWithKeys(function ($setting) {
            return [$setting->key => $setting->getTypedValue()];
        })->toArray();
    }

    /**
     * Clear settings cache
     */
    public static function clearCache(): void
    {
        Cache::forget('settings.all');
        
        self::pluck('key')->each(function ($key) {
            Cache::forget("setting.{$key}");
        });
    }
}
