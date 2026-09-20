<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\SuperAdmin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class SuperAdminAccountController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('SuperAdmin/Accounts', [
            'admins' => SuperAdmin::query()->latest()->get(['id', 'name', 'email']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'unique:super_admins,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);
        SuperAdmin::create([
            ...$data,
            'password' => Hash::make($data['password']),
        ]);

        return back()->with('status', 'Superadmin added.');
    }

    public function update(Request $request, SuperAdmin $superAdmin): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', Rule::unique('super_admins', 'email')->ignore($superAdmin->id)],
            'password' => ['nullable', 'string', 'min:8'],
        ]);
        $superAdmin->name = $data['name'];
        $superAdmin->email = $data['email'];
        if (! empty($data['password'])) {
            $superAdmin->password = Hash::make($data['password']);
        }
        $superAdmin->save();

        return back()->with('status', 'Superadmin updated.');
    }
}
