<?php

namespace App\Services\POS;

use App\Models\Product;
use App\Models\Sale;
use App\Models\StockMutation;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    /**
     * @param  array<int, array{product_id:int, quantity:int}>  $items
     */
    public function checkout(
        int $tenantId,
        int $cashierId,
        ?int $storeId,
        array $items,
        int $paidAmount,
        string $paymentMethod = 'cash',
    ): Sale {
        if ($items === []) {
            throw ValidationException::withMessages([
                'items' => 'Minimal satu produk harus dipilih.',
            ]);
        }

        return DB::transaction(function () use ($tenantId, $cashierId, $storeId, $items, $paidAmount, $paymentMethod) {
            $sale = Sale::withoutGlobalScopes()->create([
                'tenant_id' => $tenantId,
                'store_id' => $storeId,
                'cashier_id' => $cashierId,
                'invoice_number' => $this->nextInvoiceNumber($tenantId),
                'payment_method' => $paymentMethod,
                'sold_at' => now(),
            ]);

            $subtotal = 0;

            foreach ($items as $item) {
                $product = Product::withoutGlobalScopes()
                    ->where('tenant_id', $tenantId)
                    ->where(function ($query) use ($storeId) {
                        if ($storeId === null) {
                            $query->whereNull('store_id');

                            return;
                        }

                        $query->whereNull('store_id')
                            ->orWhere('store_id', $storeId);
                    })
                    ->lockForUpdate()
                    ->findOrFail($item['product_id']);

                $quantity = max(1, (int) $item['quantity']);

                $lineTotal = $product->selling_price * $quantity;
                $subtotal += $lineTotal;

                $sale->items()->create([
                    'tenant_id' => $tenantId,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'quantity' => $quantity,
                    'unit_price' => $product->selling_price,
                    'line_total' => $lineTotal,
                ]);

                if ($product->isMenu()) {
                    $this->decrementMenuIngredients($product, $quantity, $sale, $cashierId, $storeId);
                } else {
                    $this->decrementStockProduct($product, $quantity, $sale, $cashierId, $storeId);
                }
            }

            if ($paidAmount < $subtotal) {
                throw ValidationException::withMessages([
                    'paid_amount' => 'Nominal pembayaran kurang dari total transaksi.',
                ]);
            }

            $sale->forceFill([
                'subtotal' => $subtotal,
                'total' => $subtotal,
                'paid_amount' => $paidAmount,
                'change_amount' => $paidAmount - $subtotal,
            ])->save();

            return $sale->load('items');
        });
    }

    public function voidSale(Sale $sale, int $actorId): Sale
    {
        return DB::transaction(function () use ($sale, $actorId) {
            $sale = Sale::withoutGlobalScopes()
                ->with('items')
                ->lockForUpdate()
                ->findOrFail($sale->id);

            if ($sale->status === 'void') {
                throw ValidationException::withMessages([
                    'sale' => 'Transaksi ini sudah di-void sebelumnya.',
                ]);
            }

            foreach ($sale->items as $item) {
                if (! $item->product_id) {
                    continue;
                }

                $product = Product::withoutGlobalScopes()
                    ->with('recipes.ingredient')
                    ->where('tenant_id', $sale->tenant_id)
                    ->lockForUpdate()
                    ->findOrFail($item->product_id);

                if ($product->isMenu()) {
                    $this->restoreMenuIngredients($product, $item->quantity, $sale, $actorId);

                    continue;
                }

                $stockBefore = $product->stock;
                $stockAfter = $stockBefore + $item->quantity;

                $product->forceFill(['stock' => $stockAfter])->save();

                StockMutation::withoutGlobalScopes()->create([
                    'tenant_id' => $sale->tenant_id,
                    'store_id' => $sale->store_id,
                    'product_id' => $product->id,
                    'user_id' => $actorId,
                    'type' => StockMutation::TYPE_ADJUSTMENT,
                    'quantity' => $item->quantity,
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'reference_type' => Sale::class,
                    'reference_id' => $sale->id,
                    'notes' => 'Void transaksi '.$sale->invoice_number,
                ]);
            }

            $sale->forceFill([
                'status' => 'void',
            ])->save();

            return $sale->fresh('items');
        });
    }

    private function nextInvoiceNumber(int $tenantId): string
    {
        $prefix = 'INV-'.now()->format('Ymd').'-';
        $count = Sale::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->whereDate('created_at', today())
            ->count() + 1;

        return $prefix.str_pad((string) $count, 4, '0', STR_PAD_LEFT);
    }

    private function decrementStockProduct(Product $product, int $quantity, Sale $sale, int $cashierId, ?int $storeId): void
    {
        if ($product->stock < $quantity) {
            throw ValidationException::withMessages([
                'items' => "Stok {$product->name} tidak cukup.",
            ]);
        }

        $stockBefore = $product->stock;
        $stockAfter = $stockBefore - $quantity;

        $product->forceFill(['stock' => $stockAfter])->save();

        StockMutation::withoutGlobalScopes()->create([
            'tenant_id' => $sale->tenant_id,
            'store_id' => $storeId,
            'product_id' => $product->id,
            'user_id' => $cashierId,
            'type' => StockMutation::TYPE_SALE,
            'quantity' => -$quantity,
            'stock_before' => $stockBefore,
            'stock_after' => $stockAfter,
            'reference_type' => Sale::class,
            'reference_id' => $sale->id,
            'notes' => 'Transaksi POS '.$sale->invoice_number,
        ]);
    }

    private function decrementMenuIngredients(Product $menu, int $menuQuantity, Sale $sale, int $cashierId, ?int $storeId): void
    {
        $recipes = $menu->recipes()->with('ingredient')->get();

        if ($recipes->isEmpty()) {
            throw ValidationException::withMessages([
                'items' => "Resep {$menu->name} belum diatur.",
            ]);
        }

        foreach ($recipes as $recipe) {
            $needed = $recipe->quantity * $menuQuantity;
            $ingredient = Product::withoutGlobalScopes()
                ->where('tenant_id', $menu->tenant_id)
                ->where(function ($query) use ($storeId) {
                    if ($storeId === null) {
                        $query->whereNull('store_id');

                        return;
                    }

                    $query->whereNull('store_id')->orWhere('store_id', $storeId);
                })
                ->lockForUpdate()
                ->findOrFail($recipe->ingredient_product_id);

            if ($ingredient->stock < $needed) {
                throw ValidationException::withMessages([
                    'items' => "Bahan {$ingredient->name} tidak cukup untuk {$menu->name}.",
                ]);
            }
        }

        foreach ($recipes as $recipe) {
            $needed = $recipe->quantity * $menuQuantity;
            $ingredient = Product::withoutGlobalScopes()
                ->where('tenant_id', $menu->tenant_id)
                ->lockForUpdate()
                ->findOrFail($recipe->ingredient_product_id);
            $stockBefore = $ingredient->stock;
            $stockAfter = $stockBefore - $needed;

            $ingredient->forceFill(['stock' => $stockAfter])->save();

            StockMutation::withoutGlobalScopes()->create([
                'tenant_id' => $sale->tenant_id,
                'store_id' => $storeId,
                'product_id' => $ingredient->id,
                'user_id' => $cashierId,
                'type' => StockMutation::TYPE_SALE,
                'quantity' => -$needed,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'reference_type' => Sale::class,
                'reference_id' => $sale->id,
                'notes' => "Bahan {$menu->name} - {$sale->invoice_number}",
            ]);
        }
    }

    private function restoreMenuIngredients(Product $menu, int $menuQuantity, Sale $sale, int $actorId): void
    {
        foreach ($menu->recipes as $recipe) {
            $quantity = $recipe->quantity * $menuQuantity;
            $ingredient = Product::withoutGlobalScopes()
                ->where('tenant_id', $sale->tenant_id)
                ->lockForUpdate()
                ->findOrFail($recipe->ingredient_product_id);
            $stockBefore = $ingredient->stock;
            $stockAfter = $stockBefore + $quantity;

            $ingredient->forceFill(['stock' => $stockAfter])->save();

            StockMutation::withoutGlobalScopes()->create([
                'tenant_id' => $sale->tenant_id,
                'store_id' => $sale->store_id,
                'product_id' => $ingredient->id,
                'user_id' => $actorId,
                'type' => StockMutation::TYPE_ADJUSTMENT,
                'quantity' => $quantity,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'reference_type' => Sale::class,
                'reference_id' => $sale->id,
                'notes' => "Void bahan {$menu->name} - {$sale->invoice_number}",
            ]);
        }
    }
}
