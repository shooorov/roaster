<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Event extends JsonResource
{
    protected $withoutFields = [];

    public static function collection($resource)
    {
        return tap(new EventCollection($resource), function ($collection) {
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
            'date' => $this->date,
            'end_time' => $this->end_time,
            'start_time' => $this->start_time,
            'description' => $this->description,

            'date_format' => $this->date?->format('Y-m-d'),
            'end_time_format' => $this->end_time?->format('h:i a'),
            'start_time_format' => $this->start_time?->format('h:i a'),

            'title' => $this->title,
            'time' => $this->time_duration,
            'datetime' => $this->datetime,
            'href' => route('event.show', $this->id),
        ]);
    }
}
