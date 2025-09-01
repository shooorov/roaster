<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderProduct extends JsonResource
{
    protected $withoutFields = [];

    public static function collection($resource)
    {
        return tap(new OrderProductCollection($resource), function ($collection) {
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
            'code' => $this->product?->code,
            'batch' => $this->batch,
            'status' => $this->status,
            'rate' => floatval($this->rate),
            'total' => floatval($this->total),
            'quantity' => floatval($this->quantity),
            'old_quantity' => floatval($this->quantity),
            'quantity_format' => floatval($this->quantity),
            'statuses' => $this->statuses,

            'product_id' => $this->product_id,
            'product_name' => $this->product?->name,
            'product_image' => $this->product?->image,
            'vat_applicable' => $this->product?->vat_applicable,
            'toppings' => [],
            // 'toppings'      => count($this->toppings) ? ProductTopping::collection($this->toppings) : [[
            //     'id'            => '',
            //     'quantity'      => '',
            //     'topping_id'    => '',
            // ]],
        ]);
    }
}
