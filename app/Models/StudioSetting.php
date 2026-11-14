<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudioSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * Helper to get a setting value with fallback
     */
    public static function get(string $key, ?string $default = null): ?string
    {
        $setting = static::where('key', $key)->first();

        return $setting ? $setting->value : $default;
    }

    /**
     * Helper to set a setting value
     */
    public static function set(string $key, ?string $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }
}
