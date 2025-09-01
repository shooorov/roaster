<?php

namespace App\Http\Resources;

use App\Http\Cache\CacheSalary;
use Illuminate\Http\Resources\Json\JsonResource;

class Employee extends JsonResource
{
    protected $withoutFields = [];

    public static function collection($resource)
    {
        return tap(new EmployeeCollection($resource), function ($collection) {
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
        $salary_info = CacheSalary::find($this->id, 'employee_id');

        return $this->filterFields([
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'designation_id' => $this->designation_id,
            'designation_name' => $this->designation_name,
            'gross' => $salary_info?->gross,
            'basic' => $salary_info?->basic,
            'rent' => $salary_info?->rent,
            'medical' => $salary_info?->medical,
            'transport' => $salary_info?->transport,
            'food' => $salary_info?->food,
            'other' => $salary_info?->other,
            'bonus_rate' => $salary_info?->bonus_rate,
            'bonus' => $salary_info?->bonus,
        ]);
    }
}
