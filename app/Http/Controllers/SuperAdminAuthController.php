<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\SuperAdminLoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class SuperAdminAuthController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('SuperAdmin/Login');
    }

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

        return redirect()->route('super-admin.dashboard');
    }

    public function destroy(): RedirectResponse
    {
        Auth::guard('super_admin')->logout();

        return redirect()->route('super-admin.login');
    }
}
