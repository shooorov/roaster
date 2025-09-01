<?php

namespace App\Http\Resources;

use App\Http\Cache\CacheItem;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemInventoryItem extends JsonResource
{
    protected $withoutFields = [];

    public static function collection($resource)
    {
        return tap(new ItemInventoryItemCollection($resource), function ($collection) {
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
        $item = CacheItem::find($this->item_id);

        return $this->filterFields([
            'id' => $this->id,
            'quantity' => floatval($this->quantity),
            'rate' => floatval($this->rate),
            'total' => floatval($this->total),
            'item_id' => $this->item_id,

            'required_quantity_unit' => $this->required_quantity_unit,
            'required_rate_total' => $this->required_rate_total,

            'inventory_date_format' => $this->inventory_date_format,
            'item_name' => $item?->name,
            'avg_rate' => floatval($item?->avg_rate),
            'unit' => $item?->unit,
        ]);
    }
}
