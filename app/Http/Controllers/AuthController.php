<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'Email atau password tidak sesuai.'])
                ->onlyInput('email');
        }

        $user = Auth::user();

        if ($user->accessSchedules()->exists()) {
            $today = now()->dayOfWeek;
            $currentTime = now()->format('H:i:s');
            
            $hasAccess = $user->accessSchedules()
                ->where('day_of_week', $today)
                ->where('start_time', '<=', $currentTime)
                ->where('end_time', '>=', $currentTime)
                ->exists();

            if (!$hasAccess) {
                Auth::logout();
                return back()->withErrors(['email' => 'Anda tidak diizinkan login di luar jam kerja (shift) Anda.'])->onlyInput('email');
            }
        }

        $request->session()->regenerate();
        
        \App\Support\ActivityLogger::log('login');

        return redirect()->to($this->redirectTargetFor($request, $request->user()));
    }

    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'store_name' => ['required', 'string', 'max:120'],
            'owner_name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:180', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'terms' => ['accepted'],
        ]);

        $plan = SubscriptionPlan::query()->firstOrCreate(
            ['code' => 'full'],
            [
                'name' => 'Full Setup',
                'price' => 0,
                'max_stores' => null,
                'max_products' => null,
                'max_users' => null,
                'report_retention_days' => null,
                'features' => [
                    'basic_pos',
                    'stock_alerts',
                    'pdf_export',
                    'excel_export',
                    'multi_store',
                    'team_access',
                    'fnb_recipes',
                    'barcode_scanner',
                    'activity_logs',
                    'priority_support',
                ],
            ],
        );

        $user = User::query()->create([
            'name' => $data['owner_name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => User::ROLE_OWNER,
        ]);

        $tenant = Tenant::query()->create([
            'owner_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'name' => $data['store_name'],
            'slug' => Str::slug($data['store_name']).'-'.Str::lower(Str::random(5)),
            'status' => 'active',
            'trial_ends_at' => null,
            'subscription_ends_at' => null,
        ]);

        Store::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'name' => $data['store_name'],
            'code' => 'MAIN',
            'is_default' => true,
        ]);

        $user->forceFill(['tenant_id' => $tenant->id])->save();

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('status', 'Toko berhasil dibuat. Selamat datang di StokPintar.');
    }

    public function logout(Request $request): RedirectResponse
    {
        \App\Support\ActivityLogger::log('logout');
        
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function homeRouteFor(User $user): string
    {
        return $user->canPermission('platform.manage')
            ? route('platform.index')
            : route('dashboard');
    }

    private function redirectTargetFor(Request $request, User $user): string
    {
        $fallback = $this->homeRouteFor($user);
        $intended = $request->session()->pull('url.intended');

        if (! $intended) {
            return $fallback;
        }

        $path = parse_url($intended, PHP_URL_PATH) ?: '';
        $platformPath = route('platform.index', absolute: false);

        if ($user->canPermission('platform.manage') && ! str_starts_with($path, $platformPath)) {
            return $fallback;
        }

        if (! $user->canPermission('platform.manage') && str_starts_with($path, $platformPath)) {
            return $fallback;
        }

        return $intended;
    }
}
