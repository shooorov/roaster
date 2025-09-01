<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class User extends JsonResource
{
    protected $withoutFields = [];

    public static function collection($resource)
    {
        return tap(new UserCollection($resource), function ($collection) {
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
            'name' => $this->name,
            'email' => $this->email,
            'mobile' => $this->mobile,
            'address' => $this->address,
            'is_admin' => $this->is_admin,

            'role_id' => $this->role_id,
            'role_name' => $this->role_name,
            'status' => $this->status,
            'statuses' => $this->statuses,

            'image' => $this->image ?? asset('img/avatar.jpg'),
            'image_default' => asset('img/avatar.jpg'),
        ]);
    }
}
