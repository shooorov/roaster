<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductInventoryItem extends JsonResource
{
    protected $withoutFields = [];

    public static function collection($resource)
    {
        return tap(new ProductInventoryItemCollection($resource), function ($collection) {
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
        $quantity = $this->quantity;
        if ($this->inventory_product) {
            $quantity = $quantity / $this->inventory_product->quantity;
            $quantity = round($quantity, 4);
        }

        return $this->filterFields([
            'id' => $this->id,
            'quantity' => floatval($quantity),

            'in_stock' => round($this->item?->in_stock + $this->quantity, 4),
            'item_name' => $this->item?->name,
            'unit' => $this->item?->unit,
        ]);
    }
}
