<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BusinessProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $tenant = $request->user()->tenant;
        abort_if(! $tenant, 404);

        $defaultStore = Store::query()
            ->where('tenant_id', $tenant->id)
            ->where('is_default', true)
            ->first()
            ?? Store::query()->where('tenant_id', $tenant->id)->first();

        return view('business-profile.edit', compact('tenant', 'defaultStore'));
    }

    public function update(Request $request): RedirectResponse
    {
        $tenant = $request->user()->tenant;
        abort_if(! $tenant, 404);
        $store = Store::query()
            ->where('tenant_id', $tenant->id)
            ->where('is_default', true)
            ->first()
            ?? Store::query()->where('tenant_id', $tenant->id)->first();

        $data = $request->validate([
            'tenant_name' => ['required', 'string', 'max:120'],
            'store_name' => ['required', 'string', 'max:120'],
            'store_code' => [
                'nullable',
                'string',
                'max:40',
                Rule::unique('stores', 'code')
                    ->where('tenant_id', $tenant->id)
                    ->ignore($store?->id),
            ],
            'store_phone' => ['nullable', 'string', 'max:40'],
            'store_address' => ['nullable', 'string', 'max:500'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ]);

        $tenantPayload = ['name' => $data['tenant_name']];

        if ($request->hasFile('logo')) {
            if ($tenant->logo_path) {
                Storage::disk('public')->delete($tenant->logo_path);
            }

            $tenantPayload['logo_path'] = $request->file('logo')->store('tenant-logos', 'public');
        }

        $tenant->update($tenantPayload);

        if (! $store) {
            Store::query()->create([
                'tenant_id' => $tenant->id,
                'name' => $data['store_name'],
                'code' => $data['store_code'] ?: null,
                'phone' => $data['store_phone'] ?: null,
                'address' => $data['store_address'] ?: null,
                'is_default' => true,
            ]);
        } else {
            $store->update([
                'name' => $data['store_name'],
                'code' => $data['store_code'] ?: null,
                'phone' => $data['store_phone'] ?: null,
                'address' => $data['store_address'] ?: null,
            ]);
        }

        return redirect()->route('business-profile.edit')->with('status', 'Profil bisnis berhasil diperbarui.');
    }
}
