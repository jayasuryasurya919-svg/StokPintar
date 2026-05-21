<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\RolePermissionMap;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(Request $request): View
    {
        $tenant = $request->user()->tenant;

        $users = User::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->orderByRaw("case when role = '" . User::ROLE_OWNER . "' then 0 else 1 end")
            ->orderBy('name')
            ->paginate(10);

        $ownerCount = User::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->where('role', User::ROLE_OWNER)
            ->count();

        $roleOptions = RolePermissionMap::assignableRolesFor($request->user());

        return view('users.index', compact('users', 'tenant', 'ownerCount', 'roleOptions'));
    }

    public function create(): View
    {
        $user = new User;
        $user->load(['accessSchedules', 'storeAccess']);
        return view('users.form', [
            'userModel' => $user,
            'action' => route('users.store'),
            'method' => 'POST',
            'roleOptions' => RolePermissionMap::assignableRolesFor(request()->user()),
            'stores' => \App\Models\Store::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if (! $request->user()->tenant?->canAddUser()) {
            return back()
                ->withErrors(['plan' => 'Batas user pada paket Anda sudah tercapai.'])
                ->withInput();
        }

        $data = $this->validated($request);
        $data['tenant_id'] = $request->user()->tenant_id;

        $user = User::query()->create($data);
        $this->syncSchedules($request, $user);
        $this->syncStores($request, $user);

        return redirect()->route('users.index')->with('status', 'User berhasil ditambahkan.');
    }

    public function edit(Request $request, User $user): View
    {
        $this->ensureSameTenant($request, $user);
        $this->ensureCanManageUser($request, $user);

        $user->load(['accessSchedules', 'storeAccess']);

        return view('users.form', [
            'userModel' => $user,
            'action' => route('users.update', $user),
            'method' => 'PUT',
            'roleOptions' => RolePermissionMap::assignableRolesFor($request->user()),
            'stores' => \App\Models\Store::query()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->ensureSameTenant($request, $user);
        $this->ensureCanManageUser($request, $user);

        $data = $this->validated($request, $user);

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        if ($user->role === User::ROLE_OWNER && $data['role'] !== User::ROLE_OWNER && $this->isLastOwner($user)) {
            return back()
                ->withErrors(['role' => 'Tenant harus tetap memiliki minimal satu owner.'])
                ->withInput();
        }

        $user->update($data);
        $this->syncSchedules($request, $user);
        $this->syncStores($request, $user);

        return redirect()->route('users.index')->with('status', 'User berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->ensureSameTenant($request, $user);
        $this->ensureCanManageUser($request, $user);

        if ($user->id === $request->user()->id) {
            return back()->withErrors(['user' => 'Owner yang sedang login tidak bisa dihapus.']);
        }

        if ($user->role === User::ROLE_OWNER && $this->isLastOwner($user)) {
            return back()->withErrors(['user' => 'Tenant harus tetap memiliki minimal satu owner.']);
        }

        $user->delete();

        return redirect()->route('users.index')->with('status', 'User berhasil dihapus.');
    }

    private function validated(Request $request, ?User $user = null): array
    {
        $passwordRules = $user
            ? ['nullable', 'string', 'min:8', 'confirmed']
            : ['required', 'string', 'min:8', 'confirmed'];

        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => [
                'required',
                'email',
                'max:180',
                Rule::unique('users', 'email')->ignore($user?->id),
            ],
            'role' => ['required', Rule::in(RolePermissionMap::assignableRolesFor($request->user()))],
            'password' => $passwordRules,
            'stores' => ['nullable', 'array'],
            'stores.*' => [
                'integer',
                Rule::exists('stores', 'id')->where('tenant_id', $request->user()->tenant_id),
            ],
        ]);
    }

    private function ensureSameTenant(Request $request, User $user): void
    {
        abort_unless($user->tenant_id === $request->user()->tenant_id, 404);
    }

    private function ensureCanManageUser(Request $request, User $user): void
    {
        if ($request->user()->role === User::ROLE_OWNER) {
            return;
        }

        abort_if($user->role === User::ROLE_OWNER || $user->role === User::ROLE_MANAGER, 403);
    }

    private function isLastOwner(User $user): bool
    {
        return User::query()
            ->where('tenant_id', $user->tenant_id)
            ->where('role', User::ROLE_OWNER)
            ->count() <= 1;
    }

    private function syncSchedules(Request $request, User $user): void
    {
        $schedules = $request->input('schedules', []);
        $user->accessSchedules()->delete();

        foreach ($schedules as $day => $schedule) {
            if (! empty($schedule['active']) && ! empty($schedule['start_time']) && ! empty($schedule['end_time'])) {
                $user->accessSchedules()->create([
                    'tenant_id' => $user->tenant_id,
                    'day_of_week' => (int) $day,
                    'start_time' => $schedule['start_time'],
                    'end_time' => $schedule['end_time'],
                ]);
            }
        }
    }

    private function syncStores(Request $request, User $user): void
    {
        $stores = $request->input('stores', []);
        $user->storeAccess()->sync($stores);
    }
}
