<?php

namespace App\Http\Controllers;

use App\Data;
use App\Helpers;
use App\Http\Cache\CacheCustomerOrder;
use App\Http\Cache\CacheData;
use App\Http\Cache\CacheOrder;
use App\Http\Cache\CacheOrderProduct;
use App\Http\Cache\CacheOrderProductQuantity;
use App\Http\Cache\CacheProduction;
use App\Http\Cache\CacheProductionItem;
use App\Http\Resources\ChartColumn;
use App\Http\Resources\ChartLine;
use App\Http\Resources\ChartPie;
use App\Models\Branch;
use App\Models\OrderApproval;
use App\Models\Status;
use App\RolePermission;
use App\UseBranch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function use(Request $request)
    {
        if ($request->user()) {
            UseBranch::set(Branch::find($request->branch_id));
        }

        return back();
    }

    /**
     * Show the application data.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $date = Helpers::dayStartedAt();
        $today = (clone $date);
        $yesterday = (clone $date)->subDay();
        $current_month = (clone $date);
        $last_month = (clone $current_month)->subMonth();
        $sale_branches = [];
        $branch_sales = [];

        if (RolePermission::isEnabled('data.current_per_head_sale')) {

            $branch_sales = CacheData::perHeadSale($today);

            $sale_branches = UseBranch::all()->map(function ($item) use ($branch_sales) {

                $branch_sale = $branch_sales->first(fn ($bs) => $bs->branch_id == $item->id);

                $item->total_guest = $branch_sale?->total_guest ?? 0;
                $item->total_amount = $branch_sale?->total_amount ?? 0;

                $head_cost = $item->total_guest > 0 ? $item->total_amount / $item->total_guest : 0;

                $item->per_head_cost = Helpers::toAmount($head_cost);

                return $item;
            });
        }

        $cards = [];

        if (RolePermission::isEnabled('data.current_sale')) {
            $extraInfo = [];
                $extraInfo['extra_info'] = [
                    'name' => 'Current Due Sale',
                    'amount' => Helpers::toAmount(CacheData::todayDueSale($today)),
                ];
            
            $cards = array_merge($cards, [
                [
                    'name' => 'Current Sale',
                    'icon' => 'CashIcon',
                    'amount' => Helpers::toAmount(CacheData::todaySale($today)),
                    'count_record' => round(CacheData::todaySaleCount($today)).' Orders',
                    'href' => route('order.index', [
                        'end_date' => $today->format('Y-m-d'),
                        'start_date' => $today->format('Y-m-d'),
                    ]),
                ] + $extraInfo,
            ]);
        }

        if (RolePermission::isEnabled('data.yesterday_sale')) {
            $extraInfo = [];
            $extraInfo['extra_info'] = [
                'name' => 'Yesterday Due Sale',
                'amount' => Helpers::toAmount(CacheData::yesterdayDueSale($yesterday)),
            ];
            $cards = array_merge($cards, [
                [
                    'name' => 'Yesterday Sale',
                    'icon' => 'CashIcon',
                    'amount' => Helpers::toAmount(CacheData::yesterdaySale($yesterday)),
                    'count_record' => round(CacheData::yesterdaySaleCount($yesterday)).' Orders',
                    'href' => route('order.index', [
                        'end_date' => $yesterday->format('Y-m-d'),
                        'start_date' => $yesterday->format('Y-m-d'),
                    ]),
                ]+$extraInfo,
            ]);
        }

        if (RolePermission::isEnabled('data.current_month_sale')) {
            $extraInfo = [];
            $extraInfo['extra_info'] = [
                'name' => 'Current Month Due Sale',
                'amount' => Helpers::toAmount(CacheData::currentMonthDueSale($current_month)),
            ];
            $cards = array_merge($cards, [
                [
                    'name' => 'Current Month Sale',
                    'icon' => 'CashIcon',
                    'amount' => Helpers::toAmount(CacheData::currentMonthSale($current_month)),
                    'count_record' => round(CacheData::currentMonthSaleCount($current_month)).' Orders',
                    'href' => route('order.index', [
                        'end_date' => (clone $current_month)->endOfMonth()->format('Y-m-d'),
                        'start_date' => (clone $current_month)->startOfMonth()->format('Y-m-d'),
                    ]),
                ]+$extraInfo,
            ]);
        }

        if (RolePermission::isEnabled('data.last_month_sale')) {
            $extraInfo = [];
            $extraInfo['extra_info'] = [
                'name' => 'Last Month Due Sale',
                'amount' => Helpers::toAmount(CacheData::lastMonthDueSale($last_month)),
            ];
            $cards = array_merge($cards, [
                [
                    'name' => 'Last Month Sale',
                    'icon' => 'CashIcon',
                    'amount' => Helpers::toAmount(CacheData::lastMonthSale($last_month)),
                    'count_record' => round(CacheData::lastMonthSaleCount($last_month)).' Orders',
                    'href' => route('order.index', [
                        'end_date' => (clone $last_month)->endOfMonth()->format('Y-m-d'),
                        'start_date' => (clone $last_month)->startOfMonth()->format('Y-m-d'),
                    ]),
                ] + $extraInfo,
            ]);
        }

        if (RolePermission::isEnabled('data.current_month_expense')) {
            $cards = array_merge($cards, [
                [
                    'name' => 'Current Month Expense',
                    'icon' => 'CashIcon',
                    'amount' => Helpers::toAmount(CacheData::currentMonthExpense($current_month)),
                    'href' => route('expense.index', [
                        'end_date' => (clone $current_month)->endOfMonth()->format('Y-m-d'),
                        'start_date' => (clone $current_month)->startOfMonth()->format('Y-m-d'),
                    ]),
                ],

            ]);
        }

        if (RolePermission::isEnabled('data.last_month_expense')) {
            $cards = array_merge($cards, [
                [
                    'name' => 'Last Month Expense',
                    'icon' => 'CashIcon',
                    'amount' => Helpers::toAmount(CacheData::lastMonthExpense($last_month)),
                    'href' => route('expense.index', [
                        'end_date' => (clone $last_month)->endOfMonth()->format('Y-m-d'),
                        'start_date' => (clone $last_month)->startOfMonth()->format('Y-m-d'),
                    ]),
                ],

            ]);
        }

        if (RolePermission::isEnabled('data.last_month_profit')) {
            $cards = array_merge($cards, [
                [
                    'name' => 'Last Month Profit',
                    'icon' => 'CashIcon',
                    'amount' => Helpers::toAmount(
                        (CacheData::lastMonthSale($last_month) - CacheData::lastMonthExpense($last_month))
                    ),
                    'href' => '#',
                ],

            ]);
        }

        if (RolePermission::isEnabled('data.avg_bucket_size_current_month')) {
            $cards = array_merge($cards, [
                [
                    'name' => 'Average Bucket Size (Current Month)',
                    'icon' => 'CashIcon',
                    'amount' => Helpers::toAmount(
                        CacheData::currentMonthSale($current_month)
                            / (CacheData::currentMonthSaleCount($current_month) > 0 ? CacheData::currentMonthSaleCount($current_month) : 1)
                    ),
                    'href' => '#',
                ],

            ]);
        }

        if (RolePermission::isEnabled('data.avg_bucket_size_till_today')) {
            $cards = array_merge($cards, [
                [
                    'name' => 'Average Bucket Size (Till Today)',
                    'icon' => 'CashIcon',
                    'amount' => Helpers::toAmount(
                        CacheData::totalSale()
                            / (CacheData::totalSaleCount() > 0 ? CacheData::totalSaleCount() : 1)
                    ),
                    'href' => '#',
                ],

            ]);
        }

        $last_24_hour = (clone $date)->subHour(24)->startOfHour();
        $last_30_day = (clone $date)->subDay(30)->startOfDay();
        $last_6_month = (clone $date)->subMonth(6)->startOfMonth();

        $data = CacheData::hourly($last_24_hour)->groupBy('duration');
        $data_hourly = ChartLine::collection($data)->values();

        $data = CacheData::daily($last_30_day)->groupBy('duration');
        $data_daily = ChartColumn::collection($data)->values();
        // $data_branches = ChartPie::collection($data->groupBy('branch_name'))->values();

        $data = CacheData::monthly($last_6_month)->groupBy('duration');
        $data_monthly = ChartColumn::collection($data)->values();

        $params = [
            'greeting' => Helpers::greeting(),
            'cards' => $cards,
            'branch_sales' => $branch_sales,
            'sale_branches' => $sale_branches,
            'chart_data' => [
                'branches' => RolePermission::isEnabled('data.branches_pie_chart') ? [] : [],
                'hourly' => RolePermission::isEnabled('data.hourly_chart') ? $data_hourly : [],
                'daily' => RolePermission::isEnabled('data.daily_chart') ? $data_daily : [],
                'monthly' => RolePermission::isEnabled('data.monthly_chart') ? $data_monthly : [],
            ],
        ];

        return Inertia::render('Dashboard', $params);
    }

    public function orderApproval(Request $request)
    {
        $o_approval = OrderApproval::where('order_id', $request->order_id)
            ->where('token', $request->token)
            ->first();

        if (! $o_approval) {
            abort(404);
        }

        $order = $o_approval->order;
        $order->products;

        $branch = $o_approval->order->branch;

        $discount_types = collect($order->discount_types);

        $params = [
            'status' => $o_approval->status,
            'branch' => $branch,
            'order' => $order,
            'token' => $o_approval->token,
            'discount_types' => $discount_types,
        ];

        return Inertia::render('Operation/Order/Approval', $params);
    }

    public function orderApprovalUpdate(Request $request)
    {
        $o_approval = OrderApproval::where('order_id', $request->order_id)
            ->where('token', $request->token)
            ->first();

        if (! $o_approval) {
            return back()->with('fail', 'Order is invalid!');
        }

        $order = $o_approval->order;

        DB::beginTransaction();

        if ($request->status == 'approved') {
            $order_status = 'complete';

            $order->changeStatuses()->save(new Status([
                'previous_status' => $order->status ?? '',
                'changed_status' => $order_status,
                'user_id' => $o_approval->user_id,
            ]));
            $order->status = $order_status;
            $order->save();
        }
        $o_approval->status = $request->status;
        $o_approval->save();

        DB::commit();
        CacheOrder::forget();
        CacheOrderProduct::forget();
        CacheProduction::forget();
        CacheProductionItem::forget();
        CacheOrderProductQuantity::forget();
        CacheCustomerOrder::forget();

        return back()->with('success', 'Order approval process status is '.$request->status);
    }
}
