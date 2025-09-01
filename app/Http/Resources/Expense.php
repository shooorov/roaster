<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Expense extends JsonResource
{
    protected $withoutFields = [];

    public static function collection($resource)
    {
        return tap(new ExpenseCollection($resource), function ($collection) {
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
            'date' => $this->date->format('Y-m-d'),
            'date_format' => $this->date->format('d/m/Y'),
            'amount' => $this->amount,
            'status' => $this->status,
            'statuses' => $this->statuses,
            'description' => $this->description,
            'check_no' => $this->check_no,
            'remark' => $this->remark,

            'account_id' => $this->account_id,
            'category_id' => $this->expense_category_id,

            'account_name' => $this->account_name,
            'category_name' => $this->category_name,
        ]);
    }
}
