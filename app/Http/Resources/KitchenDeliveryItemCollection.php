<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class KitchenDeliveryItemCollection extends ResourceCollection
{
    protected $withoutFields = [];

    // Transform the resource collection into an array.
    public function toArray($request)
    {
        return $this->processCollection($request);
    }

    public function hide(array $fields)
    {
        $this->withoutFields = $fields;

        return $this;
    }

    // Send fields to hide to KitchenDeliveryItemResource while processing the collection.
    protected function processCollection($request)
    {
        return $this->collection->map(function (KitchenDeliveryItem $resource) use ($request) {
            return $resource->hide($this->withoutFields)->toArray($request);
        })->all();
    }
}
