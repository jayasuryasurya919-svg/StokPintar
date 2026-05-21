<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SupportTicketController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $isPlatformAdmin = $user->canPermission('platform.manage');

        abort_unless($isPlatformAdmin || $user->canPermission('subscription.manage'), 403);

        $query = SupportTicket::withoutGlobalScopes()
            ->with(['tenant.subscriptionPlan', 'requester'])
            ->when(! $isPlatformAdmin, fn ($query) => $query->where('tenant_id', $user->tenant_id))
            ->orderByRaw("case when priority = 'priority' and status != 'resolved' then 0 else 1 end")
            ->latest();

        $tickets = $query->paginate(10);
        $tenant = $user->tenant?->load('subscriptionPlan');
        $hasPrioritySupport = (bool) $tenant?->subscriptionPlan?->hasFeature('priority_support');

        return view('support.index', compact('tickets', 'isPlatformAdmin', 'tenant', 'hasPrioritySupport'));
    }

    public function store(Request $request): RedirectResponse
    {
        $tenant = $request->user()->tenant?->load('subscriptionPlan');

        abort_if(! $tenant, 404);
        abort_if($request->user()->canPermission('platform.manage'), 403);

        $data = $request->validate([
            'subject' => ['required', 'string', 'max:160'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $hasPrioritySupport = $tenant->subscriptionPlan?->hasFeature('priority_support') ?? false;

        SupportTicket::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $request->user()->id,
            'priority' => $hasPrioritySupport ? SupportTicket::PRIORITY_PRIORITY : SupportTicket::PRIORITY_NORMAL,
            'status' => SupportTicket::STATUS_OPEN,
            'subject' => $data['subject'],
            'message' => $data['message'],
        ]);

        return redirect()
            ->route('support.index')
            ->with('status', $hasPrioritySupport ? 'Tiket priority support berhasil dikirim.' : 'Tiket support berhasil dikirim.');
    }

    public function update(Request $request, int $ticket): RedirectResponse
    {
        abort_unless($request->user()->canPermission('platform.manage'), 403);

        $supportTicket = SupportTicket::withoutGlobalScopes()->findOrFail($ticket);

        $data = $request->validate([
            'status' => ['required', Rule::in([
                SupportTicket::STATUS_OPEN,
                SupportTicket::STATUS_IN_PROGRESS,
                SupportTicket::STATUS_RESOLVED,
            ])],
            'admin_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $supportTicket->update([
            'status' => $data['status'],
            'admin_note' => $data['admin_note'] ?? null,
            'resolved_at' => $data['status'] === SupportTicket::STATUS_RESOLVED ? now() : null,
        ]);

        return redirect()->route('support.index')->with('status', 'Tiket support berhasil diperbarui.');
    }
}
