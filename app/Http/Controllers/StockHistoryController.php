<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMutation;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StockHistoryController extends Controller
{
    public function __invoke(Request $request): View
    {
        $filters = $request->validate([
            'product_id' => [
                'nullable',
                'integer',
                Rule::exists('products', 'id')->where('tenant_id', $request->user()->tenant_id),
            ],
            'type' => ['nullable', Rule::in([
                StockMutation::TYPE_IN,
                StockMutation::TYPE_OUT,
                StockMutation::TYPE_SALE,
                StockMutation::TYPE_ADJUSTMENT,
            ])],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $query = StockMutation::query()
            ->with(['product', 'user'])
            ->latest();

        if (! empty($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        $summaryQuery = clone $query;

        $totalMutations = (clone $summaryQuery)->count();
        $totalIn = (clone $summaryQuery)->where('quantity', '>', 0)->sum('quantity');
        $totalOut = abs((clone $summaryQuery)->where('quantity', '<', 0)->sum('quantity'));

        $mutations = $query->paginate(20)->withQueryString();

        return view('stock-history.index', [
            'mutations' => $mutations,
            'products'  => Product::query()->orderBy('name')->get(['id', 'name', 'sku']),
            'totalMutations' => $totalMutations,
            'totalIn'   => $totalIn,
            'totalOut'  => $totalOut,
            'types'     => [
                StockMutation::TYPE_IN          => 'Stok Masuk',
                StockMutation::TYPE_OUT         => 'Stok Keluar',
                StockMutation::TYPE_SALE        => 'Penjualan POS',
                StockMutation::TYPE_ADJUSTMENT  => 'Penyesuaian',
            ],
        ]);
    }
}
