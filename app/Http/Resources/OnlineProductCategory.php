<?php

namespace App\Http\Resources;

use App\Http\Cache\CacheOnlineProduct;
use Illuminate\Http\Resources\Json\JsonResource;

class OnlineProductCategory extends JsonResource
{
    protected $withoutFields = [];

    public static function collection($resource)
    {
        return tap(new OnlineProductCategoryCollection($resource), function ($collection) {
            $collection->collects = __CLASS__;
        });
    }

    // Set the keys that are supposed to be filtered out
    public function hide(array $fields)
    {
        $this->withoutFields = $fields;

        return $this;
    }

    // Remove the filtered keys.
    protected function filterFields($array)
    {
        return collect($array)->forget($this->withoutFields)->toArray();
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $group_items = CacheOnlineProduct::get()->where('online_product_category_id', $this->id)->values();

        return $this->filterFields([
            'id' => $this->id,
            'name' => $this->name,
            'serial' => $this->serial,
            'product_count' => $this->items->count(),

            'image' => $this->image,
            'image_default' => $this->image_default,

            'group_items' => count($group_items) ? OnlineProduct::collection($group_items) : [],
        ]);
    }
}
