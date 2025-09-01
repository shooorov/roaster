<?php

namespace App;

use App\Http\Cache\CacheBranch;
use App\Http\Cache\CacheBranchAccess;
use Illuminate\Support\Facades\Auth;

class UseBranch
{
    public static function set($branch)
    {
        session()->put('branch', $branch?->id);
    }

    public static function get()
    {
        return CacheBranch::get()->first(fn ($branch) => $branch->id == self::id());
    }

    public static function id()
    {
        return session()->get('branch');
    }

    public static function take($field)
    {
        return self::get()?->$field;
    }

    public static function all()
    {
        $user = Auth::user();
        $branches = CacheBranch::get();

        if ($user?->is_admin) {
            return $branches;
        }

        $valid_ids = CacheBranchAccess::get()->filter(function ($branch_access) use ($user) {
            return $branch_access->user_id == $user?->id && $branch_access->is_checked;
        })->pluck('branch_id')->toArray();

        return $branches->filter(function ($branch) use ($valid_ids) {
            return in_array($branch->id, $valid_ids);
        });
    }
}
