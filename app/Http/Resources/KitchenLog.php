<?php

namespace App\Http\Resources;

use App\Models\Production as ModelsProduction;
use Illuminate\Http\Resources\Json\JsonResource;

class KitchenLog extends JsonResource
{
    protected $withoutFields = [];

    public static function collection($resource)
    {
        return tap(new KitchenLogCollection($resource), function ($collection) {
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
        $end_time = $this->end_time ?? now();
        $start_time = $this->start_time;
        $startTime = now()->parse($end_time);
        $finishTime = now()->parse($start_time);
        $totalDuration = $finishTime->diffInSeconds($startTime);

        $totalDuration = gmdate('H:i:s', $totalDuration);

        return $this->filterFields([
            'id' => $this->id,
            'end_time' => $this->end_time?->format('h:i:s A'),
            'start_time' => $this->start_time->format('h:i:s A'),
            'time_duration' => $this->time_duration,
            'productions_count' => ModelsProduction::where('kitchen_log_id', $this->id)->count(),
            'productions' => is_array($this->productions) ? Production::collection($this->productions) : [],
        ]);
    }
}
