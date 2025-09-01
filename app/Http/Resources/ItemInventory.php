<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ItemInventory extends JsonResource
{
    protected $withoutFields = [];

    public static function collection($resource)
    {
        return tap(new ItemInventoryCollection($resource), function ($collection) {
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
            'serial' => $this->serial,
            'date' => $this->date?->format('Y-m-d\TH:i'),
            'date_time' => $this->date?->format('Y-m-d H:i'),
            'datetime_format' => $this->date?->format('d/m/Y h:i A'),
            'direction' => $this->direction,
            'total' => floatval($this->total),
            'group_items' => ItemInventoryItem::collection($this->items),
        ]);
    }
}
