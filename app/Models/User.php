<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Http\Cache\CacheBranch;
use App\Http\Cache\CacheBranchAccess;
use App\Http\Cache\CacheCentralKitchen;
use App\Http\Cache\CacheCentralKitchenAccess;
use App\Http\Cache\CacheEmailDigest;
use App\Http\Cache\CacheEmployee;
use App\Http\Cache\CacheRole;
use App\Traits\UserTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;
    use UserTrait, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'mobile',
        'address',
        'is_admin',
        'is_waiter',
        'is_chef',
        'is_barista',
        'is_rider',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'access' => 'array',
    ];

    /**
     * The accessors to append to the Model's array form.
     *
     * @var array
     */
    protected $appends = [
        'type',
        'types',
        'is_cook',
        'statuses',
        'image_url',
        'role_name',
        'access_name',
        'email_digest',
        'branch_access',
        'central_kitchen_access',
        'image_default',
        'employee_id',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the role name.
     */
    public function isCook(): Attribute
    {
        return Attribute::get(fn () => $this->is_chef || $this->is_barista);
    }

    /**
     * Get the role name.
     */
    public function roleName(): Attribute
    {
        $role_id = $this->role_id;
        $role_name = CacheRole::find($role_id)?->name;

        $role_name = Str::of($role_name)->lower()->replace(' ', '_')->__toString();

        $role_name = $this->is_rider ? 'rider' : $role_name;
        $role_name = $this->is_chef ? 'chef' : $role_name;
        $role_name = $this->is_barista ? 'barista' : $role_name;
        $role_name = $this->is_waiter ? 'waiter' : $role_name;
        $role_name = $this->is_admin ? 'super_admin' : $role_name;

        return Attribute::get(fn () => $role_name);
    }

    /**
     * Get the role name.
     */
    public function accessName(): Attribute
    {
        $access_name = '';
        $access_name = $this->is_barista ? 'barista' : $access_name;
        $access_name = $this->is_chef ? 'chef' : $access_name;
        $access_name = $this->is_waiter ? 'waiter' : $access_name;
        $access_name = $this->is_admin ? 'admin' : $access_name;

        return Attribute::get(fn () => $access_name);
    }

    /**
     * Get the role name.
     */
    public function branchAccess(): Attribute
    {
        $access = [];
        $user_id = $this->id;
        $is_admin = $this->is_admin;

        foreach (CacheBranch::get() as $branch) {
            $is_checked = CacheBranchAccess::get()->first(fn ($ba) => $ba->user_id == $user_id && $ba->branch_id == $branch->id)?->is_checked;
            $access[$branch->id] = (bool) ($is_admin || $is_checked);
        }

        return Attribute::get(fn () => $access);
    }

    /**
     * Get the role name.
     */
    public function centralKitchenAccess(): Attribute
    {
        $access = [];
        $user_id = $this->id;
        $is_admin = $this->is_admin;

        foreach (CacheCentralKitchen::get() as $central_kitchen) {
            $is_checked = CacheCentralKitchenAccess::get()->first(fn ($ba) => $ba->user_id == $user_id && $ba->central_kitchen_id == $central_kitchen->id)?->is_checked;
            $access[$central_kitchen->id] = (bool) ($is_admin || $is_checked);
        }

        return Attribute::get(fn () => $access);
    }

    /**
     * Get the role name.
     */
    public function emailDigest(): Attribute
    {
        $digest = [];
        $user_id = $this->id;
        $is_admin = $this->is_admin;

        foreach (CacheBranch::get() as $branch) {
            $is_checked = CacheEmailDigest::get()->first(fn ($ba) => $ba->user_id == $user_id && $ba->branch_id == $branch->id)?->is_checked;
            $digest[$branch->id] = (bool) $is_checked;
            // $digest[$branch->id] = (bool) ($is_admin || $is_checked);
        }

        return Attribute::get(fn () => $digest);
    }

    /**
     * Get the image image.
     */
    public function imageDefault(): Attribute
    {
        return Attribute::get(fn () => asset('img/avatar.jpg'));
    }

    /**
     * Get the image image.
     */
    public function employeeId(): Attribute
    {
        return Attribute::get(fn () => CacheEmployee::get()->first(fn ($e) => $e->email == $this->email)?->id);
    }
}
