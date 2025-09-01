<?php

namespace App\Http\Controllers\Operation;

use App\Data;
use App\Events\OrderModified;
use App\Events\OrderPlaced;
use App\Helpers;
use App\Http\Cache\CacheBranchAccess;
use App\Http\Cache\CacheCustomer;
use App\Http\Cache\CacheCustomerOrder;
use App\Http\Cache\CacheOrder;
use App\Http\Cache\CacheOrderProduct;
use App\Http\Cache\CacheOrderProductQuantity;
use App\Http\Controllers\Controller;
use App\Http\Requests\OrderRequest;
use App\Models\Image;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderHistory;
use App\Models\OrderPaymentMethod;
use App\Models\OrderProduct;
use App\Models\User;
use App\RolePermission;
use App\UseBranch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use App\Models\Product;
use Illuminate\Support\Str;



class POSController extends Controller
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
     * Show the Order create.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function create(Request $request)
    {
        // dd($request->all());
        // die();
        $order = Order::find($request->order_id);
        // dd($order);
        // die();
        // Initialize customer as null
    $customer = null;

    // Retrieve customer information if order exists
    if ($order !== null) {
        $customer = Customer::find($order->customer_id);
    }
        
        $end_date = Helpers::dayEndedAt();
        $start_date = Helpers::dayStartedAt();

        $branch_id = UseBranch::id();
        $vat_rate = UseBranch::take('vat_rate') ?? 5;
        $branches = UseBranch::all();
        $pending_orders = Data::pendingOrders($start_date, $end_date);
        $tables = DB::table('stuffs')->where('branch_id', $branch_id)->orderBy('name')->get();

        $branch_users = CacheBranchAccess::get()->filter(fn ($item) => $item->branch_id == $branch_id && $item->is_checked)->pluck('user_id');
        $waiters = User::whereIsWaiter(true)->whereIn('id', $branch_users->toArray())->get();

        $params = [
            'vat_rate' => $vat_rate,
            'branches' => $branches,
            'tables' => $tables,
            'waiters' => $waiters,
            'pending_orders' => $pending_orders,
            'order' => $order,
            
            
        ];
         // Add customer to params if order is not null
    if ($order !== null) {
        $params['customer'] = $customer;
    }else{
        $params['customer'] = null;
    }
        // dd($params);
        // die();
        return Inertia::render('Operation/POS', $params);
    }

    /**
     * Create new resource in storage.
     *
     * @return Response
     */
    public function store(OrderRequest $request)
    {
        DB::beginTransaction();
        $order = new Order;
        $order_payment_methos = new OrderPaymentMethod;
        // dd($request->all());
        // die();
        $image = null;

        if ($request->customer_mobile) {
            $customer = Customer::updateOrCreate(
                ['mobile' => $request->customer_mobile],
                [
                    'name' => $request->customer_name ?? '',
                    'address' => $request->delivery_address,
                ]
                
                // ['address' => $request->delivery_address],
                // ['name' => $request->customer_name ?? '']
            );
			CacheCustomer::forget();
        }


        if ($request->image_file) {
            if ($request->hasFile('image_file')) {
                if ($request->file('image_file')->isValid()) {
                    Storage::makeDirectory('thumbnails/images/');
                    $thumbnail_path = Storage::path('thumbnails/images/');

                    $extension = $request->image_file->extension();
                    $file_name = Str::random(10).'.'.$extension;
                    $image_path = $request->image_file->storeAs('images', $file_name, 'public');

                    // create image manager with desired driver
                    $manager = new ImageManager(config('image.driver'));

                    // read image from file system
                    // $image_mng = $manager->make(Storage::path($image_path));

                    $image_mng = $manager->read(Storage::path($image_path));


                    // resize image proportionally to 300px width
                    $image_mng->scale(width: 134);

                    // save modified image in new format
                    $image_mng->toPng()->save($thumbnail_path.'/'.$file_name);

                    $image = new Image(['path' => $image_path]);
                    // $order->images()->save($image);
                }
            }
        } else {
            if ($order->latest_image && $request->image_removed) {
                foreach ($order->images as $image) {
                    if (Storage::exists($image->path)) {
                        unlink(storage_path('app/public/thumbnails/'.$image->path));
                        unlink(storage_path('app/public/'.$image->path));
                    }
                    $image->delete();
                }
            }
        }
        // dd($image);
        // die();

        $order->number = Order::withTrashed()->count() + 1;
        $order->invoice_number = now()->format('ymd').$order->number;
        $order->branch_id = UseBranch::id();

        $order->date = now();
        $order->sub_total = $request->sub_total;
        $order->dine_type = $request->dine_type;
        // $order->guest_number = $request->guest_number ?? 1;
        
        $order->guest_number = 1;
        $order->note = $request->note;
        $order->stuff_id = $request->table;
        $order->discount_type = $request->discount_type;
        $order->discount_rate = $request->discount_rate;
        $order->commission_amount = $request->commission_amount;
        $order->discount_amount = $request->discount_amount;
        $order->service_cost = $request->service_cost;
        $order->vatable_amount = $request->vatable_amount;
        $order->vat_rate = $request->vat_rate;
        $order->vat_amount = $request->vat_amount;
        $order->total = $request->total;
        $order->token = $request->token;
        $order->cash = $request->cash;
        $order->change = $request->change;
        $order->delivery_address = $request->delivery_address;
        $order->total_box=$request->total_box;
        $order->customer_id = ! empty($customer) ? $customer->id : null;
        $order->image = $image;
        $order->waiter_id = $request->waiter_id;
        $order->creator_id = Auth::id();

        // If paid by cash or other payment method
        if ($request->cash >= $request->total) {
            $order->status = 'complete';
            $order->manager_id = Auth::id();
        } else {
            $order->status = $request->status;
        }

        $order->save();

        $order_delivery = $order->order_delivery;
        if ($order_delivery) {
            $order_delivery->rider_id = $request->rider_id;
            $order_delivery->save();
        }

        $group_items = is_array($request->group_items) && count($request->group_items) ? $request->group_items : [];
        $group_methods = is_array($request->group_methods) && count($request->group_methods) ? $request->group_methods : [];

        foreach ($group_methods as $group_method) {
			if(isset($group_method['amount']) && $group_method['amount'] > 0) {
				 OrderPaymentMethod::create([
					 'order_id' => $order->id,
					 'payment_method_id' => $group_method['payment_method_id'],
					 'amount' => $group_method['amount'],
				 ]);
			}
        }

        foreach ($group_items as $item) {
            $product_id = $item['product_id'];
            $quantity = intval($item['quantity']);
            $rate = floatval($item['rate']);

            if ($quantity == 0) {
                continue;
            }

            $total = $quantity * $rate;

            OrderProduct::create(
                [
                    'order_id' => $order->id,
                    'product_id' => $product_id,
                    'rate' => $rate,
                    'quantity' => $quantity,
                    'total' => $total,
                ]
            );
        }
        $order->save();

        OrderHistory::create([
            'status' => $order->status,
            'sub_total' => $order->sub_total,
            'total' => $order->total,
            'discount_amount' => $order->discount_amount,
            'vat_amount' => $order->vat_amount,
            'user_id' => Auth::id(),
            'order_id' => $order->id,
        ]);

        CacheOrder::forget();
        CacheOrderProduct::forget();
        CacheOrderProductQuantity::forget();
        CacheCustomerOrder::forget();
        CacheCustomer::forget();

        DB::commit();
        OrderPlaced::dispatch($order);

        if (Auth::user()->is_waiter) {
            return redirect()->route('pos.create')->with('success', 'Order created successfully.');
        }

        return redirect()->route('pos.create', ['order_id' => $order->id])->with('success', 'Order created successfully.');
    }

    /**
     * Create new resource in storage.
     *
     * @return Response
     */
    public function update(OrderRequest $request)
    {
        DB::beginTransaction();
        $order = Order::findOrFail($request->order_id);
//    var_dump($request->delivery_address);
//         die();
        if ($request->customer_mobile) {
            $customer = Customer::updateOrCreate(
                ['mobile' => $request->customer_mobile],
                ['name' => $request->customer_name ?? ''],
                ['address' => $request->order_delivery ?? ''],
            );
			CacheCustomer::forget();
        }
        // This values store modifying history
        $current_status = $order->status;
        $current_total = $order->total;
        $modified_total = $request->total;

        $messages = [];
        if ($request->image_file) {
            if ($request->hasFile('image_file')) {
                if ($request->file('image_file')->isValid()) {
                    Storage::makeDirectory('thumbnails/images/');
                    $thumbnail_path = Storage::path('thumbnails/images/');

                    $extension = $request->image_file->extension();
                    $file_name = Str::random(10).'.'.$extension;
                    $image_path = $request->image_file->storeAs('images', $file_name, 'public');

                    // create image manager with desired driver
                    $manager = new ImageManager(config('image.driver'));

                    // read image from file system
                    // $image_mng = $manager->make(Storage::path($image_path));

                    $image_mng = $manager->read(Storage::path($image_path));


                    // resize image proportionally to 300px width
                    $image_mng->scale(width: 134);

                    // save modified image in new format
                    $image_mng->toPng()->save($thumbnail_path.'/'.$file_name);

                    $image = new Image(['path' => $image_path]);
                    // $order->images()->save($image);
                }
            }
        } else {
            if ($order->latest_image && $request->image_removed) {
                foreach ($order->images as $image) {
                    if (Storage::exists($image->path)) {
                        unlink(storage_path('app/public/thumbnails/'.$image->path));
                        unlink(storage_path('app/public/'.$image->path));
                    }
                    $image->delete();
                }
            }
        }
        // dd($image);
        // die();

        $order->date = now()->parse($request->date_time);
        $order->sub_total = $request->sub_total;
        $order->dine_type = $request->dine_type;
        $order->guest_number = 1;
        $order->note = $request->note;
        $order->stuff_id = $request->table;
        $order->discount_type = $request->discount_type;
        $order->discount_rate = $request->discount_rate;
        $order->discount_amount = $request->discount_amount;
        $order->service_cost = $request->service_cost;
        $order->vatable_amount = $request->vatable_amount;
        $order->vat_rate = $request->vat_rate;
        $order->vat_amount = $request->vat_amount;
        $order->total = $request->total;
        $order->token = $request->token;
        $order->delivery_address = $request->delivery_address;
        $order->cash = $request->cash;
        $order->change = $request->change;
        $order->customer_id = ! empty($customer) ? $customer->id : null;
        $order->commission_amount = $request->commission_amount;
        $order->waiter_id = $request->waiter_id;
        if ($request->image_file) {
            $order->image = $image;
        }

        // If paid by cash or other payment method
        if ($request->cash >= $request->total) {
            $order->status = 'complete';
            $order->manager_id = Auth::id();
        } else {
            if ($order->order_delivery) {
                if ($order->status != $request->status) {
                    $order->status = $request->status;
                }
            } else {
                $order->status = 'accept';
            }
        }

        $order_delivery = $order->order_delivery;
        if ($order_delivery) {
            $order_delivery->rider_id = $request->rider_id;
            $order_delivery->save();

            $messages[] = 'Order delivery rider modified successfully';
        }

        if (RolePermission::isEnabled('action.update_completed_order_payment_methods') || $current_status != 'complete') {
            $group_methods = is_array($request->group_methods) && count($request->group_methods) ? $request->group_methods : [];

            OrderPaymentMethod::where('order_id', $order->id)->delete();

            foreach ($group_methods as $group_method) {
				if(isset($group_method['amount']) && $group_method['amount'] > 0) {
					OrderPaymentMethod::create([
						'order_id' => $order->id,
						'payment_method_id' => $group_method['payment_method_id'],
						'amount' => $group_method['amount'],
					]);
				}
            }

            $messages[] = 'Order payment method modified successfully';
        }

        if (RolePermission::isEnabled('action.update_completed_order_foods') || $current_status != 'complete') {
            $previous_items = $order->products->pluck('id')->toArray();
            $group_items = is_array($request->group_items) && count($request->group_items) ? $request->group_items : [];

            foreach ($group_items as $item) {
                $order_product_id = $item['id'];
                $product_id = $item['product_id'];
                $quantity = intval($item['quantity']);
                $rate = floatval($item['rate']);
                $total = $quantity * $rate;

                if ($quantity == 0) {
                    continue;
                }

                if (($key = array_search($order_product_id, $previous_items)) !== false) {
                    unset($previous_items[$key]);
                }

                OrderProduct::updateOrCreate(
                    [
                        'order_id' => $order->id,
                        'product_id' => $product_id,
                    ],
                    [
                        'rate' => $rate,
                        'quantity' => $quantity,
                        'total' => $total,
                    ]
                );
            }

            $order->save();

            OrderProduct::where('order_id', $order->id)->whereIn('id', $previous_items)->delete();

            $messages[] = 'Order items modified successfully';
        }

        if (RolePermission::isEnabled('action.update_completed_order') || $current_status != 'complete') {

            if ($current_status == 'complete' && $current_total != $modified_total) {
                OrderHistory::create([
                    'status' => $order->status,
                    'sub_total' => $order->sub_total,
                    'total' => $order->total,
                    'discount_amount' => $order->discount_amount,
                    'vat_amount' => $order->vat_amount,
                    'user_id' => Auth::id(),
                    'order_id' => $order->id,
                ]);
            }
            $messages[] = 'Order modified successfully';
        }
        $order->save();

        CacheOrder::forget();
        CacheOrderProduct::forget();
        CacheOrderProductQuantity::forget();
        CacheCustomerOrder::forget();
        CacheCustomer::forget();

        DB::commit();
        OrderModified::dispatch($order);

        return redirect()->route('pos.create', ['order_id' => $order->id])->with('success', implode('<br />', array_reverse($messages)));
    }

    public function print(Order $order)
    {
        $order->products;
        $params = [
            'order' => $order,
        ];

        return Inertia::render('Operation/POSReceipt', $params);
    }

    public function printed(Request $request)
    {
        $order = Order::findOrFail($request->order_id);

        $order->is_printed = true;
        $order->save();

        return back();
    }
}
