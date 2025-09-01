<?php

namespace App\Http\Resources;

use App\Http\Cache\CacheKitchenDeliveryItem;
use App\Http\Cache\CacheProduct;
use Illuminate\Http\Resources\Json\JsonResource;

class KitchenDelivery extends JsonResource
{
    protected $withoutFields = [];

    public static function collection($resource)
    {
        return tap(new KitchenDeliveryCollection($resource), function ($collection) {
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
        $items = CacheProduct::get();
        $items_list = [];

        foreach ($items as $item) {
            // $delivery_item = KitchenDeliveryItem::where('item_id', $item->id)->where('kitchen_delivery_id', $this->id)->first();
            $delivery_item = CacheKitchenDeliveryItem::get()
                ->where('item_id', $item->id)->where('kitchen_delivery_id', $this->id)
                ->first(fn ($i) => $i->item_id == $item->id);

            $item->avg_rate = $item->rate;
            $manipulated_item = $item;
            if ($delivery_item) {
                $manipulated_item->delivery_quantity = $delivery_item->delivery_quantity;
                $manipulated_item->requisition_quantity = $delivery_item->requisition_quantity;
                $manipulated_item->delivery_total = $delivery_item->delivery_quantity * $item->avg_rate;
                $manipulated_item->requisition_total = $delivery_item->requisition_quantity * $item->avg_rate;

                $manipulated_item->delivery_item_id = $delivery_item->id;
            }
            $items_list[] = $manipulated_item;
        }
        // dd($items_list[0]);
        $delivery_items = CacheKitchenDeliveryItem::whereIn($this->id, 'kitchen_delivery_id');

        return $this->filterFields([
            'id' => $this->id,
            'date' => $this->date->format('Y-m-d'),
            'date_time' => $this->date->format('Y-m-d H:i'),
            'datetime_format' => $this->date->format('d/m/Y h:i A'),
            'total' => $this->total,

            'branch_id' => $this->branch_id,
            'requisition_id' => $this->requisition_id,
            'central_kitchen_id' => $this->central_kitchen_id,

            'items' => KitchenDeliveryItem::collection($delivery_items),
            'group_items' => KitchenDeliveryItem::collection($items_list),
        ]);
    }
}
