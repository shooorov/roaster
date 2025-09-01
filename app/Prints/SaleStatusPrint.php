<?php

namespace App\Prints;

use App\Helpers;
use App\Http\Resources\Order as ResourcesOrder;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Order;
use App\Models\OrderPaymentMethod;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Contracts\View\View;

class SaleStatusPrint
{
    // Uneditable payment method for cash
    protected $METHOD_CASH = 1;

    // private $start_date, private $end_date, private $payment_method_id, private $customer_id, private $creator_id
    public function __construct(private $request)
    {
    }

    public function options(): array
    {
        return [
            'orientation' => 'L',
        ];
    }

    public function view(): View
    {
        $end_date = Helpers::dayEndedAt($this->request->end_date);
        $start_date = Helpers::dayStartedAt($this->request->start_date);
        $date_duration = Helpers::operationDay($end_date) == Helpers::operationDay($start_date) ? $start_date->format('d/m/Y') : ($start_date->format('d/m/Y').' - '.Helpers::dayStartedAt(Helpers::operationDay($end_date))->format('d/m/Y'));

        $customer_id = $this->request->customer_id;
        $payment_method_id = $this->request->payment_method_id;
        $creator_id = $this->request->creator_id;
        $waiter_id = $this->request->waiter_id;

        $orders = Order::when($start_date, function ($query, $start_date) {
            $query->where('date', '>=', $start_date);
        })
            ->when($end_date, function ($query, $end_date) {
                $query->where('date', '<=', $end_date);
            })
            ->when($customer_id, function ($query, $customer_id) {
                $query->where('customer_id', $customer_id);
            })
            ->when($creator_id, function ($query, $creator_id) {
                $query->where('creator_id', $creator_id);
            })
            ->when($waiter_id, function ($query, $waiter_id) {
                $query->where('waiter_id', $waiter_id);
            })
            // ->whereHas('methods', function ($query) use ($payment_method_id) {
            //     $query->when($payment_method_id, function ($query2, $payment_method_id) {
            //         $query2->where('payment_method_id', $payment_method_id);
            //     });
            // })
            ->orderBy('date', 'ASC')
            ->get();

        $order_ids = $orders->pluck('id');

        $payment_methods = PaymentMethod::all()->map(function ($payment_method) use ($order_ids) {
            $payment_method->amount = OrderPaymentMethod::whereIn('order_id', $order_ids)
                ->where('payment_method_id', $payment_method->id)->sum('amount');

            if ($payment_method->id == $this->METHOD_CASH) {
                $payment_method->amount -= Order::whereIn('id', $order_ids)->sum('change');
            }

            return $payment_method;
        });

        $params = [
            'heading' => 'Sale Status',
            'duration' => $date_duration,
            'orders' => ResourcesOrder::collection($orders),
            'customer' => Customer::find($customer_id),
            'creator' => User::find($creator_id),
            'waiter' => Employee::find($waiter_id),
            'payment_method' => PaymentMethod::find($payment_method_id),
            'payment_methods' => $payment_methods,
        ];

        return view('reports.sale', $params);
    }

    public function headerView(): View
    {
        return view('reports.partials.header', ['options' => $this->options()]);
    }
}
