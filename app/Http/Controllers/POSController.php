<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\Store;
use App\Services\POS\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class POSController extends Controller
{
    public function index(): View
    {
        $currentStoreId = $this->currentStoreId();
        $user = auth()->user();
        $productsQuery = Product::query()
            ->with(['category', 'recipes.ingredient'])
            ->where('is_active', true)
            ->when($currentStoreId, fn ($query) => $query->where(function ($query) use ($currentStoreId) {
                $query->whereNull('store_id')->orWhere('store_id', $currentStoreId);
            }));

        return view('pos.index', [
            'products' => (clone $productsQuery)->orderBy('name')->get(),
            'categories' => (clone $productsQuery)->get()
                ->pluck('category.name')
                ->filter()
                ->unique()
                ->values(),
            'recentSales' => Sale::query()
                ->with('items')
                ->where('status', 'paid')
                ->when($user?->isCashier(), fn ($query) => $query->where('cashier_id', $user?->id))
                ->latest('id')
                ->limit(20)
                ->get(),
            'activeStore' => $currentStoreId ? Store::query()->find($currentStoreId) : null,
            'receipt' => session('receipt'),
        ]);
    }

    public function store(Request $request, CheckoutService $checkout): RedirectResponse
    {
        $data = $request->validate([
            'paid_amount' => ['required', 'integer', 'min:0'],
            'payment_method' => ['required', 'in:cash,qris,transfer'],
            'items' => ['required', 'array'],
            'items.*.product_id' => ['required', 'integer'],
            'items.*.quantity' => ['nullable', 'integer', 'min:0'],
        ]);

        $items = collect($data['items'])
            ->map(fn (array $item) => [
                'product_id' => (int) $item['product_id'],
                'quantity' => (int) ($item['quantity'] ?? 0),
            ])
            ->filter(fn (array $item) => $item['quantity'] > 0)
            ->values()
            ->all();

        $sale = $checkout->checkout(
            tenantId: $request->user()->tenant_id,
            cashierId: $request->user()->id,
            storeId: $this->currentStoreId(),
            items: $items,
            paidAmount: (int) $data['paid_amount'],
            paymentMethod: $data['payment_method'],
        );

        $sale->load(['items', 'cashier']);
        
        \App\Support\ActivityLogger::log('checkout', $sale);

        return redirect()->route('pos.index')
            ->with('status', "Transaksi {$sale->invoice_number} berhasil. Kembalian Rp ".number_format($sale->change_amount, 0, ',', '.'))
            ->with('receipt', [
                'invoice_number' => $sale->invoice_number,
                'sold_at' => $sale->sold_at?->format('d M Y H:i'),
                'cashier' => $sale->cashier?->name ?? $request->user()->name,
                'payment_method' => strtoupper($sale->payment_method),
                'paid_amount' => $sale->paid_amount,
                'change_amount' => $sale->change_amount,
                'total' => $sale->total,
                'items' => $sale->items->map(fn ($item) => [
                    'name' => $item->product_name,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'line_total' => $item->line_total,
                ])->all(),
            ]);
    }

    public function void(Request $request, Sale $sale, CheckoutService $checkout): RedirectResponse
    {
        try {
            $checkout->voidSale($sale, $request->user()->id);
            \App\Support\ActivityLogger::log('void_sale', $sale);
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors());
        }

        return back()->with('status', "Transaksi {$sale->invoice_number} berhasil di-void dan stok telah dikembalikan.");
    }

    private function currentStoreId(): ?int
    {
        $user = auth()->user();
        $sessionStoreId = session('store_id');

        if ($sessionStoreId && Store::query()->where('tenant_id', $user?->tenant_id)->whereKey($sessionStoreId)->exists()) {
            if (! $user?->storeAccess()->exists() || $user->storeAccess()->whereKey($sessionStoreId)->exists()) {
                return (int) $sessionStoreId;
            }
        }

        if ($user?->storeAccess()->exists()) {
            return (int) $user->storeAccess()->orderBy('name')->value('stores.id');
        }

        if ($sessionStoreId && Store::query()->where('tenant_id', $user?->tenant_id)->whereKey($sessionStoreId)->exists()) {
            return (int) $sessionStoreId;
        }

        return Store::query()->where('tenant_id', $user?->tenant_id)->where('is_default', true)->value('id')
            ?? Store::query()->where('tenant_id', $user?->tenant_id)->value('id');
    }
}
