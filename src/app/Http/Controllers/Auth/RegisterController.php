<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

/**
 * Virtual PTSP - Register Controller
 * Built with ❤️ by zhayyn (+6281317361689)
 */
class RegisterController extends Controller
{
    /**
     * Show registration form
     */
    public function showRegistrationForm(): View
    {
        return view('auth.register');
    }

    /**
     * Handle registration
     */
    public function register(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput($request->except('password'));
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'admin', // First user becomes admin
        ]);

        // Create default tenant for this user
        $tenant = \App\Models\Tenant::create([
            'name' => $user->name . "'s Organization",
            'slug' => \Illuminate\Support\Str::slug($user->name) . '-' . \Illuminate\Support\Str::random(4),
            'is_active' => true,
        ]);

        // Attach tenant to user
        $user->tenant_id = $tenant->id;
        $user->save();

        // Login the user
        \Illuminate\Support\Facades\Auth::login($user);

        return redirect()->route('dashboard');
    }
}