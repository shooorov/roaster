<?php

namespace App\Http\Controllers\Manage;

use App\Http\Cache\CacheCentralKitchen;
use App\Http\Cache\CacheCentralKitchenAccess;
use App\Http\Cache\CacheUser;
use App\Http\Controllers\Controller;
use App\Models\CentralKitchen;
use App\Models\CentralKitchenAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class CentralController extends Controller
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
     * Show the CentralKitchen list page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $central_kitchens = CacheCentralKitchen::get();

        $params = [
            'central_kitchens' => $central_kitchens,
        ];

        return Inertia::render('Manage/CentralKitchen/Index', $params);
    }

    /**
     * Show the CentralKitchen create.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function create()
    {
        $params = [];

        return Inertia::render('Manage/CentralKitchen/Create', $params);
    }

    /**
     * Create new resource in storage.
     *
     * @param  CentralKitchen  $central_kitchen
     * @return Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:191', 'unique:central_kitchens,name'],
            'short_code' => ['required', 'string', 'max:10', 'unique:central_kitchens,short_code'],
            'contact' => ['nullable', 'string', 'max:14'],
            'address' => ['nullable', 'string', 'max:500'],
        ]);

        DB::beginTransaction();
        $central_kitchen = new CentralKitchen;
        $central_kitchen->name = $request->name;
        $central_kitchen->short_code = $request->short_code;
        $central_kitchen->phone = $request->contact;
        $central_kitchen->address = $request->address;
        $central_kitchen->save();

        DB::commit();
        CacheCentralKitchen::forget();

        return redirect()->route('central.index')->with('success', 'CentralKitchen created successfully.');
    }

    /**
     * Show the CentralKitchen detail page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function show(CentralKitchen $central_kitchen)
    {
        $params = [
            'central_kitchen' => $central_kitchen,
        ];

        return Inertia::render('Manage/CentralKitchen/Show', $params);
    }

    /**
     * Show the CentralKitchen edit page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function edit(CentralKitchen $central_kitchen)
    {
        $params = [
            'central_kitchen' => $central_kitchen,
        ];

        return Inertia::render('Manage/CentralKitchen/Edit', $params);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return Response
     */
    public function update(Request $request, CentralKitchen $central_kitchen)
    {
        $request->validate([
            'short_code' => ['required', 'string', 'max:10', Rule::unique('central_kitchens')->ignore($central_kitchen)],
            'name' => ['required', 'string', 'max:191', Rule::unique('central_kitchens')->ignore($central_kitchen)],
            'contact' => ['nullable', 'string', 'max:14'],
            'address' => ['nullable', 'string', 'max:500'],
        ]);

        DB::beginTransaction();

        $central_kitchen->name = $request->name;
        $central_kitchen->short_code = $request->short_code;
        $central_kitchen->phone = $request->contact;
        $central_kitchen->address = $request->address;
        $central_kitchen->update();

        DB::commit();
        CacheCentralKitchen::forget();

        return back()->with('success', 'CentralKitchen updated successfully.');
    }

    public function access()
    {
        $users = CacheUser::get();
        $central_kitchens = CacheCentralKitchen::get();

        $params = [
            'users' => $users,
            'central_kitchens' => $central_kitchens,
        ];

        return Inertia::render('Manage/CentralKitchen/Access', $params);
    }

    public function accessUpdate(Request $request, CentralKitchen $central_kitchen)
    {
        $request->validate([
            'users' => ['required', 'array'],
        ]);

        DB::beginTransaction();

        foreach ($request->users as $user) {

            $central_kitchen_access = $user['central_kitchen_access'];

            foreach ($central_kitchen_access as $central_kitchen => $access) {
                CentralKitchenAccess::updateOrCreate([
                    'user_id' => $user['id'],
                    'central_kitchen_id' => $central_kitchen,
                ], [
                    'is_checked' => $access,
                ]);
            }
        }

        DB::commit();
        CacheUser::forget();
        CacheCentralKitchenAccess::forget();

        return back()->with('success', __('CentralKitchen Access modified successfully!'));
    }

    /**
     * Delete the specified resource from storage.
     *
     * @return Response
     */
    public function destroy(CentralKitchen $central_kitchen)
    {
        DB::beginTransaction();
        if ($central_kitchen->orders->count() > 0) {
            return back()->with('fail', __('CentralKitchen removing failed!'));
        }
        $central_kitchen->delete();
        DB::commit();
        CacheCentralKitchen::forget();

        return redirect()->route('central.index')->with('success', __('CentralKitchen removed successfully!'));
    }
}
