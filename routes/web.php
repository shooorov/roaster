<?php

use App\Http\Controllers\Account\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SummaryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::post('/', [DashboardController::class, 'use'])->name('use');
Route::get('/', function (Request $request) {
    if (! $request->user()) {
        return redirect()->route('dashboard');
    }

    if ($request->user()->is_waiter) {
        return redirect()->route('pos.create');
    } elseif ($request->user()->is_barista || $request->user()->is_chef) {
        return redirect()->route('production.index');
    } elseif ($request->user()->is_rider) {
        return redirect()->route('delivery.index');
    }

    return redirect()->route('dashboard')->with('fail', session()->get('fail'));

})->name('index');

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/order-approval', [DashboardController::class, 'orderApproval'])->name('order.approval');
Route::post('/order-approval', [DashboardController::class, 'orderApprovalUpdate'])->name('order.approval.update');

Route::name('summary.')->prefix('/summary')->group(function () {
    Route::get('/overview', [SummaryController::class, 'overview'])->name('overview');
    Route::get('/report/hourly', [SummaryController::class, 'saleHourly'])->name('sale_hourly');
    Route::get('/sale-products', [SummaryController::class, 'saleProduct'])->name('sale_products');
    Route::get('/sale-items', [SummaryController::class, 'saleItem'])->name('sale_items');
});

Route::name('profile.')->prefix('profile')->middleware(['auth', 'verified'])->group(function () {
    Route::get('', [ProfileController::class, 'profile'])->name('index');
    Route::post('update', [ProfileController::class, 'update'])->name('update');
    Route::get('password', [ProfileController::class, 'password'])->name('password');
    Route::patch('password/update', [ProfileController::class, 'updatePassword'])->name('password.update');
});

require __DIR__.'/web/dev.php';

require __DIR__.'/web/report.php';

require __DIR__.'/web/operation.php';

require __DIR__.'/web/manage.php';

require __DIR__.'/web/system.php';

require __DIR__.'/auth.php';
