<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Production extends JsonResource
{
    protected $withoutFields = [];

    public static function collection($resource)
    {
        return tap(new ProductionCollection($resource), function ($collection) {
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
        $order = $this->order;
        $items = $this->items;

        return $this->filterFields([
            'id' => $this->id,
            'type' => $this->type,
            'status' => $this->status,

            'accepted_at' => $this->formatted_accepted_at_format,
            'completed_at' => $this->formatted_completed_at_format,
            'created_at' => $this->formatted_created_at_format,

            'order_id' => $this->order_id,
            'order_number' => $order?->number,
            'order_note' => $order?->note,
            'stuff_name' => $order?->stuff_name,
            'items' => count($items) ? ProductionItem::collection($items) : [],
        ]);
    }
}
