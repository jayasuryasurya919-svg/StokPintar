<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\StockMutation;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $user = auth()->user();
        $ownSalesOnly = $user?->isCashier();
        $stockFocused = $user?->role === User::ROLE_STAFF_GUDANG;
        $cashierFocused = $user?->role === User::ROLE_CASHIER;
        $reportFocused = $user?->role === User::ROLE_VIEWER;

        // Chart 7 hari terakhir
        $chartData = collect(range(6, 0))->map(function (int $daysAgo) use ($ownSalesOnly, $user) {
            $date = today()->subDays($daysAgo);
            return [
                'label' => $date->locale('id')->isoFormat('ddd, D MMM'),
                'total' => (int) Sale::query()
                    ->where('status', 'paid')
                    ->when($ownSalesOnly, fn ($query) => $query->where('cashier_id', $user?->id))
                    ->whereDate('sold_at', $date)
                    ->sum('total'),
            ];
        });

        return view('dashboard', [
            'totalProducts'   => Product::query()->count(),
            'lowStockProducts'=> Product::query()
                ->whereColumn('stock', '<=', 'minimum_stock')
                ->orderBy('stock')->limit(8)->get(),
            'lowStockCount'   => Product::query()->whereColumn('stock', '<=', 'minimum_stock')->count(),
            'todaySalesCount' => Sale::query()->where('status', 'paid')->when($ownSalesOnly, fn ($query) => $query->where('cashier_id', $user?->id))->whereDate('sold_at', today())->count(),
            'todayRevenue'    => Sale::query()->where('status', 'paid')->when($ownSalesOnly, fn ($query) => $query->where('cashier_id', $user?->id))->whereDate('sold_at', today())->sum('total'),
            'monthRevenue'    => Sale::query()->where('status', 'paid')
                ->when($ownSalesOnly, fn ($query) => $query->where('cashier_id', $user?->id))
                ->whereYear('sold_at', now()->year)->whereMonth('sold_at', now()->month)->sum('total'),
            'monthSalesCount' => Sale::query()->where('status', 'paid')
                ->when($ownSalesOnly, fn ($query) => $query->where('cashier_id', $user?->id))
                ->whereYear('sold_at', now()->year)->whereMonth('sold_at', now()->month)->count(),
            'recentMutations' => StockMutation::query()->with('product')->latest()->limit(6)->get(),
            'recentSales'     => Sale::query()->with('cashier')->where('status', 'paid')->when($ownSalesOnly, fn ($query) => $query->where('cashier_id', $user?->id))->latest()->limit(5)->get(),
            'ownSalesOnly'    => $ownSalesOnly,
            'stockFocused'    => $stockFocused,
            'cashierFocused'  => $cashierFocused,
            'reportFocused'   => $reportFocused,
            'revenueChart'    => [
                'labels' => $chartData->pluck('label'),
                'values' => $chartData->pluck('total'),
            ],
        ]);
    }
}
