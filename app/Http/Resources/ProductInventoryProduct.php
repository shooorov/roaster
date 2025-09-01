<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductInventoryProduct extends JsonResource
{
    protected $withoutFields = [];

    public static function collection($resource)
    {
        return tap(new ProductInventoryProductCollection($resource), function ($collection) {
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
            'direction' => $this->direction,
            'quantity' => $this->quantity,
            'status' => $this->status,
            // 'quantity_unit'     => $unit ? number_format($this->quantity / $unit->factor) . $unit->from_unit?->short : $this->quantity . $this->unit?->short,

            'product_id' => $this->product_id,
            'product_name' => $this->product?->name,
            'rate' => $this->product?->rate,
            'code' => $this->product?->code,
            'vat_applicable' => $this->product?->vat_applicable,
            'total' => $this->quantity * $this->product?->rate,
        ]);
    }
}
