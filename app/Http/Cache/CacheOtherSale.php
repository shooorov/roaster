<?php

namespace App\Http\Cache;

use App\Models\OtherSale as ModelsOtherSale;
use App\UseBranch;
use Illuminate\Support\Facades\Cache;

class CacheOtherSale
{
    private static $key = 'other_sale';

    private static function cacheKey()
    {
        $branch_id = UseBranch::id() ?? 'all';

        return self::$key.'_'.$branch_id;
    }

    public static function get()
    {
        $cache_key = self::cacheKey();

        return Cache::remember($cache_key, now()->addHours(2), function () {
            return ModelsOtherSale::orderBy('date', 'DESC')->get();
        });
    }

    public static function find($value, $field_name = 'id')
    {
        return self::get()->first(fn ($i) => $i->$field_name == $value);
    }

    public static function whereIn($values, $field_name = 'id')
    {
        return self::get()->whereIn($field_name, $values);
    }

    public static function forget()
    {
        $cache_key = self::cacheKey();
        if (Cache::has($cache_key)) {
            Cache::forget($cache_key);
        }
    }
}
