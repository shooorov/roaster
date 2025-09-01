<?php

namespace App\Http\Controllers\Manage;

use App\Http\Cache\CacheBranch;
use App\Http\Cache\CacheBranchAccess;
use App\Http\Cache\CacheProductCategory;
use App\Http\Cache\CacheRole;
use App\Http\Cache\CacheTokenAmount;
use App\Http\Cache\CacheUser;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\BranchAccess;
use App\Models\TokenAmount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class BranchController extends Controller
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
     * Show the Branch list page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $all_branches = CacheBranch::get();

        $params = [
            'all_branches' => $all_branches,
        ];

        return Inertia::render('Manage/Branch/Index', $params);
    }

    /**
     * Show the Branch create.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function create()
    {
        $params = [];

        return Inertia::render('Manage/Branch/Create', $params);
    }

    /**
     * Create new resource in storage.
     *
     * @param  Branch  $branch
     * @return Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'short_code' => ['required', 'string', 'max:10', 'unique:branches,short_code'],
            'name' => ['required', 'string', 'max:191', 'unique:branches,name'],
            'address' => ['nullable', 'string', 'max:500'],
            'vat_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'opening_hours' => ['nullable', 'string', 'max:191'],
            'pos_end_line' => ['nullable', 'string', 'max:500'],
        ]);

        DB::beginTransaction();
        $branch = new Branch;
        $branch->name = $request->name;
        $branch->short_code = $request->short_code;
        $branch->phone = $request->contact_number;
        $branch->address = $request->address;
        $branch->vat_rate = $request->vat_rate ?? 0;
        $branch->opening_hours = $request->opening_hours;
        $branch->pos_end_line = $request->pos_end_line;
        $branch->save();

        DB::commit();
        CacheBranch::forget();

        return redirect()->route('branch.index')->with('success', 'Branch created successfully.');
    }

    /**
     * Show the Branch detail page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function show(Branch $branch)
    {
        $params = [
            'branch' => $branch,
        ];

        return Inertia::render('Manage/Branch/Show', $params);
    }

    /**
     * Show the Branch edit page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function edit(Branch $branch)
    {
        $params = [
            'selected_branch' => $branch,
            'module' => [
                'production' => config('module.production'),
                'delivery' => config('module.delivery'),
            ],
        ];

        return Inertia::render('Manage/Branch/Edit', $params);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return Response
     */
    public function update(Request $request, Branch $branch)
    {
        $request->validate([
            'short_code' => ['required', 'string', 'max:10', Rule::unique('branches')->ignore($branch)],
            'name' => ['required', 'string', 'max:191', Rule::unique('branches')->ignore($branch)],
            'address' => ['nullable', 'string', 'max:500'],
            'vat_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'opening_hours' => ['nullable', 'string', 'max:191'],
            'service_cost' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'barista_target' => ['nullable', 'string', 'max:191'],
            'chef_target' => ['nullable', 'string', 'max:191'],
            'delivery_cost' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'pos_end_line' => ['nullable', 'string', 'max:500'],
        ]);

        DB::beginTransaction();

        $branch->name = $request->name;
        $branch->short_code = $request->short_code;
        $branch->phone = $request->contact_number;
        $branch->address = $request->address;
        $branch->vat_rate = $request->vat_rate ?? 0;
        $branch->opening_hours = $request->opening_hours;
        $branch->service_cost = $request->service_cost ?? 0;
        $branch->barista_target = $request->barista_target;
        $branch->chef_target = $request->chef_target;
        $branch->delivery_cost = $request->delivery_cost ?? 0;
        $branch->pos_end_line = $request->pos_end_line;
        $branch->update();

        DB::commit();
        CacheBranch::forget();

        return back()->with('success', 'Branch updated successfully.');
    }

    /**
     * Delete the specified resource from storage.
     *
     * @return Response
     */
    public function destroy(Branch $branch)
    {
        DB::beginTransaction();
        if ($branch->orders->count() > 0) {
            return back()->with('fail', __('Branch removing failed!'));
        }
        $branch->delete();
        DB::commit();
        CacheBranch::forget();

        return redirect()->route('branch.index')->with('success', __('Branch removed successfully!'));
    }

    public function access()
    {
        // $users = CacheUser::get()->where('status', 'active')->values();
        $users = CacheUser::get();
        $all_branches = CacheBranch::get();
        $all_roles = CacheRole::get();

        $params = [
            'users' => $users,
            'all_branches' => $all_branches,
            'all_roles' => $all_roles,
        ];

        return Inertia::render('Manage/Branch/Access', $params);
    }

    public function accessUpdate(Request $request, Branch $branch)
    {
        $request->validate([
            'users' => ['required', 'array'],
        ]);

        DB::beginTransaction();

        foreach ($request->users as $user) {
            $branch_access = $user['branch_access'];

            foreach ($branch_access as $branch => $access) {
                BranchAccess::updateOrCreate([
                    'user_id' => $user['id'],
                    'branch_id' => $branch,
                ], [
                    'is_checked' => $access,
                ]);
            }
        }

        DB::commit();
        CacheUser::forget();
        CacheBranchAccess::forget();

        return back()->with('success', __('Branch Access modified successfully!'));
    }

    public function token()
    {
        $categories = CacheProductCategory::get();
        $all_branches = CacheBranch::get();

        $params = [
            'categories' => $categories,
            'all_branches' => $all_branches,
        ];

        return Inertia::render('Manage/Branch/Token', $params);
    }

    public function tokenUpdate(Request $request, Branch $branch)
    {
        $request->validate([
            'categories' => ['required', 'array'],
        ]);

        DB::beginTransaction();

        foreach ($request->categories as $user) {

            $token_amount = $user['token_amount'];

            foreach ($token_amount as $branch => $amount) {
                TokenAmount::updateOrCreate([
                    'product_category_id' => $user['id'],
                    'branch_id' => $branch,
                ], [
                    'amount' => $amount ?? 0,
                ]);
            }
        }

        DB::commit();
        CacheUser::forget();
        CacheTokenAmount::forget();

        return back()->with('success', __('Token amount modified successfully!'));
    }
}
