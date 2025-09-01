<?php

namespace App\Http\Controllers\Manage;

use App\Http\Cache\CacheBranch;
use App\Http\Cache\CacheBranchAccess;
use App\Http\Cache\CacheEmailDigest;
use App\Http\Cache\CacheRole;
use App\Http\Cache\CacheUser;
use App\Http\Controllers\Controller;
use App\Models\BranchAccess;
use App\Models\EmailDigest;
use App\Models\Status;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class UserController extends Controller
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
     * Show the User list page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $users = CacheUser::get();
        $branches = CacheBranch::get();

        $params = [
            'branches' => $branches,
            'users' => $users,
        ];

        return Inertia::render('Manage/User/Index', $params);
    }

    /**
     * Show the User create.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function create()
    {
        $params = [
            'user' => new User,
            'roles' => CacheRole::get(),
            'branches' => CacheBranch::get(),
        ];

        return Inertia::render('Manage/User/Create', $params);
    }

    /**
     * Create new resource in storage.
     *
     * @param  User  $user
     * @return Response
     */
    public function store(Request $request)
    {
        $user_type = $request->type;
        $is_other = $user_type == 'other';
        $is_barista = $user_type == 'barista';
        $is_chef = $user_type == 'chef';
        $is_waiter = $user_type == 'waiter';
        $is_rider = $user_type == 'rider';

        $request->validate([
            'type' => ['required', 'string'],
            'role_id' => [$is_other ? 'required' : 'nullable', 'exists:roles,id'],
            'name' => ['required', 'string', 'max:191'],
            'email' => ['required', 'email', 'max:191', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'mobile' => ['nullable', 'string', 'max:191'],
            'address' => ['nullable', 'string', 'max:500'],
            'branches' => ['required', 'array'],
        ]);

        DB::beginTransaction();
        $user = new User;
        $user->role_id = $is_other ? $request->role_id : null;
        $user->is_barista = $is_barista;
        $user->is_chef = $is_chef;
        $user->is_waiter = $is_waiter;
        $user->is_rider = $is_rider;

        $user->name = $request->name;
        $user->email = $request->email;
        $user->mobile = $request->mobile;
        $user->address = $request->address;
        $user->status = $user->default_status;
        $user->password = bcrypt($request->password);
        $user->save();

        foreach ($request->branches as $branch) {
            BranchAccess::updateOrCreate([
                'user_id' => $user->id,
                'branch_id' => $branch['id'],
            ], [
                'is_checked' => $branch['is_checked'] ?? false,
            ]);
        }

        DB::commit();
        CacheUser::forget();
        CacheBranchAccess::forget();

        return redirect()->route('user.index')->with('success', 'User created successfully.');
    }

    /**
     * Show the User detail page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function show(User $user)
    {
        $params = [
            'user' => $user,
        ];

        return Inertia::render('Manage/User/Show', $params);
    }

    /**
     * Show the User edit page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function edit(User $user)
    {
        $params = [
            'user' => $user,
            'roles' => CacheRole::get(),
            'branches' => CacheBranch::get(),
        ];

        return Inertia::render('Manage/User/Edit', $params);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return Response
     */
    public function update(Request $request, User $user)
    {
        $user_type = $request->type;
        $is_other = $user_type == 'other';
        $is_barista = $user_type == 'barista';
        $is_chef = $user_type == 'chef';
        $is_waiter = $user_type == 'waiter';
        $is_rider = $user_type == 'rider';

        $request->validate([
            'type' => ['required', 'string'],
            'role_id' => [$is_other ? 'required' : 'nullable', 'exists:roles,id'],
            'name' => ['required', 'string', 'max:191'],
            'email' => ['required', 'email', 'max:191', Rule::unique('users')->ignore($user)],
            'password' => ['nullable', 'string', 'min:8'],
            'mobile' => ['nullable', 'string', 'max:191'],
            'address' => ['nullable', 'string', 'max:500'],
            'branches' => ['required', 'array'],
        ]);

        DB::beginTransaction();
        $user->role_id = $is_other ? $request->role_id : null;
        $user->is_barista = $is_barista;
        $user->is_chef = $is_chef;
        $user->is_waiter = $is_waiter;
        $user->is_rider = $is_rider;

        $user->name = $request->name;
        $user->email = $request->email;
        $user->mobile = $request->mobile;
        $user->address = $request->address;

        if ($request->password) {
            $user->password = bcrypt($request->password);
        }
        $user->save();

        foreach ($request->branches as $branch) {
            BranchAccess::updateOrCreate([
                'user_id' => $user->id,
                'branch_id' => $branch['id'],
            ], [
                'is_checked' => $branch['is_checked'] ?? false,
            ]);
        }

        DB::commit();
        CacheUser::forget();
        CacheBranchAccess::forget();

        return back()->with('success', 'User updated successfully.');
    }

    /**
     * Delete the specified resource from storage.
     *
     * @return Response
     */
    public function destroy(User $user)
    {
        DB::beginTransaction();
        $user->delete();

        DB::commit();
        CacheUser::forget();

        return redirect()->route('user.index')->with('success', __('User removed successfully!'));
    }

    /**
     * Change status the specified resource in storage.
     *
     * @return Response
     */
    public function updateStatus(Request $request, User $user)
    {
        if (! array_key_exists($request->status, $user->statuses)) {
            return back()->with('fail', 'Status changing request failed! Invalid status!');
        }

        DB::beginTransaction();
        $user->changeStatuses()->save(new Status([
            'previous_status' => $user->status ?? '',
            'changed_status' => $request->status,
            'user_id' => Auth::id(),
        ]));
        $user->status = $request->status;
        $user->save();
        DB::commit();
        CacheUser::forget();

        return back()->with('success', 'Status changed to "'.$user->statuses[$request->status].'" successfully');
    }

    public function digest()
    {
        $users = CacheUser::get();
        $branches = CacheBranch::get();

        $params = [
            'users' => $users,
            'branches' => $branches,
        ];

        return Inertia::render('Manage/User/EmailDigest', $params);
    }

    public function digestUpdate(Request $request, User $user)
    {
        $request->validate([
            'users' => ['required', 'array'],
        ]);

        DB::beginTransaction();

        foreach ($request->users as $user) {
            $email_digest = $user['email_digest'];

            foreach ($email_digest as $branch => $access) {
                EmailDigest::updateOrCreate([
                    'user_id' => $user['id'],
                    'branch_id' => $branch,
                ], [
                    'is_checked' => $access,
                ]);
            }
        }

        DB::commit();
        CacheUser::forget();
        CacheEmailDigest::forget();

        return back()->with('success', __('User Email Digest modified successfully!'));
    }

    /**
     * User login page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function login(User $user)
    {
        Auth::loginUsingId($user->id);

        return redirect()->route('index');
    }
}
