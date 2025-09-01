<?php

namespace App\Http\Middleware;

use App\UseBranch;
use Closure;

class PreventWithoutBranchAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (UseBranch::id()) {
            return $next($request);
        }
        // redirect()->route('index')
        return back()->with('fail', 'Please select branch to proceed');
    }
}
