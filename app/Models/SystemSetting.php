<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'display_name',
        'group',
    ];

    public static function get(string $key, ?string $default = null): ?string
    {
        $setting = static::where('key', $key)->first();

        return $setting ? $setting->value : $default;
    }

    public static function set(string $key, ?string $value, ?string $displayName = null, string $group = 'general'): self
    {
        return static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'display_name' => $displayName ?? ucfirst(str_replace('_', ' ', $key)),
                'group' => $group,
            ]
        );
    }
}
