<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductTopping extends JsonResource
{
    protected $withoutFields = [];

    public static function collection($resource)
    {
        return tap(new ProductToppingCollection($resource), function ($collection) {
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
            'price' => $this->price,
            'quantity' => $this->quantity,
            'total' => $this->total,
            'quantity_format' => number_format($this->quantity),

            'topping_id' => $this->topping_id,
            'name' => $this->name,
        ]);
    }
}
