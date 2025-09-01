<?php

namespace App\Http\Controllers\Operation;

use App\Events\OrderModified;
use App\Helpers;
use App\Http\Cache\CacheBranch;
use App\Http\Cache\CacheBranchAccess;
use App\Http\Cache\CacheCustomer;
use App\Http\Cache\CacheCustomerOrder;
use App\Http\Cache\CacheOrder;
use App\Http\Cache\CacheOrderProductQuantity;
use App\Http\Cache\CachePaymentMethod;
use App\Http\Cache\CacheUser;
use App\Http\Controllers\Controller;
use App\Http\Resources\Order as ResourcesOrder;
use App\Http\Resources\OrderLoadCollection;
use App\Models\Customer;
use App\Models\CustomerToken;
use App\Models\Order;
use App\Models\OrderApproval;
use App\Models\OrderDelivery;
use App\Models\OrderHistory;
use App\Models\OrderPaymentMethod;
use App\Models\OrderProduct;
use App\Models\OrderProductTopping;
use App\Models\ProductInventory;
use App\Models\Production;
use App\Models\ProductionItem;
use App\Models\Status;
use App\Models\User;
use App\RolePermission;
use App\UseBranch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class OrderController extends Controller
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

    /**
     * Show the Order list page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        $isDateSearch = RolePermission::isEnabled('record_search.order_date_search');

        if ($isDateSearch) {
            $end_date = $request->end_date ? Helpers::dayEndedAt($request->end_date) : null;
            $start_date = $request->start_date ? Helpers::dayStartedAt($request->start_date) : null;
        } else {
            $end_date = Helpers::dayEndedAt();
            $start_date = Helpers::dayStartedAt();
        }

        $branch_id = UseBranch::id();
        $customers = $customers = Customer::select('id', 'mobile', 'name')->get()->map(function ($c) {
            return [
                'id' => $c->id,
                'name' => $c->mobile . ' - ' . $c->name
            ];
        });

        $payment_methods = CachePaymentMethod::get();
        $branches = CacheBranch::get();

        $order_managers = Order::select('manager_id')->groupBy('manager_id')->pluck('manager_id')->toArray();
        $managers = CacheUser::get()->whereIn('id', $order_managers)->values()->all();

        $branch_users = CacheBranchAccess::get()->filter(fn($item) => $item->branch_id == $branch_id && $item->is_checked)->pluck('user_id');
        $waiters = User::whereIsWaiter(true)->whereIn('id', $branch_users->toArray())->get();

        $params = [
            'branches' => $branches,
            'customers' => $customers,
            'payment_methods' => $payment_methods,
            'managers' => $managers,
            'waiters' => $waiters,

            'filter' => [
                'manager_id' => $request->manager_id ?? '',
                'waiter_id' => $request->waiter_id ?? '',
                'customer_id' => $request->customer_id ?? '',
                'payment_method_id' => $request->payment_method_id ?? '',
                'bill_status' => $request->bill_status ?? '',
                'end_date' => $request->end_date ? Helpers::operationDay($end_date) : null,
                'start_date' => $request->start_date ? Helpers::operationDay($start_date) : null,
            ],
        ];

        // dd($params);
        // die();

        return Inertia::render('Operation/Order/Index', $params);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function load2(Request $request)
    {
        $start = $request->start ?? 0;
        $length = $request->length ?? -1;

        $filter = $request->search['value'];
        $sort_dir = $request->order[0]['dir'];
        $sort_column = $request->order[0]['column'];

        $manager_id = $request->manager_id;
        $waiter_id = $request->waiter_id;
        $customer_id = $request->customer_id;
        $payment_method_id = $request->payment_method_id;

        $isDateSearch = RolePermission::isEnabled('record_search.order_date_search');

        if ($isDateSearch) {
            $end_date = $request->end_date ? Helpers::dayEndedAt($request->end_date) : null;
            $start_date = $request->start_date ? Helpers::dayStartedAt($request->start_date) : null;
        } else {
            $end_date = Helpers::dayEndedAt();
            $start_date = Helpers::dayStartedAt();
        }

        $records = Order::leftJoin('customers', 'orders.customer_id', '=', 'customers.id')
            ->leftJoin('users as managers', 'orders.manager_id', '=', 'managers.id')
            ->leftJoin('users as waiters', 'orders.waiter_id', '=', 'waiters.id')
            ->leftJoin('branches', 'orders.branch_id', '=', 'branches.id')
            ->leftJoin('order_payment_methods', 'orders.id', '=', 'order_payment_methods.order_id')
            ->leftJoin('payment_methods', 'payment_methods.id', '=', 'order_payment_methods.payment_method_id')
            ->select('orders.*', 'branches.name as branch_name')
            ->when($start_date, function ($query, $start_date) {
                $query->where('orders.date', '>=', $start_date);
            })
            ->when($end_date, function ($query, $end_date) {
                $query->where('orders.date', '<=', $end_date);
            })
            ->when($manager_id, function ($query, $manager_id) {
                $query->where('orders.manager_id', $manager_id);
            })
            ->when($waiter_id, function ($query, $waiter_id) {
                $query->where('orders.waiter_id', $waiter_id);
            })
            ->when($customer_id, function ($query, $customer_id) {
                $query->where('orders.customer_id', $customer_id);
            })
            ->when($payment_method_id, function ($query, $payment_method_id) {
                $query->where('payment_methods.id', $payment_method_id);
            });

        //$recordsTotal = (clone $records)->groupBy('orders.id')->get()->count();
        $recordsTotal = (clone $records)->count();
        $records->where(function ($query) use ($filter) {
            $query->where('orders.invoice_number', 'like', '%' . $filter . '%')
                ->orWhere('payment_methods.name', 'like', '%' . $filter . '%')
                ->orWhere('customers.mobile', 'like', '%' . $filter . '%')
                ->orWhere('customers.name', 'like', '%' . $filter . '%')
                ->orWhere('waiters.name', 'like', '%' . $filter . '%')
                ->orWhere('managers.name', 'like', '%' . $filter . '%');
        });

        //$recordsFiltered = (clone $records)->groupBy('orders.id')->get()->count();
        $recordsFiltered = (clone $records)->count();
        $columns = [
            '',
            'orders.date',
            'orders.invoice_number',
            '',
            'orders.discount_amount',
            'orders.commission_amount',
            'orders.vat_amount',
            'orders.total',
            '',
        ];

        $records->groupBy('orders.id')->orderBy($columns[$sort_column], $sort_dir);

        if ($length > 0) {
            $records->offset($start)->limit($length);
        }

        $record_collection = new OrderLoadCollection($records->get());
        $record_collection->draw = intval($request->draw);
        $record_collection->recordsFiltered = $recordsFiltered;
        $record_collection->recordsTotal = $recordsTotal;

        return $record_collection;
    }
    public function load(Request $request)
    {
        // dd($request);
        // die();
        $start = $request->start ?? 0;
        $length = $request->length ?? -1;

        $filter = $request->search['value'];
        $sort_dir = $request->order[0]['dir'];
        $sort_column = $request->order[0]['column'];


        $status = $request->status;
        $manager_id = $request->manager_id;
        $waiter_id = $request->waiter_id;
        $customer_id = $request->customer_id;
        $payment_method_id = $request->payment_method_id;
        $bill_status = $request->bill_status;

        $isDateSearch = RolePermission::isEnabled('record_search.order_date_search');

        if ($isDateSearch) {
            $end_date = $request->end_date ? Helpers::dayEndedAt($request->end_date) : null;
            $start_date = $request->start_date ? Helpers::dayStartedAt($request->start_date) : null;
        } else {
            $end_date = Helpers::dayEndedAt();
            $start_date = Helpers::dayStartedAt();
        }

        $records = Order::query()
            ->when($start_date, function ($query, $start_date) {
                $query->where('orders.date', '>=', $start_date);
            })
            ->when($end_date, function ($query, $end_date) {
                $query->where('orders.date', '<=', $end_date);
            })
            ->when($manager_id, function ($query, $manager_id) {
                $query->where('orders.manager_id', $manager_id);
            })
            ->when($waiter_id, function ($query, $waiter_id) {
                $query->where('orders.waiter_id', $waiter_id);
            })
            ->when($status, function ($query, $status) {
                $query->where('orders.status', $status);
            })
            ->when($customer_id, function ($query, $customer_id) {
                $query->where('orders.customer_id', $customer_id);
            })
            ->when($payment_method_id, function ($query, $payment_method_id) {
                $query->where('payment_methods.id', $payment_method_id);
            })
            ->when($bill_status === 'Paid', function ($query) {
                $query->whereRaw('total - cash = 0');
            })
            ->when($bill_status === 'Due', function ($query) {
                $query->whereRaw('total - cash != 0');
            });

        $recordsTotal = (clone $records)->count();

        $records->where(function ($query) use ($filter) {
            $query->where('orders.invoice_number', 'like', '%' . $filter . '%');
        });

        $recordsFiltered = (clone $records)->count();

        $columns = [
            '',
            'orders.date',
            'orders.invoice_number',
            '',
            'orders.discount_amount',
            'orders.commission_amount',
            'orders.vat_amount',
            'orders.total',
            '',
        ];

        $records->orderBy($columns[$sort_column], $sort_dir);

        if ($length > 0) {
            $records->offset($start)->limit($length);
        }

        $record_collection = new OrderLoadCollection($records->get());
        $record_collection->draw = intval($request->draw);
        $record_collection->recordsFiltered = $recordsFiltered;
        $record_collection->recordsTotal = $recordsTotal;
        // dd($record_collection);
        // die();

        return $record_collection;
    }

    /**
     * Show the Order detail page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function show(Order $order)
    {
        $params = [
            'order' => new ResourcesOrder($order),
        ];

        return Inertia::render('Operation/Order/Show', $params);
    }

    /**
     * Delete the specified resource from storage.
     *
     * @return Response
     */
    public function destroy(Order $order)
    {
        if ($order->status == 'complete' && ! RolePermission::isEnabled('action.delete_completed_order')) {
            return back()->with('fail', 'Order removing denied! Order status complete');
        }

        DB::beginTransaction();

        try {
            ProductInventory::where('order_id', $order->id)->delete();

            OrderApproval::where('order_id', $order->id)->delete();

            OrderDelivery::where('order_id', $order->id)->delete();

            OrderHistory::where('order_id', $order->id)->delete();

            OrderPaymentMethod::where('order_id', $order->id)->delete();

            OrderProduct::where('order_id', $order->id)->delete();

            OrderProductTopping::where('order_id', $order->id)->delete();

            Production::where('order_id', $order->id)->delete();

            CustomerToken::where('order_id', $order->id)->delete();

            $order->delete();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw ($e);
            return back()->withInput()->with('fail', __('Order removing request failed!'));
        }

        CacheOrderProductQuantity::forget();
        CacheCustomerOrder::forget();

        return redirect()->route('order.index')->with('success', __('Order removed successfully!'));
    }

    /**
     * Change status the specified resource in storage.
     *
     * @return Response
     */
    public function updateStatus(Request $request, Order $order)
    {
        if (! array_key_exists($request->status, $order->statuses)) {
            return back()->with('fail', 'Status changing request failed! Invalid status!');
        }

        DB::beginTransaction();
        $order->changeStatuses()->save(new Status([
            'previous_status' => $order->status ?? '',
            'changed_status' => $request->status,
            'user_id' => Auth::id(),
        ]));
        $order->status = $request->status;
        $order->save();

        DB::commit();

        OrderModified::dispatch($order);

        CacheOrder::forget();

        return back()->with('success', 'Status changed to "' . $order->statuses[$request->status] . '" successfully');
    }
}
