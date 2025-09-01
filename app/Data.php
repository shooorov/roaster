<?php

namespace App;

use App\Models\Expense;
use App\Models\Order;
use App\Models\OrderPaymentMethod;
use App\Models\OrderProduct;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Scopes\UseBranchScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Data
{
    private static $branchId;

    // Uneditable payment method for cash
    private static $METHOD_CASH = 1;

    public function __construct()
    {
    }

    public static function whereBranch($branchId)
    {
        self::$branchId = $branchId;

        return new self;
    }

    public static function pendingOrders($start_date, $end_date)
    {
        $branch_id = self::$branchId ?? UseBranch::id();
        $waiter_id = Auth::user()->is_waiter ? Auth::user()->id : null;

        return Order::withoutGlobalScope(UseBranchScope::class)
            ->select('id', 'invoice_number', 'status', 'is_printed', 'branch_id')
            ->when($branch_id, function ($query, $branch_id) {
                $query->where('branch_id', $branch_id);
            })
            ->when($start_date, function ($query, $start_date) {
                $query->where('date', '>=', $start_date);
            })
            ->when($end_date, function ($query, $end_date) {
                $query->where('date', '<=', $end_date);
            })
            ->when($waiter_id, function ($query, $waiter_id) {
                $query->where('waiter_id', $waiter_id);
            })
            ->where('status', '!=', 'complete')
            ->orderBy('id', 'DESC')
            ->get();
    }

    public function summary($start_date, $end_date)
    {
        $branch_id = self::$branchId;
        // dd($start_date, $end_date);

        $expenses = Expense::withoutGlobalScope(UseBranchScope::class)
            ->when($branch_id, function ($query, $branch_id) {
                $query->where('branch_id', $branch_id);
            })
            ->where('date', '>=', now()->parse($start_date)->startOfDay())
            ->where('date', '<=', now()->parse($end_date)->subDay()->endOfDay())
            // ->where('status', 'paid')
            ->get();

        $orders = Order::withoutGlobalScope(UseBranchScope::class)
            ->when($branch_id, function ($query, $branch_id) {
                $query->where('branch_id', $branch_id);
            })
            ->where('date', '>=', $start_date)
            ->where('date', '<=', $end_date)
            ->get();

        $total_sale = $orders->sum('total');
        $total_due = $orders->sum(function ($order) {
            return $order->total - $order->cash;
        });
        $without_due = $total_sale - $total_due;
        $total_sale_after_commission = $orders->sum(function ($order) {
            return $order->total - $order->commission_amount;
        });
        $total_commission = $orders->sum('commission_amount');
        $total_vat_sale = $orders->sum('vat_amount');
        $total_expense = $expenses->where('status', 'paid')->sum('amount');
        $total_expense_due = $expenses->where('status', 'unpaid')->sum('amount');

        $total_sale_without_vat = $total_sale - $total_vat_sale;
        $total_income = $total_sale_without_vat - $total_expense;

        $total_guests_served = $orders->sum('guest_number');
        $avg_bucket_size = $total_sale / ($total_guests_served ? $total_guests_served : 1);

        $order_ids = $orders->pluck('id');

        $order_products = OrderProduct::select(DB::raw('SUM(quantity) as product_quantity'), 'product_id')
            ->whereIn('order_id', $order_ids)
            ->groupBy('product_id')
            ->pluck('product_quantity', 'product_id')->toArray();

        $total_sold = array_sum($order_products);

        $total_item_recipe_cost = Product::whereIn('id', array_keys($order_products))->get()->map(function ($product) use ($order_products) {
            return $product->production_cost * $order_products[$product->id];
        })->sum();

        $cards = [];

        $cards[] = [
            'name' => 'Total Sale',
            'amount' => Helpers::toAmount($total_sale),
        ];
        $cards[] = [
            'name' => 'Total Due',
            'amount' => Helpers::toAmount($total_due),
        ];
        $cards[] = [
            'name' => 'Total Paid Amount',
            'amount' => Helpers::toAmount($without_due),
        ];
        $cards[] = [
            'name' => 'Total Commissions',
            'amount' => Helpers::toAmount($total_commission),
        ];
        $cards[] = [
            'name' => 'Total Sale After Commission',
            'amount' => Helpers::toAmount($total_sale_after_commission),
        ];

        $cards[] = [
            'name' => 'Total VAT',
            'amount' => Helpers::toAmount($total_vat_sale),
        ];

        $cards[] = [
            'name' => 'Total Sale Without VAT',
            'amount' => Helpers::toAmount($total_sale_without_vat),
        ];

        $cards[] = [
            'name' => 'Total Expense',
            'amount' => Helpers::toAmount($total_expense),
        ];

        $cards[] = [
            'name' => 'Total Expense Due',
            'amount' => Helpers::toAmount($total_expense_due),
        ];

        $cards[] = [
            'name' => 'Total Income',
            'amount' => Helpers::toAmount($total_income),
        ];

        $cards[] = [
            'name' => '',
        ];

        foreach (PaymentMethod::all() as $payment_method) {
            $sale_amount = OrderPaymentMethod::whereIn('order_id', $order_ids)->where('payment_method_id', $payment_method->id)->sum('amount');

            if ($payment_method->id == self::$METHOD_CASH) {
                $sale_amount -= Order::whereIn('id', $order_ids)->sum('change');
            }

            $cards[] = [
                'name' => 'Total '.$payment_method->name.' Sale',
                'amount' => Helpers::toAmount($sale_amount),
            ];
        }

        $dine_type_orders = $orders->whereNotNull('dine_type')
            ->where('dine_type', '!=', '')
            ->groupBy('dine_type')
            ->mapWithKeys(function ($method_orders, $key) {
                $total_sale = $method_orders->sum('total');

                return [
                    $key => [
                        'name' => 'Total '.Str::of($key)->replace('-', ' ')->title()->value.' Sale',
                        'amount' => Helpers::toAmount($total_sale),
                    ],
                ];
            })->values()->all();

        $cards = array_merge($cards, $dine_type_orders);
        $cards[] = [
            'name' => '',
        ];

        $cards[] = [
            'name' => 'Total Guests Served',
            'amount' => Helpers::toAmount($total_guests_served),
        ];

        $cards[] = [
            'name' => 'Average Bucket Size',
            'amount' => Helpers::toAmount($avg_bucket_size),
        ];
        $cards[] = [
            'name' => '',
        ];

        $cards[] = [
            'name' => 'Total Item Recipe Cost',
            'amount' => Helpers::toAmount($total_item_recipe_cost),
        ];

        $cards[] = [
            'name' => 'Total Sold',
            'amount' => Helpers::toQuantity($total_sold),
        ];

        return $cards;
    }

    public static function hourly($value)
    {
        $end_time = Helpers::dayEndedAt();
        $current_time = Helpers::dayStartedAt();

        $data = [];
        do {
            $time = $current_time->format('h:i A');

            $data[] = [
                'dateFormat' => 'yyyy-MM-dd H:i',
                'xAxisName' => 'Hourly',
                'yAxisName' => 'Sales',
                'xAxisValue' => $current_time->format('Y-m-d h:i A'),
                'xAxisTooltip' => $current_time->format('H A'),
                'yAxisValue' => floatval($value[$time] ?? 0),
            ];

            $current_time = $current_time->addHour();

        } while ($current_time->lt($end_time));

        // dd($value, $data);
        return $data;
    }
}
