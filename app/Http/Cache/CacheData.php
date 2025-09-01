<?php

namespace App\Http\Cache;

use App\Helpers;
use App\Models\Expense;
use App\Models\Order;
use App\UseBranch;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CacheData
{
    private static $seconds = 30;

    public static function perHeadSale($date)
    {
        $end_date = Helpers::dayEndedAt($date);
        $start_date = Helpers::dayStartedAt($date);

        return Cache::remember('per_head_sale_today', self::$seconds, function () use ($start_date, $end_date) {
            return DB::table('orders')
                ->select(
                    'branch_id',
                    DB::raw('SUM(total) as total_amount'),
                    DB::raw('SUM(guest_number) as total_guest')
                )->where('date', '>=', $start_date)
                ->where('date', '<=', $end_date)
                ->groupBy('branch_id')
                ->get();
        });
    }

    public static function todaySale($date)
    {
        $branch_id = UseBranch::id();
        $end_date = Helpers::dayEndedAt($date);
        $start_date = Helpers::dayStartedAt($date);

        return Cache::remember('sale_today'.$branch_id, self::$seconds, function () use ($start_date, $end_date) {
            return Order::where('date', '>=', $start_date)->where('date', '<=', $end_date)->sum('total');
        });
    }

    public static function todayDueSale($date)
    {
        $branch_id = UseBranch::id();
        $end_date = Helpers::dayEndedAt($date);
        $start_date = Helpers::dayStartedAt($date);

        return Cache::remember('due_sale_today'.$branch_id, self::$seconds, function () use ($start_date, $end_date) {
            return Order::where('date', '>=', $start_date)->where('date', '<=', $end_date)->sum(DB::raw('total - cash'));
        });
    }

    public static function todaySaleCount($date)
    {
        $branch_id = UseBranch::id();
        $end_date = Helpers::dayEndedAt($date);
        $start_date = Helpers::dayStartedAt($date);

        return Cache::remember('sale_count_today'.$branch_id, self::$seconds, function () use ($start_date, $end_date) {
            return Order::where('date', '>=', $start_date)->where('date', '<=', $end_date)->count();
        });
    }

    public static function yesterdaySale($date)
    {
        $branch_id = UseBranch::id();
        $end_date = Helpers::dayEndedAt($date);
        $start_date = Helpers::dayStartedAt($date);

        return Cache::remember('sale_yesterday'.$branch_id, self::$seconds, function () use ($start_date, $end_date) {
            return Order::where('date', '>=', $start_date)->where('date', '<=', $end_date)->sum('total');
        });
    }

    public static function yesterdayDueSale($date)
    {
        $branch_id = UseBranch::id();
        $end_date = Helpers::dayEndedAt($date);
        $start_date = Helpers::dayStartedAt($date);

        return Cache::remember('sale_due_yesterday'.$branch_id, self::$seconds, function () use ($start_date, $end_date) {
            return Order::where('date', '>=', $start_date)->where('date', '<=', $end_date)->sum(DB::raw('total - cash'));
        });
    }

    public static function yesterdaySaleCount($date)
    {
        $branch_id = UseBranch::id();
        $end_date = Helpers::dayEndedAt($date);
        $start_date = Helpers::dayStartedAt($date);

        return Cache::remember('sale_count_yesterday'.$branch_id, self::$seconds, function () use ($start_date, $end_date) {
            return Order::where('date', '>=', $start_date)->where('date', '<=', $end_date)->count();
        });
    }

    public static function currentMonthSale($month)
    {
        $branch_id = UseBranch::id();
        $end_date = Helpers::dayEndedAt($month->endOfMonth());
        $start_date = Helpers::dayStartedAt($month->startOfMonth());

        return Cache::remember('sale_current_month'.$branch_id, self::$seconds, function () use ($start_date, $end_date) {
            return Order::where('date', '>=', $start_date)->where('date', '<=', $end_date)->sum('total');
        });
    }
    public static function currentMonthDueSale($month)
    {
        $branch_id = UseBranch::id();
        $end_date = Helpers::dayEndedAt($month->endOfMonth());
        $start_date = Helpers::dayStartedAt($month->startOfMonth());

        return Cache::remember('sale_current_month_due'.$branch_id, self::$seconds, function () use ($start_date, $end_date) {
            return Order::where('date', '>=', $start_date)->where('date', '<=', $end_date)->sum(DB::raw('total - cash'));
        });
    }

    public static function currentMonthSaleCount($month)
    {
        $branch_id = UseBranch::id();
        $end_date = Helpers::dayEndedAt($month->endOfMonth());
        $start_date = Helpers::dayStartedAt($month->startOfMonth());

        return Cache::remember('sale_count_current_month'.$branch_id, self::$seconds, function () use ($start_date, $end_date) {
            return Order::where('date', '>=', $start_date)->where('date', '<=', $end_date)->count();
        });
    }

    public static function lastMonthSale($month)
    {
        $branch_id = UseBranch::id();
        $end_date = Helpers::dayEndedAt($month->endOfMonth());
        $start_date = Helpers::dayStartedAt($month->startOfMonth());

        return Cache::remember('sale_last_month'.$branch_id, self::$seconds, function () use ($start_date, $end_date) {
            return Order::where('date', '>=', $start_date)->where('date', '<=', $end_date)->sum('total');
        });
    }

    public static function lastMonthDueSale($month)
    {
        $branch_id = UseBranch::id();
        $end_date = Helpers::dayEndedAt($month->endOfMonth());
        $start_date = Helpers::dayStartedAt($month->startOfMonth());

        return Cache::remember('sale_last_month_due'.$branch_id, self::$seconds, function () use ($start_date, $end_date) {
            return Order::where('date', '>=', $start_date)->where('date', '<=', $end_date)->sum(DB::raw('total - cash'));
        });
    }

    public static function lastMonthSaleCount($month)
    {
        $branch_id = UseBranch::id();
        $end_date = Helpers::dayEndedAt($month->endOfMonth());
        $start_date = Helpers::dayStartedAt($month->startOfMonth());

        return Cache::remember('sale_count_last_month'.$branch_id, self::$seconds, function () use ($start_date, $end_date) {
            return Order::where('date', '>=', $start_date)->where('date', '<=', $end_date)->count();
        });
    }

    public static function totalSale()
    {
        $branch_id = UseBranch::id();

        return Cache::remember('sale_total'.$branch_id, self::$seconds, function () {
            return Order::sum('total');
        });
    }

    public static function totalSaleCount()
    {
        $branch_id = UseBranch::id();

        return Cache::remember('sale_count'.$branch_id, self::$seconds, function () {
            return Order::count();
        });
    }

    public static function currentMonthExpense($month)
    {
        $branch_id = UseBranch::id();
        $end_date = (clone $month)->endOfMonth();
        $start_date = (clone $month)->startOfMonth();

        return Cache::remember('expense_current_month'.$branch_id, self::$seconds, function () use ($start_date, $end_date) {
            return Expense::where('date', '>=', $start_date)->where('date', '<=', $end_date)->where('status', 'paid')->sum('amount');
        });
    }

    public static function lastMonthExpense($month)
    {
        $branch_id = UseBranch::id();
        $end_date = (clone $month)->endOfMonth();
        $start_date = (clone $month)->startOfMonth();

        return Cache::remember('expense_last_month'.$branch_id, self::$seconds, function () use ($start_date, $end_date) {
            return Expense::where('date', '>=', $start_date)->where('date', '<=', $end_date)->where('status', 'paid')->sum('amount');
        });
    }

    public static function hourly($date)
    {
        $branch_id = UseBranch::id();
        $end_date = Helpers::dayEndedAt($date);
        $start_date = Helpers::dayStartedAt($date);

        return Cache::remember('sale_last_24_hour'.$branch_id, self::$seconds, function () use ($start_date, $end_date) {
            return Order::join('branches', 'branches.id', '=', 'orders.branch_id')
                ->select(
                    DB::raw('DATE_FORMAT(date, "%Y-%m-%d %h:%00 %p") as duration'),
                    DB::raw('total'),
                )
                ->when($start_date, function ($query, $start_date) {
                    $query->where('date', '>=', $start_date);
                })
                ->when($end_date, function ($query, $end_date) {
                    $query->where('date', '<=', $end_date);
                })
                ->get();
        });
    }

    public static function daily($date)
    {
        // Not fully configured with settings day_started_at field
        $branch_id = UseBranch::id();
        $end_date = Helpers::dayEndedAt();
        $start_date = Helpers::dayStartedAt($date);

        return Cache::remember('sale_last_30_day'.$branch_id, self::$seconds, function () use ($start_date, $end_date) {
            return Order::join('branches', 'branches.id', '=', 'orders.branch_id')
                ->select(
                    DB::raw('DATE_FORMAT(date, "%d %b, %y") as duration'),
                    DB::raw('total'),
                    DB::raw('branches.name as branch_name'),
                )
                ->where('date', '>=', $start_date)
                ->where('date', '<=', $end_date)
                ->get();
        });
    }

    public static function monthly($date)
    {
        // Not fully configured with settings day_started_at field
        $branch_id = UseBranch::id();
        $end_date = Helpers::dayEndedAt();
        $start_date = Helpers::dayStartedAt($date);

        return Cache::remember('sale_last_6_month'.$branch_id, self::$seconds, function () use ($start_date, $end_date) {
            return Order::join('branches', 'branches.id', '=', 'orders.branch_id')
                ->select(
                    DB::raw('DATE_FORMAT(date, "%b, %y") as duration'),
                    DB::raw('total'),
                    DB::raw('branches.name as branch_name'),
                )
                ->where('date', '>=', $start_date)
                ->where('date', '<=', $end_date)
                ->get();
        });
    }
}
