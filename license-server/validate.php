<?php
/**
 * Virtual PTSP - Simple License Server
 * Built with ❤️ by zhayyn (+6281317361689)
 *
 * This is a standalone PHP license validation server.
 * Deploy this on your license server (VPS).
 *
 * Usage:
 * 1. Upload this file to your license server
 * 2. Set the LICENSE_SECRET in this file
 * 3. Update LICENSE_SERVER_URL in customer .env
 *
 * API Endpoints:
 * POST /validate    - Validate license key
 * POST /register   - Register new license
 * GET  /check      - Check license status
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ============================================================
// CONFIGURATION
// ============================================================
define('LICENSE_SECRET', getenv('LICENSE_SECRET') ?: 'virtual_ptsp_secret_key_change_me');
define('DATA_FILE', __DIR__ . '/licenses.json');
define('LICENSE_SERVER_NAME', 'Virtual PTSP License Server');
define('LICENSE_SERVER_VERSION', '1.0.0');

// ============================================================
// DATABASE (Simple JSON file-based)
// ============================================================
function getLicenses(): array {
    if (!file_exists(DATA_FILE)) {
        return [];
    }
    $data = file_get_contents(DATA_FILE);
    return json_decode($data, true) ?? [];
}

function saveLicenses(array $licenses): bool {
    return file_put_contents(DATA_FILE, json_encode($licenses, JSON_PRETTY_PRINT)) !== false;
}

// ============================================================
// LICENSE VALIDATION
// ============================================================

/**
 * Generate a license key
 */
function generateLicenseKey(string $domain, string $plan = 'pro', int $months = 12): array {
    $prefix = strtoupper(substr($plan, 0, 4));
    $random = strtoupper(bin2hex(random_bytes(4)));
    $checksum = strtoupper(bin2hex(random_bytes(2)));

    $key = "{$prefix}-{$random}-{$checksum}";
    $expires = date('Y-m-d H:i:s', strtotime("+{$months} months"));

    return [
        'key' => $key,
        'domain' => strtolower($domain),
        'plan' => $plan,
        'expires' => $expires,
        'created' => date('Y-m-d H:i:s'),
        'active' => true,
    ];
}

/**
 * Validate license key
 */
function validateLicense(string $key, string $domain, string $product = 'virtual-ptsp'): array {
    $licenses = getLicenses();

    // Find license
    $license = null;
    foreach ($licenses as $l) {
        if ($l['key'] === $key) {
            $license = $l;
            break;
        }
    }

    // License not found
    if (!$license) {
        return [
            'valid' => false,
            'error' => 'License key not found',
            'code' => 'NOT_FOUND',
        ];
    }

    // Check if active
    if (!$license['active']) {
        return [
            'valid' => false,
            'error' => 'License key has been deactivated',
            'code' => 'DEACTIVATED',
        ];
    }

    // Check domain binding
    if (!empty($license['domain']) && strtolower($license['domain']) !== strtolower($domain)) {
        return [
            'valid' => false,
            'error' => 'License key is bound to a different domain',
            'code' => 'DOMAIN_MISMATCH',
        ];
    }

    // Check expiry
    if (strtotime($license['expires']) < time()) {
        return [
            'valid' => false,
            'error' => 'License key has expired',
            'code' => 'EXPIRED',
            'expired_at' => $license['expires'],
        ];
    }

    // Generate signature
    $signature = generateSignature([
        'key' => $license['key'],
        'domain' => $domain,
        'expires' => $license['expires'],
    ]);

    return [
        'valid' => true,
        'key' => $license['key'],
        'domain' => $domain,
        'plan' => $license['plan'],
        'expires' => $license['expires'],
        'days_remaining' => ceil((strtotime($license['expires']) - time()) / 86400),
        'signature' => $signature,
    ];
}

/**
 * Generate signature for validation
 */
function generateSignature(array $data): string {
    $payload = implode('|', $data) . '|' . LICENSE_SECRET;
    return hash_hmac('sha256', $payload, LICENSE_SECRET);
}

// ============================================================
// API ENDPOINTS
// ============================================================

$requestUri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

// Parse request body
$input = json_decode(file_get_contents('php://input'), true) ?? [];

// Route handling
$path = parse_url($requestUri, PHP_URL_PATH);

// ============================================================
// Health Check
// ============================================================
if ($path === '/health' || $path === '/') {
    echo json_encode([
        'status' => 'ok',
        'server' => LICENSE_SERVER_NAME,
        'version' => LICENSE_SERVER_VERSION,
        'timestamp' => date('Y-m-d H:i:s'),
    ]);
    exit;
}

// ============================================================
// Validate License - POST /validate
// ============================================================
if ($path === '/validate' && $method === 'POST') {
    $key = $input['license_key'] ?? '';
    $domain = $input['domain'] ?? '';
    $product = $input['product'] ?? 'virtual-ptsp';

    if (empty($key) || empty($domain)) {
        http_response_code(400);
        echo json_encode([
            'valid' => false,
            'error' => 'Missing required fields: license_key, domain',
        ]);
        exit;
    }

    $result = validateLicense($key, $domain, $product);
    echo json_encode($result);
    exit;
}

