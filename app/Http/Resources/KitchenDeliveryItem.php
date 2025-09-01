<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class KitchenDeliveryItem extends JsonResource
{
    protected $withoutFields = [];

    public static function collection($resource)
    {
        return tap(new KitchenDeliveryItemCollection($resource), function ($collection) {
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
        return $this->filterFields([
            'id' => $this->id,
            'name' => $this->name,
            'unit' => $this->unit ?? 'pcs',
            'delivery_quantity' => $this->delivery_quantity,
            'requisition_quantity' => $this->requisition_quantity,
            'avg_rate' => $this->avg_rate,
            'delivery_total' => $this->delivery_quantity * $this->avg_rate,
            'requisition_total' => $this->requisition_quantity * $this->avg_rate,

            'item_id' => $this->item_id,
            'item_name' => $this->item_name,
            'item_unit' => $this->item_unit,
            'delivery_item_id' => $this->delivery_item_id,
        ]);
    }
}
