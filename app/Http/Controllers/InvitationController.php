<?php

namespace App\Http\Controllers;

use App\Mail\TeamInvitationMail;
use App\Models\Invitation;
use App\Models\User;
use App\Support\RolePermissionMap;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InvitationController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        if (! $request->user()->tenant?->canAddUser()) {
            return back()
                ->withErrors(['plan' => 'Batas user pada paket Anda sudah tercapai.'])
                ->withInput();
        }

        $request->validate([
            'email' => [
                'required',
                'email',
                'max:180',
                Rule::unique('users', 'email'),
            ],
            'role' => ['required', Rule::in(RolePermissionMap::assignableRolesFor($request->user()))],
        ]);

        $tenantId = $request->user()->tenant_id;
        
        // Hapus undangan sebelumnya jika masih pending untuk email yang sama di tenant ini
        Invitation::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('email', $request->email)
            ->whereNull('accepted_at')
            ->delete();

        $invitation = Invitation::create([
            'tenant_id' => $tenantId,
            'email' => $request->email,
            'role' => $request->role,
            'token' => Str::random(40),
            'expires_at' => now()->addHours(48),
        ]);

        Mail::to($invitation->email)->send(new TeamInvitationMail($invitation, $request->user()->tenant));

        return back()->with('status', 'Undangan berhasil dikirim ke ' . $invitation->email);
    }

    public function show(string $token)
    {
        $invitation = Invitation::withoutGlobalScopes()->where('token', $token)->firstOrFail();

        if ($invitation->accepted_at !== null) {
            return redirect()->route('login')->with('error', 'Undangan ini sudah pernah digunakan.');
        }

        if ($invitation->expires_at->isPast()) {
            return redirect()->route('login')->with('error', 'Undangan ini sudah kadaluarsa.');
        }

        return view('auth.invite', compact('invitation'));
    }

    public function accept(Request $request, string $token): RedirectResponse
    {
        $invitation = Invitation::withoutGlobalScopes()->where('token', $token)->firstOrFail();

        if ($invitation->accepted_at !== null || $invitation->expires_at->isPast()) {
            return redirect()->route('login')->with('error', 'Undangan tidak valid atau sudah kadaluarsa.');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'tenant_id' => $invitation->tenant_id,
            'name' => $request->name,
            'email' => $invitation->email,
            'password' => Hash::make($request->password),
            'role' => $invitation->role,
        ]);

        $invitation->update(['accepted_at' => now()]);

        Auth::login($user);

        return redirect()->route('dashboard')->with('status', 'Selamat datang di StokPintar!');
    }
}
