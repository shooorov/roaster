<?php

namespace App\Http\Resources;

use App\Models\Item;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class ProductInventory extends JsonResource
{
    protected $withoutFields = [];

    public static function collection($resource)
    {
        return tap(new ProductInventoryCollection($resource), function ($collection) {
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
        $group_items = $required_items = [];
        foreach ($this->products as $product) {
            foreach ($product->items as $p_item) {
                $group_items[] = $p_item;

                $quantity = $p_item->quantity;
                if ($p_item->inventory_product) {
                    $quantity = $quantity / $p_item->inventory_product->quantity;
                }
                $quantity = round($quantity, 4);

                if (in_array($p_item->item_id, array_keys($required_items))) {
                    $required_items[$p_item->item_id]['quantity'] += $quantity;
                } else {
                    if (! Item::find($p_item->item_id)) {
                        continue;
                    }
                    $required_items[$p_item->item_id] = [
                        'id' => $p_item->item_id,
                        'name' => $p_item->item?->name,
                        'item_name' => $p_item->item?->name,
                        'unit' => $p_item->item?->unit,
                        'quantity' => $quantity,
                        'in_stock' => round($p_item->item?->in_stock + $quantity, 4),
                    ];
                }
            }
        }
        // dd($group_items);

        return $this->filterFields([
            'id' => $this->id,
            'serial' => $this->serial,
            'date' => $this->date->format('Y-m-d'),
            'date_time' => $this->date->format('Y-m-d H:i'),
            'datetime_format' => $this->date->format('d/m/Y h:i A'),
            'direction' => $this->direction,
            'reference' => $this->reference,
            'name' => $this->reference.' - '.$this->serial.' - '.$this->date->format('d/m/Y h:i A'),

            'order_id' => $this->order_id,
            'branch' => $this->branch,
            'total' => $this->order?->total,
            'order_number' => $this->order?->number,
            'manager_name' => Auth::user()->name,

            'required_items' => $required_items,

            'group_items' => count($group_items) ? ProductInventoryItem::collection($group_items)
                ->hide([
                    'quantity_format',
                    'rate',
                    'total',
                ]) : [],

            'group_products' => count($this->products) ? ProductInventoryProduct::collection($this->products)
                ->hide([
                    'direction',
                ]) : [],
        ]);
    }
}
