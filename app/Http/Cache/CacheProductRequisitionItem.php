<?php

namespace App\Http\Cache;

use App\Models\ProductRequisitionItem as ModelsProductRequisitionItem;
use Illuminate\Support\Facades\Cache;

class CacheProductRequisitionItem
{
    private static $key = 'ProductRequisitionItem';

    public static function get()
    {
        return Cache::remember(self::$key, now()->addHours(2), function () {
            return ModelsProductRequisitionItem::all();
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
        if (Cache::has(self::$key)) {
            Cache::forget(self::$key);
        }
    }
}
