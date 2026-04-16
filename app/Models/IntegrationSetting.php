<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntegrationSetting extends Model
{
    protected $fillable = ['key', 'value', 'group'];

    public static function get(string $key, ?string $default = null): ?string
    {
        $record = static::query()->where('key', $key)->first();

        return $record?->value ?? $default;
    }

    public static function set(string $key, ?string $value, ?string $group = null): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group]
        );
    }
}

