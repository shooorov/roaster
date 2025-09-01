<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OtherSale extends JsonResource
{
    protected $withoutFields = [];

    public static function collection($resource)
    {
        return tap(new OtherSaleCollection($resource), function ($collection) {
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
            'date' => $this->date->format('d/m/Y h:i A'),
            'date_format' => $this->date->format('Y-m-d\TH:i'),
            'amount' => $this->amount,
            'description' => $this->description,
            'check_no' => $this->check_no,

            'account_id' => $this->account_id,
            'branch_id' => $this->branch_id,
            'other_sale_category_id' => $this->other_sale_category_id,

            'account_name' => $this->account_name,
            'branch_name' => $this->branch_name,
            'other_sale_category_name' => $this->category_name,
        ]);
    }
}
