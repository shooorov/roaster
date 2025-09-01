<?php

namespace App\Http\Controllers;

use App\Data;
use App\Helpers;
use App\Http\Cache\CacheProductCategory;
use App\Http\Resources\ChartLine;
use App\Models\Item;
use App\Models\Order;
use App\Models\Product;
use App\RolePermission;
use App\UseBranch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SummaryController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['auth', 'access.permission']);
    }

    /**
     * Show the application data.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function overview(Request $request)
    {
        $isDateSearch = RolePermission::isEnabled('record_search.reporting_summery_date_search');

        if ($isDateSearch) {
            $end_date = $request->end_date ? Helpers::dayEndedAt($request->end_date) : Helpers::dayEndedAt();
            $start_date = $request->start_date ? Helpers::dayStartedAt($request->start_date) : Helpers::dayStartedAt();
        } else {
            $end_date = Helpers::dayEndedAt();
            $start_date = Helpers::dayStartedAt();
        }

        $params = [
            'cards' => Data::whereBranch(UseBranch::id())->summary($start_date, $end_date),
            'filter' => [
                'end_date' => Helpers::operationDay($end_date),
                'start_date' => Helpers::operationDay($start_date),
            ],
        ];

        return Inertia::render('Reporting/Summery', $params);
    }

    /**
     * Show the Product list page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function saleProduct(Request $request)
    {
        $branch_id = UseBranch::id();
        $category_id = $request->category_id;

        $isDateSearch = RolePermission::isEnabled('record_search.reporting_product_date_search');

        if ($isDateSearch) {
            $end_date = $request->end_date ? Helpers::dayEndedAt($request->end_date) : Helpers::dayEndedAt();
            $start_date = $request->start_date ? Helpers::dayStartedAt($request->start_date) : Helpers::dayStartedAt();
        } else {
            $end_date = Helpers::dayEndedAt();
            $start_date = Helpers::dayStartedAt();
        }

        $sold_products = DB::table('orders')->select(
            'product_id',
            'products.name',
            'products.rate',
            DB::raw('SUM(order_products.quantity) as product_quantity'),
            DB::raw('SUM(order_products.quantity * order_products.rate) as product_amount')
            // DB::raw('FORMAT(SUM(order_products.quantity), 0) as product_quantity'),
            // DB::raw('FORMAT(SUM(order_products.quantity * order_products.rate), 2) as product_amount')
        )
            ->join('order_products', 'orders.id', '=', 'order_products.order_id')
            ->join('products', 'products.id', '=', 'order_products.product_id')
            ->join('product_categories', 'product_categories.id', '=', 'products.product_category_id')
            ->when($start_date, function ($query, $start_date) {
                $query->where('orders.date', '>=', $start_date);
            })
            ->when($end_date, function ($query, $end_date) {
                $query->where('orders.date', '<=', $end_date);
            })
            ->when($category_id, function ($query, $category_id) {
                $query->where('products.product_category_id', $category_id);
            })
            ->when($branch_id, function ($query, $branch_id) {
                $query->where('orders.branch_id', $branch_id);
            })
            ->groupBy('product_id', 'products.name', 'products.rate')
            ->orderBy('products.name')
            ->get();

        $params = [
            'sold_products' => $sold_products,
            'categories' => CacheProductCategory::get()->whereNull('product_category_id')->values(),

            'filter' => [
                'category_id' => $category_id,
                'end_date' => Helpers::operationDay($end_date),
                'start_date' => Helpers::operationDay($start_date),
            ],
        ];

        return Inertia::render('Reporting/SaleProduct', $params);
    }

    /**
     * Show the Product list page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function saleItem(Request $request)
    {
        $category_id = $request->category_id;

        $isDateSearch = RolePermission::isEnabled('record_search.reporting_item_date_search');

        if ($isDateSearch) {
            $end_date = $request->end_date ? Helpers::dayEndedAt($request->end_date) : Helpers::dayEndedAt();
            $start_date = $request->start_date ? Helpers::dayStartedAt($request->start_date) : Helpers::dayStartedAt();
        } else {
            $end_date = Helpers::dayEndedAt();
            $start_date = Helpers::dayStartedAt();
        }

        $sold_products = DB::table('orders')->select(
            'product_id',
            'products.name',
            'products.rate',
            DB::raw('SUM(order_products.quantity) as product_quantity'),
            DB::raw('SUM(order_products.quantity * order_products.rate) as product_amount')
            // DB::raw('FORMAT(SUM(order_products.quantity), 0) as product_quantity'),
            // DB::raw('FORMAT(SUM(order_products.quantity * order_products.rate), 2) as product_amount')
        )
            ->join('order_products', 'orders.id', '=', 'order_products.order_id')
            ->join('products', 'products.id', '=', 'order_products.product_id')
            ->join('product_categories', 'product_categories.id', '=', 'products.product_category_id')
            ->when($start_date, function ($query, $start_date) {
                $query->where('orders.date', '>=', $start_date);
            })
            ->when($end_date, function ($query, $end_date) {
                $query->where('orders.date', '<=', $end_date);
            })
            ->when($category_id, function ($query, $category_id) {
                $query->where('products.product_category_id', $category_id);
            })
            ->groupBy('product_id', 'products.name', 'products.rate')
            ->orderBy('products.name')
            ->get();

        $items = Item::all();

        foreach ($sold_products as $sold_product) {
            $product = Product::find($sold_product->product_id);

            foreach ($product->items as $product_item) {
                $item = $items->first(fn ($i) => $i->id == $product_item->item_id);
                $item->quantity = ($item->quantity ?? 0) + $product_item->quantity;
            }
        }

        $params = [
            'items' => $items->filter(fn ($i) => $i->quantity > 0)->toArray(),
            'categories' => CacheProductCategory::get()->whereNull('product_category_id')->values(),

            'filter' => [
                'category_id' => $category_id,
                'end_date' => Helpers::operationDay($end_date),
                'start_date' => Helpers::operationDay($start_date),
            ],
        ];

        return Inertia::render('Reporting/SaleItem', $params);
    }

    /**
     * Show the application data.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function saleHourly(Request $request)
    {
        $isDateSearch = RolePermission::isEnabled('record_search.reporting_hourly_date_search');

        if ($isDateSearch) {
            $end_date = $request->end_date ? Helpers::dayEndedAt($request->end_date) : Helpers::dayEndedAt();
            $start_date = $request->start_date ? Helpers::dayStartedAt($request->start_date) : Helpers::dayStartedAt();
        } else {
            $end_date = Helpers::dayEndedAt();
            $start_date = Helpers::dayStartedAt();
        }

        $records = Order::join('branches', 'branches.id', '=', 'orders.branch_id')
            ->select(
                DB::raw('DATE_FORMAT(date, "%h:%00 %p") as duration'),
                DB::raw('SUM(total) as total'),
            )
            ->when($start_date, function ($query, $start_date) {
                $query->where('date', '>=', $start_date);
            })
            ->when($end_date, function ($query, $end_date) {
                $query->where('date', '<=', $end_date);
            })
            ->groupBy('duration')
            ->get();

        $data = Data::hourly($records->pluck('total', 'duration'));

        // $data = ChartLine::collection($values)->values();
        // dd($data);

        $params = [
            'data' => $data,
            'filter' => [
                'end_date' => Helpers::operationDay($end_date),
                'start_date' => Helpers::operationDay($start_date),
            ],
        ];

        return Inertia::render('Reporting/SaleDaily', $params);
    }
}
