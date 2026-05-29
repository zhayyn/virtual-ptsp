<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Tenant;

/**
 * Virtual PTSP - License Check Middleware
 * Built with ❤️ by zhayyn (+6281317361689)
 */
class CheckLicense
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        // Get tenant
        $tenant = $user->tenant;

        // If no tenant, skip (single-tenant mode)
        if (!$tenant) {
            return $next($request);
        }

        // Check if license key exists
        if (empty($tenant->license_key)) {
            // For demo/development, allow access but show warning
            if (app()->environment('local', 'development')) {
                session()->flash('license_warning', 'License key belum dikonfigurasi. Beberapa fitur mungkin terbatas.');
                return $next($request);
            }

            return response()->json([
                'error' => 'License required',
                'message' => 'Silakan konfigurasi license key untuk mengakses aplikasi.',
                'action' => '/settings/license'
            ], 403);
        }

        // Validate license via license server (if configured)
        $licenseServerUrl = config('services.license.server_url');

        if ($licenseServerUrl && !empty($licenseServerUrl)) {
            $isValid = $this->validateLicense($tenant->license_key, request()->getHost());

            if (!$isValid) {
                return response()->json([
                    'error' => 'License invalid or expired',
                    'message' => 'License key Anda tidak valid atau sudah kadaluarsa.',
                    'action' => '/settings/license'
                ], 403);
            }
        }

        // Check expiry date
        if ($tenant->license_expires_at && $tenant->license_expires_at->isPast()) {
            return response()->json([
                'error' => 'License expired',
                'message' => 'License Anda sudah kadaluarsa. Silakan perpanjang.',
                'action' => '/settings/license'
            ], 403);
        }

        return $next($request);
    }

    /**
     * Validate license with external server
     */
    private function validateLicense(string $licenseKey, string $domain): bool
    {
        $licenseServerUrl = config('services.license.server_url');
        $secret = config('services.license.secret');

        try {
            $response = \Illuminate\Support\Facades\Http::timeout(5)
                ->post($licenseServerUrl . '/validate', [
                    'license_key' => $licenseKey,
                    'domain' => $domain,
                    'product' => 'virtual-ptsp',
                    'timestamp' => now()->timestamp,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['valid'] ?? false;
            }

            // If server unreachable, allow (fail open for development)
            return true;

        } catch (\Exception $e) {
            \Log::warning('License validation failed: ' . $e->getMessage());
            // Fail open - allow access if license server is unreachable
            return true;
        }
    }
}