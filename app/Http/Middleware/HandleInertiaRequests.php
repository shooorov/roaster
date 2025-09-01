<?php

namespace App\Http\Middleware;

use App\Http\Cache\CacheSetting;
use App\Navigation;
use App\UseBranch;
use App\UseKitchen;
use App\UseSystemName;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Middleware;
use Tightenco\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     *
     * @return string|null
     */
    public function version(Request $request)
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array
     */
    public function share(Request $request)
    {
        $branches = UseBranch::all();
        if (empty(UseBranch::id()) && $branches->count() == 1) {
            UseBranch::set($branches->first());
        }

        $kitchen_logs = UseKitchen::all();
        $user = Auth::user();

        if ($kitchen_logs->count() == 1) {
            UseKitchen::set($kitchen_logs->where('user_id', $user->id)->first());
        }

        $setting = CacheSetting::get()->pluck('value', 'name')->toArray();

        $logo = $setting['company_logo'] ?? '/cr_logo.png';
        //$logo_invoice = $setting['company_logo_invoice'] ?? '/logo-invoice.png';
        $logo_invoice = $setting['company_logo_invoice'] ?? '/cr_logo.png';

        return array_merge(parent::share($request), [
            'alertMessage' => [
                'fail' => fn () => $request->session()->get('fail'),
                'success' => fn () => $request->session()->get('success'),
                'successItems' => fn () => $request->session()->get('successItems'),
            ],
            'app' => [
                'base' => config('app.url'),
                'name' => config('app.name'),
                'avatar' => asset('/img/avatar.jpg'),
                'logo' => file_exists(public_path($logo)) ? asset($logo) : '',
                'logo_invoice' => file_exists(public_path($logo_invoice)) ? asset($logo_invoice) : '',
                'product_image' => asset('/img/product.jpg'),
            ],
            'auth' => [
                'user' => $request->user(),
            ],
            'branch' => UseBranch::get(),
            'branches' => $branches,
            'kitchen' => UseKitchen::get(),
            'kitchen_logs' => $kitchen_logs,
            'module' => config('module'),
            'navigation' => [
                'menu' => Navigation::get(UseBranch::id()),
                'routes' => Navigation::routes(),
            ],
            // 'notifications' => [
            //     'all'           => $request->user()?->notifications->map(function ($notification) {
            //         return [
            //             'id'                => $notification->id,
            //             'read_at'           => $notification->read_at,
            //             'data'              => $notification->data,
            //             'created_from_now'  => now()->parse($notification->created_at)->longAbsoluteDiffForHumans() . ' ago',
            //         ];
            //     }),
            //     'unread_count' => $request->user()?->unreadNotifications->count(),
            // ],
            'setting' => $setting,
            'string_change' => UseSystemName::get(),
            'ziggy' => function () use ($request) {
                return array_merge((new Ziggy)->toArray(), [
                    'location' => $request->url(),
                ]);
            },
        ]);
    }
}
