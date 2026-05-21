<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AccountProfileController;
use App\Http\Controllers\BusinessProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\LegalPageController;
use App\Http\Controllers\PlatformAdminController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\POSController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StoreContextController;
use App\Http\Controllers\StockMutationController;
use App\Http\Controllers\StockHistoryController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\UserManagementController;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Store;
use App\Models\Tenant;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route(auth()->user()->canPermission('platform.manage') ? 'platform.index' : 'dashboard');
    }

    $paidSales = Schema::hasTable('sales')
        ? Sale::withoutGlobalScopes()->where('status', 'paid')
        : null;

    return view('welcome', [
        'landingStats' => [
            'activeStores' => Schema::hasTable('stores') ? Store::withoutGlobalScopes()->count() : 0,
            'processedRevenue' => $paidSales ? (int) (clone $paidSales)->sum('total') : 0,
            'managedProducts' => Schema::hasTable('products') ? Product::withoutGlobalScopes()->count() : 0,
            'activeTenants' => Schema::hasTable('tenants') ? Tenant::withoutGlobalScopes()->whereIn('status', ['trial', 'active'])->count() : 0,
        ],
    ]);
})->name('home');

Route::view('/demo', 'demo')->name('demo');
Route::get('/terms', [LegalPageController::class, 'terms'])->name('legal.terms');
Route::get('/privacy', [LegalPageController::class, 'privacy'])->name('legal.privacy');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:10,1')
        ->name('login.store');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])
        ->middleware('throttle:5,1')
        ->name('register.store');
    Route::get('/forgot-password', [PasswordResetController::class, 'request'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendLink'])
        ->middleware('throttle:5,1')
        ->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'reset'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'update'])
        ->middleware('throttle:5,1')
        ->name('password.update');
    Route::get('/invite/{token}', [InvitationController::class, 'show'])->name('invite.show');
    Route::post('/invite/{token}', [InvitationController::class, 'accept'])->name('invite.accept');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/akun-saya', [AccountProfileController::class, 'edit'])->name('account.edit');
    Route::post('/akun-saya', [AccountProfileController::class, 'update'])->name('account.update');
    Route::get('/platform', [PlatformAdminController::class, 'index'])
        ->middleware('permission:platform.manage')
        ->name('platform.index');
    Route::post('/platform/tenants/{tenant}', [PlatformAdminController::class, 'updateTenant'])
        ->middleware('permission:platform.manage')
        ->name('platform.tenants.update');
    Route::delete('/platform/tenants/{tenant}', [PlatformAdminController::class, 'destroyTenant'])
        ->middleware('permission:platform.manage')
        ->name('platform.tenants.destroy');
    Route::post('/platform/plans', [PlatformAdminController::class, 'storePlan'])
        ->middleware('permission:platform.manage')
        ->name('platform.plans.store');
    Route::put('/platform/plans/{plan}', [PlatformAdminController::class, 'updatePlan'])
        ->middleware('permission:platform.manage')
        ->name('platform.plans.update');
    Route::get('/dashboard', DashboardController::class)
        ->middleware('permission:dashboard.view')
        ->name('dashboard');
    Route::get('/products', [ProductController::class, 'index'])
        ->middleware('permission:stock.mutate')
        ->name('products.index');
    Route::resource('products', ProductController::class)
        ->except(['index', 'show'])
        ->middleware('permission:products.manage');
    Route::get('/users/activities', [ActivityLogController::class, 'index'])
        ->middleware('permission:activity_log.view')
        ->name('users.activities');
    Route::resource('users', UserManagementController::class)
        ->except('show')
        ->middleware('permission:users.manage');
    Route::post('/users/invite', [InvitationController::class, 'store'])
        ->middleware('permission:users.invite')
        ->name('users.invite');
    Route::get('/subscription', [SubscriptionController::class, 'index'])
        ->middleware('permission:subscription.manage')
        ->name('subscription.index');
    Route::post('/subscription/plan', [SubscriptionController::class, 'updatePlan'])
        ->middleware('permission:subscription.manage')
        ->name('subscription.plan.update');
    Route::post('/subscription/tenant', [SubscriptionController::class, 'updateTenant'])
        ->middleware('permission:subscription.manage')
        ->name('subscription.tenant.update');
    Route::get('/profil-bisnis', [BusinessProfileController::class, 'edit'])
        ->middleware('permission:subscription.manage')
        ->name('business-profile.edit');
    Route::post('/profil-bisnis', [BusinessProfileController::class, 'update'])
        ->middleware('permission:subscription.manage')
        ->name('business-profile.update');
    Route::post('/toko-aktif', [StoreContextController::class, 'update'])
        ->name('stores.context.update');
    Route::post('/products/{product}/stock', [StockMutationController::class, 'store'])
        ->middleware('permission:stock.mutate')
        ->name('products.stock.store');
    Route::get('/pos', [POSController::class, 'index'])
        ->middleware('permission:pos.access')
        ->name('pos.index');
    Route::post('/pos', [POSController::class, 'store'])
        ->middleware('permission:pos.access')
        ->name('pos.store');
    Route::post('/sales/{sale}/void', [POSController::class, 'void'])
        ->middleware('permission:sales.void')
        ->name('sales.void');
    Route::get('/laporan', ReportController::class)
        ->middleware('permission:reports.view')
        ->name('reports.index');
    Route::get('/laporan/export/pdf', [ReportController::class, 'exportPdf'])
        ->middleware('permission:reports.export')
        ->name('reports.export.pdf');
    Route::get('/laporan/export/excel', [ReportController::class, 'exportExcel'])
        ->middleware('permission:reports.export')
        ->name('reports.export.excel');
    Route::get('/laporan/struk/{sale}', [ReportController::class, 'receipt'])
        ->middleware('permission:sales.receipt')
        ->name('reports.receipt');
    Route::get('/laporan/kasir', [ReportController::class, 'cashierPerformance'])
        ->middleware('permission:reports.cashier')
        ->name('reports.cashier');
    Route::get('/riwayat-stok', StockHistoryController::class)
        ->middleware('permission:stock_history.view')
        ->name('stock-history.index');
});
