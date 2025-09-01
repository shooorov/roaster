<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class Order extends JsonResource
{
    protected $withoutFields = [];

    public static function collection($resource)
    {
        return tap(new OrderCollection($resource), function ($collection) {
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
        $item_detail = $this->products->count().' '.
            Str::plural('Item', $this->products->count()).' '.
            $this->products->sum('quantity').' '.
            Str::plural('Unit', $this->products->sum('quantity'));
        $order_title = [
            $this->note,
            $this->customer?->name,
        ];
        $order_title = count(array_filter($order_title)) ? implode(' ', $order_title) : $this->number;

        return $this->filterFields([
            'id' => $this->id,
            'date' => $this->date->format('Y-m-d'),
            'date_time' => $this->date->format('Y-m-d\TH:i'),
            'datetime_format' => $this->date->format('d/m/Y h:i A'),
            'time_format' => $this->date->format('h:i A'),
            'number' => $this->number,
            'order_title' => $order_title,
            'type' => $this->type,
            'sub_total' => $this->sub_total,
            'discount_rate' => $this->discount_rate,
            'discount_type' => $this->discount_type,
            'discount_amount' => $this->discount_amount,
            'commission_amount' => $this->commission_amount,
            'vat_rate' => $this->vat_rate,
            'vat_amount' => $this->vat_amount,
            'total' => $this->total,
            'cash' => $this->cash,
            'change' => $this->change,
            'status' => $this->status,
            'delivery_cost' => $this->delivery_cost,
            'item_detail' => $item_detail,
            'delivery_address' => $this->order_delivery?->delivery_address,
            'note' => $this->note,
            'dine_type' => $this->dine_type,
            //'guest_number' => $this->guest_number,
            'statuses' => $this->statuses,

			'waiter_id' => $this->waiter_id,
            'creator_id' => $this->creator_id,
            'customer_id' => $this->customer_id,

            'invoice_number' => $this->invoice_number,
            'payment_method_name' => $this->payment_method_name,
            'waiter_name' => $this->waiter_name,
            'creator_name' => $this->creator_name,
            'branch_name' => $this->branch?->name,
            'table_name' => $this->stuff?->name,
            'customer_name' => $this->customer?->name,
            'customer_mobile' => $this->customer?->mobile,

            'productions' => count($this->productions) ? Production::collection($this->productions) : [],
            'group_products' => count($this->products) ? OrderProduct::collection($this->products) : [],
        ]);
    }
}
