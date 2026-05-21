<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;

        $logsQuery = ActivityLog::query()
            ->with(['user', 'subject'])
            ->where('tenant_id', $tenantId)
            ->latest();

        if ($request->filled('user_id')) {
            $logsQuery->where('user_id', $request->user_id);
        }

        if ($request->filled('action')) {
            $logsQuery->where('action', $request->action);
        }

        $logs = $logsQuery->paginate(20)->withQueryString();

        $users = User::query()
            ->where('tenant_id', $tenantId)
            ->orderBy('name')
            ->get();

        $actions = [
            'login' => 'Login',
            'logout' => 'Logout',
            'create_product' => 'Tambah Produk',
            'update_product' => 'Edit Produk',
            'delete_product' => 'Hapus Produk',
            'checkout' => 'Checkout POS',
            'void_sale' => 'Void Transaksi',
            'stock_mutation' => 'Mutasi Stok Manual',
        ];

        return view('users.activities', compact('logs', 'users', 'actions'));
    }
}
