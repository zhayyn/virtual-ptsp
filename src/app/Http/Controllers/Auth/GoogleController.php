<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

/**
 * Virtual PTSP - Google OAuth Controller
 * Built with ❤️ by zhayyn (+6281317361689)
 *
 * Guide untuk buyer:
 * 1. Buat project di Google Cloud Console (https://console.cloud.google.com)
 * 2. Enable Google+ API
 * 3. Buat OAuth 2.0 credentials
 * 4. Masukkan Client ID dan Client Secret di .env
 * 5. Set redirect URI ke: {APP_URL}/auth/google/callback
 */
class GoogleController extends Controller
{
    /**
     * Redirect to Google OAuth
     */
    public function redirectToGoogle(): RedirectResponse
    {
        return Socialite::driver('google')
            ->scopes(['email', 'profile'])
            ->redirect();
    }

    /**
     * Handle Google OAuth callback
     */
    public function handleGoogleCallback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            // Find or create user
            $user = User::where('google_id', $googleUser->id)
                ->orWhere('email', $googleUser->email)
                ->first();

            if (!$user) {
                // Create new user
                $user = User::create([
                    'name' => $googleUser->name ?? $googleUser->nickname ?? 'User',
                    'email' => $googleUser->email,
                    'google_id' => $googleUser->id,
                    'google_token' => $googleUser->token,
                    'avatar' => $googleUser->avatar,
                    'email_verified_at' => now(),
                    'role' => 'admin', // First user becomes admin
                ]);

                // Create default tenant
                $tenant = Tenant::create([
                    'name' => $user->name . "'s Organization",
                    'slug' => Str::slug($user->name) . '-' . Str::random(4),
                    'is_active' => true,
                ]);

                $user->tenant_id = $tenant->id;
                $user->save();
            } else {
                // Update google token if changed
                if ($user->google_id !== $googleUser->id) {
                    $user->google_id = $googleUser->id;
                    $user->google_token = $googleUser->token;
                }
                if ($user->avatar !== $googleUser->avatar) {
                    $user->avatar = $googleUser->avatar;
                }
                $user->last_login_at = now();
                $user->save();
            }

            Auth::login($user, true);

            return redirect()->intended(route('dashboard'));

        } catch (\Exception $e) {
            \Log::error('Google OAuth Error: ' . $e->getMessage());
            return redirect('/login')
                ->withErrors(['error' => 'Gagal login dengan Google. Silakan coba lagi.']);
        }
    }
}