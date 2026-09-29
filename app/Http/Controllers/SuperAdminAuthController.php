<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\SuperAdminLoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class SuperAdminAuthController extends Controller
{
    public function store(SuperAdminLoginRequest $request): RedirectResponse
    {
        $identifier = trim($request->string('username')->toString());
        $credentialField = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $credentials = [
            $credentialField => $credentialField === 'email' ? strtolower($identifier) : $identifier,
            'password' => $request->string('password')->toString(),
        ];

        if (! Auth::guard('super_admin')->attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['username' => 'These credentials do not match our records.']);
        }

        $request->session()->regenerate();

        return redirect()->route('filament.super-admin.pages.dashboard');
    }

    public function destroy(): RedirectResponse
    {
        Auth::guard('super_admin')->logout();

        return redirect()->route('filament.super-admin.auth.login');
    }
}
