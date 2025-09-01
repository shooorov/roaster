<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Account extends JsonResource
{
    protected $withoutFields = [];

    public static function collection($resource)
    {
        return tap(new AccountCollection($resource), function ($collection) {
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
            'type' => $this->type,
            'name' => $this->name,
            'number' => $this->number,
            'branch' => $this->branch,
            'address' => $this->address,
            'description' => $this->description,
            'balance' => $this->balance,

            'opening_check' => $this->opening_check,
            'opening_balance' => $this->opening_balance,
            'opening_date' => $this->opening_date?->format('Y-m-d'),

            'account_name' => $this->extended_name,
        ]);
    }
}
