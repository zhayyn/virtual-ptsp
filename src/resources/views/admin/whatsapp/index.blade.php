<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>WhatsApp Manager - Virtual PTSP</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- QR Code Library -->
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        clerk: {
                            dark: '#0A0F1C',
                            primary: '#6366F1',
                            secondary: '#8B5CF6',
                            accent: '#EC4899',
                            surface: '#111827',
                            border: '#1F2937',
                        },
                        wa: {
                            green: '#25D366',
                            dark: '#128C7E',
                        }
                    }
                }
            }
        }
    </script>

    <style>
        * { font-family: 'Inter', system-ui, sans-serif; }
        body { background: #0A0F1C; }
        .glass-card {
            background: rgba(17, 24, 39, 0.8);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .btn-primary {
            background: linear-gradient(135deg, #6366F1 0%, #6366F1 100%);
            transition: all 0.2s ease;
        }
        .btn-primary:hover { transform: scale(1.02); }
        .btn-wa {
            background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
            transition: all 0.2s ease;
        }
        .btn-wa:hover { transform: scale(1.02); }
        .pulse-green {
            animation: pulseGreen 2s ease-in-out infinite;
        }
        @keyframes pulseGreen {
            0%, 100% { box-shadow: 0 0 0 0 rgba(37, 211, 102, 0.4); }
            50% { box-shadow: 0 0 0 10px rgba(37, 211, 102, 0); }
        }
        .status-dot {
            width: 12px; height: 12px; border-radius: 50%;
        }
        .status-connected { background: #25D366; }
        .status-disconnected { background: #EF4444; }
        .status-pending { background: #F59E0B; }
    </style>
</head>
<body class="min-h-screen flex">
    <!-- Sidebar (simplified) -->
    @include('layouts.partials.sidebar')

    <!-- Main Content -->
    <main class="flex-1 p-6">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-white">WhatsApp Manager</h1>
                <p class="text-gray-400">Kelola koneksi WhatsApp dan kirim pesan</p>
            </div>
            <div class="flex items-center space-x-4">
                <span id="connection-status" class="flex items-center space-x-2 px-4 py-2 rounded-full glass-card">
                    <span class="status-dot status-disconnected" id="status-dot"></span>
                    <span id="status-text" class="text-sm text-gray-300">Memuat...</span>
                </span>
            </div>
        </div>

        <div class="grid lg:grid-cols-2 gap-6">
            <!-- WhatsApp Connection Panel -->
            <div class="glass-card p-6">
                <div class="flex items-center space-x-3 mb-6">
                    <div class="w-12 h-12 rounded-xl bg-wa-green/20 flex items-center justify-center">
                        <svg class="w-7 h-7 text-wa-green" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-white">WhatsApp Connection</h2>
                        <p class="text-sm text-gray-400">Scan QR code untuk connect</p>
                    </div>
                </div>

                <!-- Device Info (when connected) -->
                <div id="device-info" class="hidden mb-6 p-4 rounded-xl bg-wa-green/10 border border-wa-green/30">
                    <div class="flex items-center space-x-3">
                        <span class="status-dot status-connected pulse-green"></span>
                        <div>
                            <p class="text-white font-medium" id="device-name">WhatsApp Connected</p>
                            <p class="text-sm text-gray-400" id="device-number">-</p>
                        </div>
                    </div>
                    <div class="mt-3 flex items-center justify-between">
                        <div class="flex items-center space-x-2 text-sm text-gray-400">
                            <span>Battery:</span>
                            <span id="device-battery" class="text-wa-green">-</span>
                        </div>
                        <button onclick="disconnectWhatsApp()" class="text-sm text-red-400 hover:text-red-300">
                            Disconnect
                        </button>
                    </div>
                </div>

                <!-- QR Code Area -->
                <div id="qr-section" class="text-center">
                    <div id="qr-container" class="inline-block p-6 rounded-2xl bg-white mb-4">
                        <canvas id="qr-canvas" width="200" height="200"></canvas>
                    </div>
                    <p class="text-sm text-gray-400 mb-4">
                        Scan QR code dengan WhatsApp aplikasi Anda
                    </p>
                    <div class="flex items-center justify-center space-x-4">
                        <button onclick="refreshQR()" class="px-4 py-2 rounded-lg bg-clerk-border text-white hover:bg-clerk-primary transition">
                            🔄 Refresh QR
                        </button>
                        <button onclick="restartConnection()" class="px-4 py-2 rounded-lg bg-clerk-border text-white hover:bg-clerk-primary transition">
                            🔁 Restart
                        </button>
                    </div>
                </div>

                <!-- Instructions -->
                <div class="mt-6 p-4 rounded-xl bg-clerk-surface border border-clerk-border">
                    <h3 class="text-sm font-semibold text-white mb-3">📱 Cara Connect:</h3>
                    <ol class="text-sm text-gray-400 space-y-2">
                        <li>1. Buka WhatsApp di HP Anda</li>
                        <li>2. Ketuk menu ⋮ atau Settings</li>
                        <li>3. Pilih "Perangkat Tertaut"</li>
                        <li>4. Ketuk "Tautkan Perangkat"</li>
                        <li>5. Scan QR code di atas</li>
                    </ol>
                </div>
            </div>

            <!-- Send Message Panel -->
            <div class="glass-card p-6">
                <div class="flex items-center space-x-3 mb-6">
                    <div class="w-12 h-12 rounded-xl bg-clerk-primary/20 flex items-center justify-center">
                        <svg class="w-6 h-6 text-clerk-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-white">Kirim Pesan</h2>
                        <p class="text-sm text-gray-400">Kirim pesan test ke nomor</p>
                    </div>
                </div>

                <form id="send-form" onsubmit="sendTestMessage(event)">
                    <div class="space-y-4">
                        <!-- Phone Number -->
                        <div>
                            <label class="block text-sm text-gray-400 mb-2">Nomor WhatsApp</label>
                            <div class="flex">
                                <span class="inline-flex items-center px-4 rounded-l-lg bg-clerk-surface border border-r-0 border-clerk-border text-gray-400">
                                    +62
                                </span>
                                <input type="text"
                                       id="phone"
                                       placeholder="8123456789"
                                       class="flex-1 px-4 py-3 rounded-r-lg bg-clerk-surface border border-clerk-border text-white placeholder-gray-500 focus:border-clerk-primary focus:outline-none"
                                       required>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Contoh: 8123456789 (tanpa +62)</p>
                        </div>

                        <!-- Message -->
                        <div>
                            <label class="block text-sm text-gray-400 mb-2">Pesan</label>
                            <textarea id="message"
                                      rows="4"
                                      placeholder="Ketik pesan Anda..."
                                      class="w-full px-4 py-3 rounded-lg bg-clerk-surface border border-clerk-border text-white placeholder-gray-500 focus:border-clerk-primary focus:outline-none resize-none"
                                      required></textarea>
                        </div>

                        <!-- Send Button -->
                        <button type="submit"
                                id="send-btn"
                                class="w-full btn-wa py-3 rounded-lg text-white font-semibold flex items-center justify-center space-x-2">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                            </svg>
                            <span>Kirim Pesan</span>
                        </button>
                    </div>
                </form>

                <!-- Result -->
                <div id="send-result" class="mt-4 hidden">
                    <!-- Will be populated by JS -->
                </div>

                <!-- Recent Logs -->
                <div class="mt-6 pt-6 border-t border-clerk-border">
                    <h3 class="text-sm font-semibold text-white mb-4">📋 Recent Activity</h3>
                    <div id="recent-logs" class="space-y-2 max-h-64 overflow-y-auto">
                        @forelse($recentLogs as $log)
                            <div class="flex items-start space-x-3 p-3 rounded-lg bg-clerk-surface">
                                <span class="w-2 h-2 mt-2 rounded-full
                                    @if($log->status === 'success') bg-green-400
                                    @elseif($log->status === 'failed') bg-red-400
                                    @else bg-gray-400 @endif">
                                </span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm text-white truncate">{{ $log->message ?? $log->event }}</p>
                                    <p class="text-xs text-gray-500">{{ $log->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500 text-center py-4">Belum ada aktivitas</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Settings Section -->
        <div class="glass-card p-6 mt-6">
            <h2 class="text-lg font-semibold text-white mb-4">⚙️ Gateway Settings</h2>
            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm text-gray-400 mb-2">Gateway URL</label>
                    <input type="url"
                           id="gateway-url"
                           placeholder="http://192.168.88.44:8790"
                           class="w-full px-4 py-3 rounded-lg bg-clerk-surface border border-clerk-border text-white placeholder-gray-500 focus:border-clerk-primary focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm text-gray-400 mb-2">API Key</label>
                    <input type="password"
                           id="api-key"
                           placeholder="Masukkan API Key"
                           class="w-full px-4 py-3 rounded-lg bg-clerk-surface border border-clerk-border text-white placeholder-gray-500 focus:border-clerk-primary focus:outline-none">
                </div>
            </div>
            <div class="mt-4 flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox" id="auto-reply" class="w-4 h-4 rounded border-gray-600 bg-clerk-surface text-clerk-primary focus:ring-clerk-primary">
                        <span class="text-sm text-gray-300">AI Auto Reply</span>
                    </label>
                </div>
                <button onclick="saveSettings()" class="btn-primary px-6 py-2 rounded-lg text-white font-medium">
                    💾 Save Settings
                </button>
            </div>
        </div>
    </main>

    <script>
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            checkStatus();
            // Poll status every 5 seconds
            setInterval(checkStatus, 5000);
        });

        // Check connection status
        async function checkStatus() {
            try {
                const response = await fetch('/whatsapp/status');
                const data = await response.json();

                const statusDot = document.getElementById('status-dot');
                const statusText = document.getElementById('status-text');
                const deviceInfo = document.getElementById('device-info');
                const qrSection = document.getElementById('qr-section');

                if (data.success && data.connected) {
                    statusDot.className = 'status-dot status-connected';
                    statusText.textContent = 'Connected';
                    deviceInfo.classList.remove('hidden');
                    qrSection.classList.add('hidden');

                    if (data.device) {
                        document.getElementById('device-name').textContent = data.device;
                    }
                    if (data.battery) {
                        document.getElementById('device-battery').textContent = data.battery + '%';
                    }
                } else {
                    statusDot.className = 'status-dot status-disconnected';
                    statusText.textContent = 'Not Connected';
                    deviceInfo.classList.add('hidden');
                    qrSection.classList.remove('hidden');

                    // Auto-refresh QR if needed
                    if (!data.connected) {
                        refreshQR();
                    }
                }
            } catch (error) {
                console.error('Status check failed:', error);
            }
        }

        // Refresh QR code
        async function refreshQR() {
            try {
                const response = await fetch('/whatsapp/qr');
                const data = await response.json();

                if (data.success) {
                    if (data.already_connected) {
                        checkStatus();
                    } else if (data.qr_code) {
                        // Generate QR code image
                        QRCode.toCanvas(document.getElementById('qr-canvas'), data.qr_code, {
                            width: 200,
                            margin: 2,
                            color: {
                                dark: '#000000',
                                light: '#FFFFFF'
                            }
                        });
                    }
                }
            } catch (error) {
                console.error('QR refresh failed:', error);
            }
        }

        // Restart connection
        async function restartConnection() {
            if (!confirm('Restart WhatsApp connection? Anda perlu scan QR lagi.')) return;

            try {
                const response = await fetch('/whatsapp/restart', { method: 'POST' });
                const data = await response.json();

                if (data.success) {
                    checkStatus();
                    setTimeout(refreshQR, 1000);
                } else {
                    alert('Failed: ' + (data.error || 'Unknown error'));
                }
            } catch (error) {
                alert('Error: ' + error.message);
            }
        }

        // Disconnect
        async function disconnectWhatsApp() {
            if (!confirm('Putuskan koneksi WhatsApp?')) return;

            try {
                const response = await fetch('/whatsapp/disconnect', { method: 'POST' });
                const data = await response.json();

                if (data.success) {
                    checkStatus();
                } else {
                    alert('Failed: ' + (data.error || 'Unknown error'));
                }
            } catch (error) {
                alert('Error: ' + error.message);
            }
        }

        // Send test message
        async function sendTestMessage(event) {
            event.preventDefault();

            const phone = document.getElementById('phone').value;
            const message = document.getElementById('message').value;
            const sendBtn = document.getElementById('send-btn');
            const resultDiv = document.getElementById('send-result');

            // Disable button
            sendBtn.disabled = true;
            sendBtn.innerHTML = '<span>Mengirim...</span>';

            try {
                const response = await fetch('/whatsapp/test', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ phone: phone, message: message })
                });

                const data = await response.json();

                resultDiv.classList.remove('hidden');
                if (data.success) {
                    resultDiv.innerHTML = `
                        <div class="p-4 rounded-lg bg-green-500/10 border border-green-500/30">
                            <div class="flex items-center space-x-2 text-green-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span>Pesan berhasil dikirim!</span>
                            </div>
                            <p class="text-sm text-gray-400 mt-1">Message ID: ${data.message_id || '-'}</p>
                        </div>
                    `;
                    document.getElementById('send-form').reset();
                } else {
                    resultDiv.innerHTML = `
                        <div class="p-4 rounded-lg bg-red-500/10 border border-red-500/30">
                            <div class="flex items-center space-x-2 text-red-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                <span>Gagal mengirim pesan</span>
                            </div>
                            <p class="text-sm text-gray-400 mt-1">${data.error || 'Unknown error'}</p>
                        </div>
                    `;
                }
            } catch (error) {
                resultDiv.classList.remove('hidden');
                resultDiv.innerHTML = `
                    <div class="p-4 rounded-lg bg-red-500/10 border border-red-500/30">
                        <p class="text-red-400">Error: ${error.message}</p>
                    </div>
                `;
            }

            // Re-enable button
            sendBtn.disabled = false;
            sendBtn.innerHTML = `
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                </svg>
                <span>Kirim Pesan</span>
            `;
        }

        // Save settings
        async function saveSettings() {
            const gatewayUrl = document.getElementById('gateway-url').value;
            const apiKey = document.getElementById('api-key').value;
            const autoReply = document.getElementById('auto-reply').checked;

            if (!gatewayUrl || !apiKey) {
                alert('Gateway URL dan API Key harus diisi');
                return;
            }

            try {
                const response = await fetch('/whatsapp/settings', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        gateway_url: gatewayUrl,
                        api_key: apiKey,
                        auto_reply_enabled: autoReply
                    })
                });

                const data = await response.json();

                if (data.success) {
                    alert('Settings saved! Refreshing status...');
                    checkStatus();
                } else {
                    alert('Failed: ' + (data.message || 'Unknown error'));
                }
            } catch (error) {
                alert('Error: ' + error.message);
            }
        }
    </script>
</body>
</html>