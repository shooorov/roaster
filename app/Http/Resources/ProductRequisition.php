<?php

namespace App\Http\Resources;

use App\Http\Cache\CacheProduct;
use App\Http\Cache\CacheProductRequisitionItem;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductRequisition extends JsonResource
{
    protected $withoutFields = [];

    public static function collection($resource)
    {
        return tap(new ProductRequisitionCollection($resource), function ($collection) {
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
            // $requisition_item = ProductRequisitionItem::where('item_id', $item->id)->where('requisition_id', $this->id)->first();
            $requisition_item = CacheProductRequisitionItem::get()
                ->where('item_id', $item->id)->where('requisition_id', $this->id)
                ->first(fn ($i) => $i->item_id == $item->id);

            $item->avg_rate = $item->rate;
            $manipulated_item = $item;
            if ($requisition_item) {
                $manipulated_item->quantity = $requisition_item->quantity;
                $manipulated_item->total = $requisition_item->quantity * $item->avg_rate;

                $manipulated_item->requisition_item_id = $requisition_item->id;
            }
            $items_list[] = $manipulated_item;
        }
        // dd($items_list[0]);
        $requisition_items = CacheProductRequisitionItem::whereIn($this->id, 'requisition_id');

        return $this->filterFields([
            'id' => $this->id,
            'date' => $this->date->format('Y-m-d'),
            'date_time' => $this->date->format('Y-m-d H:i'),
            'datetime_format' => $this->date->format('d/m/Y h:i A'),
            'central_kitchen_id' => $this->central_kitchen_id,

            'total' => $this->total,
            'group_items' => ProductRequisitionItem::collection($items_list),
            'items' => ProductRequisitionItem::collection($requisition_items),
        ]);
    }
}
