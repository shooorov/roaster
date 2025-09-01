<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ChartLine extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'dateFormat' => 'yyyy-MM-dd H:i',
            'xAxisValue' => $this->first()?->duration,
            'yAxisValue' => $this->sum('total'),
        ];
    }
}