// ============================================================
// Register New License - POST /register
// ============================================================
if ($path === '/register' && $method === 'POST') {
    // Verify admin secret
    $adminSecret = $input['admin_secret'] ?? '';

    if ($adminSecret !== LICENSE_SECRET && $adminSecret !== getenv('ADMIN_SECRET')) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error' => 'Invalid admin secret',
        ]);
        exit;
    }

    $domain = $input['domain'] ?? '';
    $plan = $input['plan'] ?? 'pro';
    $months = intval($input['months'] ?? 12);

    if (empty($domain)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Domain is required',
        ]);
        exit;
    }

    // Check if domain already has license
    $licenses = getLicenses();
    foreach ($licenses as $l) {
        if ($l['domain'] === strtolower($domain) && $l['active']) {
            http_response_code(409);
            echo json_encode([
                'success' => false,
                'error' => 'Domain already has an active license',
                'existing_license' => [
                    'key' => $l['key'],
                    'plan' => $l['plan'],
                    'expires' => $l['expires'],
                ],
            ]);
            exit;
        }
    }

    // Generate new license
    $license = generateLicenseKey($domain, $plan, $months);
    $licenses[] = $license;
    saveLicenses($licenses);

    echo json_encode([
        'success' => true,
        'message' => 'License registered successfully',
        'license' => $license,
    ]);
    exit;
}

// ============================================================
// Check License Status - GET /check?key=xxx
// ============================================================
if ($path === '/check' && $method === 'GET') {
    $key = $_GET['key'] ?? '';

    if (empty($key)) {
        http_response_code(400);
        echo json_encode([
            'error' => 'License key is required',
        ]);
        exit;
    }

    $licenses = getLicenses();
    $license = null;

    foreach ($licenses as $l) {
        if ($l['key'] === $key) {
            $license = $l;
            break;
        }
    }

    if (!$license) {
        http_response_code(404);
        echo json_encode([
            'found' => false,
            'error' => 'License not found',
        ]);
        exit;
    }

    // Hide sensitive data
    unset($license['domain']);

    echo json_encode([
        'found' => true,
        'license' => $license,
    ]);
    exit;
}

// ============================================================
// Deactivate License - POST /deactivate
// ============================================================
if ($path === '/deactivate' && $method === 'POST') {
    $adminSecret = $input['admin_secret'] ?? '';
    $key = $input['license_key'] ?? '';

    if ($adminSecret !== LICENSE_SECRET && $adminSecret !== getenv('ADMIN_SECRET')) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error' => 'Invalid admin secret',
        ]);
        exit;
    }

    $licenses = getLicenses();
    $found = false;

    foreach ($licenses as &$l) {
        if ($l['key'] === $key) {
            $l['active'] = false;
            $l['deactivated_at'] = date('Y-m-d H:i:s');
            $found = true;
            break;
        }
    }

    if ($found) {
        saveLicenses($licenses);
        echo json_encode([
            'success' => true,
            'message' => 'License deactivated',
        ]);
    } else {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'License not found',
        ]);
    }
    exit;
}

// ============================================================
// Generate Test License (Development Only) - POST /generate-test
// ============================================================
if ($path === '/generate-test' && $method === 'POST') {
    // Only allow in development
    $allowedIps = ['127.0.0.1', '::1', 'localhost'];
    $clientIp = $_SERVER['REMOTE_ADDR'] ?? '';

    // Check if it's a development environment
    if (!in_array($clientIp, $allowedIps) && !getenv('ALLOW_TEST_GENERATION')) {
        http_response_code(403);
        echo json_encode([
            'error' => 'Test generation not allowed from this IP',
        ]);
        exit;
    }

    $domain = $input['domain'] ?? 'demo.virtual-ptsp.com';
    $plan = $input['plan'] ?? 'demo';

    $license = generateLicenseKey($domain, $plan, 30); // 30 days for demo
    $licenses = getLicenses();
    $licenses[] = $license;
    saveLicenses($licenses);

    echo json_encode([
        'success' => true,
        'message' => 'Demo license generated',
        'license' => $license,
        'note' => 'This license is for testing purposes only',
    ]);
    exit;
}

// ============================================================
// 404 Handler
// ============================================================
http_response_code(404);
echo json_encode([
    'error' => 'Endpoint not found',
    'available_endpoints' => [
        'GET  /' => 'Health check',
        'POST /validate' => 'Validate license',
        'POST /register' => 'Register new license (admin)',
        'GET  /check' => 'Check license status',
        'POST /deactivate' => 'Deactivate license (admin)',
        'POST /generate-test' => 'Generate test license',
    ],
]);

/*
============================================================
INSTRUCTIONS FOR DEPLOYMENT:
============================================================

1. Upload this file to your license server VPS
2. Set environment variable: export LICENSE_SECRET="your_secure_secret"
3. Set environment variable: export ADMIN_SECRET="your_admin_secret"
4. Configure nginx to serve this file:
   location / {
       try_files $uri /validate.php?q=$uri;
   }

5. In customer .env:
   LICENSE_SERVER_URL=https://your-license-server.com
   LICENSE_SERVER_SECRET=your_secure_secret

6. To generate a license (from admin panel or CLI):
   curl -X POST https://your-license-server.com/register \
     -H "Content-Type: application/json" \
     -d '{"admin_secret":"your_admin_secret","domain":"customer.com","plan":"pro","months":12}'

============================================================
*/