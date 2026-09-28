<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Models\PlatformSetting;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
            'subscriberCode' => $this->subscriberCodeForIntendedTenant($request),
            'wallpaperUrl' => ($path = PlatformSetting::value('login_wallpaper'))
                ? $request->getSchemeAndHttpHost().'/storage/'.ltrim($path, '/')
                : null,
        ]);
    }

    private function subscriberCodeForIntendedTenant(Request $request): ?string
    {
        $intendedPath = parse_url((string) $request->session()->get('url.intended', ''), PHP_URL_PATH);
        $segments = array_values(array_filter(explode('/', trim((string) $intendedPath, '/'))));
        $tenantIdentifier = match (true) {
            ($segments[0] ?? null) === 'dashboard' && isset($segments[1]) => $segments[1],
            ($segments[1] ?? null) === 'dashboard' => $segments[0],
            default => null,
        };

        if ($tenantIdentifier === null) {
            return null;
        }

        $tenant = Tenant::query()
            ->where('id', $tenantIdentifier)
            ->orWhere('slug', $tenantIdentifier)
            ->first();

        return $tenant?->subscriber_code ?: data_get($tenant?->data, 'subscriber_code');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = $request->user();
        if ($user !== null && $user->role === 'admin' && $user->tenant_id !== null) {
            return redirect(route('tenant.dashboard.direct', [
                'tenant' => $user->tenant_id,
            ], absolute: false));
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
