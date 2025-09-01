<?php

namespace App\Http\Controllers\Api;

use App\Helpers;
use App\Http\Cache\CacheOnlineProductCategory;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Location;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\PaymentMethod;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function locations()
    {
        return response()->json(Location::all()->toArray(), 200);
    }

    public function branches()
    {
        return response()->json(Branch::all()->toArray(), 200);
    }

    public function categories()
    {
        return response()->json(CacheOnlineProductCategory::all()->toArray(), 200);
    }

    public function products()
    {
        return response()->json(DB::table('products')->whereNull('deleted_at')->get()->toArray(), 200);
    }

    public function basic()
    {
        $categories = ProductCategory::orderBy('name')->get()->toArray();
        $customers = Customer::orderBy('name')->get()->toArray();
        $paymentMethods = PaymentMethod::all()->toArray();
        $employees = Employee::orderBy('name')->get()->toArray();
        $riders = User::where('is_rider', true)->orderBy('name')->get()->toArray();
        $products = DB::table('products')->whereNull('deleted_at')->get();

        $start_date = Helpers::dayStartedAt(now()->subMonth());

        $topProducts = OrderProduct::select(
            'product_id',
            DB::raw('COUNT(product_id) as count')
        )
            ->where('created_at', '>=', $start_date)
            ->groupBy('product_id')
            ->orderBy('count', 'DESC')
            ->limit(20)
            ->pluck('product_id');

        $order = new Order();

        $discountTypes = collect($order->discount_types);

        $dineTypes = collect([
            ['id' => 'dine-in',     'name' => 'Dine In'],
            ['id' => 'take-away',   'name' => 'Take Away'],
        ]);

        if (config('module.delivery')) {
            $dineTypes->push([
                'id' => 'delivery',   'name' => 'Delivery',
            ]);
        }

        $statuses = collect($order->statuses)->map(function ($status, $index) {
            return [
                'id' => $index,
                'name' => $status,
            ];
        })->values()->toArray();

        $params = [
            'categories' => $categories,
            'customers' => $customers,
            'dineTypes' => $dineTypes,
            'discountTypes' => $discountTypes,
            'employees' => $employees,
            'paymentMethods' => $paymentMethods,
            'products' => $products,
            'riders' => $riders,
            'statuses' => $statuses,
            'topProducts' => $topProducts,
        ];

        return response()->json($params, 200);
    }
}
