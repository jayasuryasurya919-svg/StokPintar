<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMutation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockMutationController extends Controller
{
    public function store(Request $request, Product $product): RedirectResponse
    {
        if ($product->isMenu()) {
            throw ValidationException::withMessages([
                'product' => 'Menu/racikan tidak bisa dimutasi langsung. Ubah stok bahan bakunya.',
            ]);
        }

        $data = $request->validate([
            'type' => ['required', 'in:in,out'],
            'quantity' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        if ($data['type'] === StockMutation::TYPE_OUT && blank($data['notes'] ?? null)) {
            throw ValidationException::withMessages([
                'notes' => 'Catatan wajib diisi untuk stok keluar agar histori lebih jelas.',
            ]);
        }

        DB::transaction(function () use ($data, $product, $request) {
            $product->refresh();

            $stockBefore = $product->stock;
            $change = $data['type'] === StockMutation::TYPE_IN ? $data['quantity'] : -$data['quantity'];
            $stockAfter = $stockBefore + $change;

            if ($stockAfter < 0) {
                throw ValidationException::withMessages([
                    'quantity' => 'Stok tidak cukup untuk mutasi keluar.',
                ]);
            }

            $product->forceFill(['stock' => $stockAfter])->save();

            StockMutation::query()->create([
                'tenant_id' => $product->tenant_id,
                'store_id' => $product->store_id,
                'product_id' => $product->id,
                'user_id' => $request->user()->id,
                'type' => $data['type'],
                'quantity' => $change,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'notes' => $data['notes'] ?? null,
            ]);

            \App\Support\ActivityLogger::log('stock_mutation', $product, [
                'type' => $data['type'],
                'quantity' => $change
            ]);
        });

        return back()->with('status', 'Mutasi stok berhasil dicatat.');
    }
}
