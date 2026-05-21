<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StoreContextController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'store_id' => ['required', 'integer'],
        ]);

        $user = $request->user();
        $store = Store::query()
            ->where('tenant_id', $user->tenant_id)
            ->findOrFail($data['store_id']);

        if ($user->storeAccess()->exists()) {
            abort_unless($user->storeAccess()->whereKey($store->id)->exists(), 403);
        }

        session(['store_id' => $store->id]);

        return back()->with('status', "Toko aktif diganti ke {$store->name}.");
    }
}
