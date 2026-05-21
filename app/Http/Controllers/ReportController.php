<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __invoke(Request $request): View
    {
        $month = (int) $request->integer('month', now()->month);
        $year = (int) $request->integer('year', now()->year);
        $cutoff = $this->reportCutoff($request);

        $sales = $this->paidSalesForMonth($request, $month, $year, $cutoff)
            ->with('cashier')
            ->latest('sold_at')
            ->paginate(10)
            ->withQueryString();

        $baseQuery = $this->paidSalesForMonth($request, $month, $year, $cutoff);

        $chartData = collect(range(1, 12))->map(function (int $monthNumber) use ($request, $year, $cutoff) {
            return [
                'label' => \DateTime::createFromFormat('!m', (string) $monthNumber)->format('M'),
                'total' => (int) $this->paidSalesForMonth($request, $monthNumber, $year, $cutoff)->sum('total'),
            ];
        });

        $topProducts = SaleItem::query()
            ->selectRaw('product_name, SUM(quantity) as total_qty, SUM(line_total) as total_revenue')
            ->whereHas('sale', fn (Builder $query) => $this->applySaleVisibility(
                $query
                    ->where('status', 'paid')
                    ->whereYear('sold_at', $year)
                    ->whereMonth('sold_at', $month),
                $request,
                $cutoff
            )
            )
            ->groupBy('product_name')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        return view('reports.index', [
            'sales' => $sales,
            'month' => $month,
            'year' => $year,
            'totalRevenue' => (clone $baseQuery)->sum('total'),
            'totalTransactions' => (clone $baseQuery)->count(),
            'averageBasket' => (int) (clone $baseQuery)->avg('total'),
            'monthlyChart' => [
                'labels' => $chartData->pluck('label'),
                'values' => $chartData->pluck('total'),
            ],
            'topProducts' => $topProducts,
        ]);
    }

    public function cashierPerformance(Request $request): View
    {
        $month = (int) $request->integer('month', now()->month);
        $year = (int) $request->integer('year', now()->year);
        $cutoff = $this->reportCutoff($request);

        $sales = $this->paidSalesForMonth($request, $month, $year, $cutoff)
            ->with('cashier')
            ->get();

        $performance = $sales->groupBy('cashier_id')->map(function ($cashierSales) {
            $paymentMethods = $cashierSales->groupBy('payment_method');
            $favoriteMethod = $paymentMethods->sortByDesc(fn ($group) => $group->count())->keys()->first();

            return [
                'cashier_name' => $cashierSales->first()->cashier?->name ?? 'Kasir Terhapus',
                'total_transactions' => $cashierSales->count(),
                'total_revenue' => $cashierSales->sum('total'),
                'avg_transaction' => $cashierSales->avg('total'),
                'favorite_payment_method' => strtoupper($favoriteMethod),
            ];
        })->sortByDesc('total_revenue')->values();

        return view('reports.cashier', compact('performance', 'month', 'year'));
    }

    public function exportPdf(Request $request): View
    {
        $data = $this->reportData($request, paginate: false);

        return view('reports.print', $data + ['format' => 'PDF']);
    }

    public function exportExcel(Request $request): StreamedResponse
    {
        $data = $this->reportData($request, paginate: false);
        $filename = "laporan-penjualan-{$data['year']}-".str_pad((string) $data['month'], 2, '0', STR_PAD_LEFT).'.xls';

        return response()->streamDownload(function () use ($data) {
            echo view('reports.excel', $data)->render();
        }, $filename, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
        ]);
    }

    public function receipt(Request $request, Sale $sale): View
    {
        abort_if($request->user()->isCashier() && $sale->cashier_id !== $request->user()->id, 403);

        $cutoff = $this->reportCutoff($request);
        abort_if($cutoff && $sale->sold_at?->lt($cutoff), 403);

        $sale->load(['items', 'cashier', 'store']);

        return view('reports.receipt', compact('sale'));
    }

    private function reportData(Request $request, bool $paginate): array
    {
        $month = (int) $request->integer('month', now()->month);
        $year = (int) $request->integer('year', now()->year);
        $cutoff = $this->reportCutoff($request);

        $query = $this->paidSalesForMonth($request, $month, $year, $cutoff)
            ->with('cashier')
            ->latest('sold_at');

        $sales = $paginate
            ? $query->paginate(10)->withQueryString()
            : $query->get();

        $baseQuery = $this->paidSalesForMonth($request, $month, $year, $cutoff);

        $topProducts = SaleItem::query()
            ->selectRaw('product_name, SUM(quantity) as total_qty, SUM(line_total) as total_revenue')
            ->whereHas('sale', fn (Builder $query) => $this->applySaleVisibility(
                $query
                    ->where('status', 'paid')
                    ->whereYear('sold_at', $year)
                    ->whereMonth('sold_at', $month),
                $request,
                $cutoff
            )
            )
            ->groupBy('product_name')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        return [
            'sales' => $sales,
            'month' => $month,
            'year' => $year,
            'totalRevenue' => (clone $baseQuery)->sum('total'),
            'totalTransactions' => (clone $baseQuery)->count(),
            'averageBasket' => (int) (clone $baseQuery)->avg('total'),
            'topProducts' => $topProducts,
        ];
    }

    private function paidSalesForMonth(Request $request, int $month, int $year, ?Carbon $cutoff): Builder
    {
        return $this->applySaleVisibility(
            Sale::query()
                ->where('status', 'paid')
                ->whereYear('sold_at', $year)
                ->whereMonth('sold_at', $month),
            $request,
            $cutoff
        );
    }

    private function applySaleVisibility(Builder $query, Request $request, ?Carbon $cutoff): Builder
    {
        $user = $request->user();

        if ($user?->isCashier()) {
            $query->where('cashier_id', $user->id);
        }

        if ($cutoff) {
            $query->where('sold_at', '>=', $cutoff);
        }

        return $query;
    }

    private function reportCutoff(Request $request): ?Carbon
    {
        $retentionDays = $request->user()?->tenant?->plan?->report_retention_days;

        return $retentionDays ? now()->subDays((int) $retentionDays)->startOfDay() : null;
    }
}
