<?php

namespace App\Http\Cache;

use App\Models\Branch as ModelsBranch;
use Illuminate\Support\Facades\Cache;

class CacheBranch
{
    private static $key = 'branch';

    public static function get()
    {
        return Cache::remember(self::$key, now()->addHours(2), function () {
            return ModelsBranch::orderBy('name')->get();
        });
    }

    public static function find($value, $field_name = 'id')
    {
        return self::get()->first(fn ($i) => $i->$field_name == $value);
    }

    public static function forget()
    {
        if (Cache::has(self::$key)) {
            Cache::forget(self::$key);
        }
    }
}
