<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard - Virtual PTSP</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

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
                            muted: '#6B7280',
                            border: '#1F2937',
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <style>
        * { font-family: 'Inter', system-ui, sans-serif; }
        body { background: #0A0F1C; color: #F9FAFB; }
        .glass-card {
            background: rgba(17, 24, 39, 0.6);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 16px;
        }
        .gradient-text {
            background: linear-gradient(135deg, #6366F1 0%, #8B5CF6 50%, #EC4899 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .nav-item { transition: all 0.2s ease; }
        .nav-item:hover, .nav-item.active {
            background: rgba(99, 102, 241, 0.1);
            color: #6366F1;
        }
        .stat-card {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(139, 92, 246, 0.05) 100%);
            border: 1px solid rgba(99, 102, 241, 0.2);
        }
        .btn-primary {
            background: linear-gradient(135deg, #6366F1 0%, #6366F1 100%);
            transition: all 0.2s ease;
        }
        .btn-primary:hover {
            transform: scale(1.02);
            box-shadow: 0 10px 30px -5px rgba(99, 102, 241, 0.5);
        }
    </style>
</head>
<body class="min-h-screen flex">
    <!-- ============================================================
         SIDEBAR
         ============================================================ -->
    <aside class="w-64 bg-clerk-surface border-r border-clerk-border flex flex-col">
        <!-- Logo -->
        <div class="p-6 border-b border-clerk-border">
            <a href="/dashboard" class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-clerk-primary to-clerk-secondary flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                </div>
                <span class="text-lg font-bold text-white">Virtual PTSP</span>
            </a>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 p-4 space-y-1">
            <a href="/dashboard" class="nav-item active flex items-center space-x-3 px-4 py-3 rounded-xl text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                <span>Dashboard</span>
            </a>

            <a href="/conversations" class="nav-item flex items-center space-x-3 px-4 py-3 rounded-xl text-gray-400 hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                </svg>
                <span>Conversations</span>
                <span class="ml-auto px-2 py-0.5 text-xs rounded-full bg-clerk-primary text-white">{{ $stats['open_conversations'] }}</span>
            </a>

            <a href="/channels" class="nav-item flex items-center space-x-3 px-4 py-3 rounded-xl text-gray-400 hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <span>Channels</span>
            </a>

            <div class="pt-4">
                <p class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase">AI & Knowledge</p>
            </div>

            <a href="/ai/config" class="nav-item flex items-center space-x-3 px-4 py-3 rounded-xl text-gray-400 hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                </svg>
                <span>AI Configuration</span>
            </a>

            <a href="/knowledge-base" class="nav-item flex items-center space-x-3 px-4 py-3 rounded-xl text-gray-400 hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
                <span>Knowledge Base</span>
            </a>

            <a href="/whatsapp/settings" class="nav-item flex items-center space-x-3 px-4 py-3 rounded-xl text-gray-400 hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                </svg>
                <span>WhatsApp</span>
            </a>

            <div class="pt-4">
                <p class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase">Settings</p>
            </div>

            <a href="/settings" class="nav-item flex items-center space-x-3 px-4 py-3 rounded-xl text-gray-400 hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <span>Settings</span>
            </a>
        </nav>

        <!-- User Profile -->
        <div class="p-4 border-t border-clerk-border">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-clerk-primary to-clerk-secondary flex items-center justify-center text-white font-semibold">
                    {{ substr(Auth::user()->name ?? 'U', 0, 1) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-white truncate">{{ Auth::user()->name ?? 'User' }}</p>
                    <p class="text-xs text-gray-400 truncate">{{ Auth::user()->email ?? '' }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="p-2 rounded-lg hover:bg-clerk-border text-gray-400 hover:text-white transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- ============================================================
         MAIN CONTENT
         ============================================================ -->
    <main class="flex-1 flex flex-col">
        <!-- Header -->
        <header class="h-16 bg-clerk-surface border-b border-clerk-border flex items-center justify-between px-6">
            <div>
                <h1 class="text-xl font-semibold text-white">Dashboard</h1>
                <p class="text-sm text-gray-400">Selamat datang kembali! 👋</p>
            </div>
            <div class="flex items-center space-x-4">
                <button class="p-2 rounded-lg hover:bg-clerk-border text-gray-400 hover:text-white transition relative">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                    <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                </button>
            </div>
        </header>

        <!-- Dashboard Content -->
        <div class="flex-1 p-6 overflow-auto">
            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Total Conversations -->
                <div class="stat-card glass-card p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 rounded-xl bg-clerk-primary/20 flex items-center justify-center">
                            <svg class="w-6 h-6 text-clerk-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                        </div>
                    </div>
                    <p class="text-3xl font-bold text-white">{{ $stats['total_conversations'] }}</p>
                    <p class="text-sm text-gray-400">Total Percakapan</p>
                </div>

                <!-- Open Conversations -->
                <div class="stat-card glass-card p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 rounded-xl bg-green-500/20 flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <span class="px-2 py-1 text-xs rounded-full bg-green-500/20 text-green-400">
                            {{ $stats['open_conversations'] }} aktif
                        </span>
                    </div>
                    <p class="text-3xl font-bold text-white">{{ $stats['open_conversations'] }}</p>
                    <p class="text-sm text-gray-400">Percakapan Terbuka</p>
                </div>

                <!-- Active Channels -->
                <div class="stat-card glass-card p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 rounded-xl bg-blue-500/20 flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <p class="text-3xl font-bold text-white">{{ $stats['total_channels'] }}</p>
                    <p class="text-sm text-gray-400">Channel Aktif</p>
                </div>

                <!-- Today Messages -->
                <div class="stat-card glass-card p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 rounded-xl bg-purple-500/20 flex items-center justify-center">
                            <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                            </svg>
                        </div>
                    </div>
                    <p class="text-3xl font-bold text-white">{{ $stats['today_messages'] }}</p>
                    <p class="text-sm text-gray-400">Pesan Hari Ini</p>
                </div>
            </div>

            <!-- AI Stats & Recent Activity -->
            <div class="grid lg:grid-cols-3 gap-6 mb-8">
                <!-- AI Stats -->
                <div class="glass-card p-6">
                    <h3 class="text-lg font-semibold text-white mb-4">🤖 AI Statistics</h3>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-400">Total AI Chats</span>
                            <span class="text-xl font-bold text-white">{{ $aiStats['total_ai_chats'] }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-400">AI Hari Ini</span>
                            <span class="text-xl font-bold text-clerk-primary">{{ $aiStats['ai_today'] }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-400">Avg Response</span>
                            <span class="text-xl font-bold text-green-400">{{ round($aiStats['avg_response_time']) }}ms</span>
                        </div>
                    </div>
                    <a href="/ai/config" class="mt-4 block text-center py-2 rounded-lg border border-clerk-border text-gray-300 hover:bg-clerk-border transition">
                        Configure AI →
                    </a>
                </div>

                <!-- Message Trend -->
                <div class="glass-card p-6 lg:col-span-2">
                    <h3 class="text-lg font-semibold text-white mb-4">📊 Message Trend (7 Hari)</h3>
                    <div class="flex items-end justify-between h-32 space-x-2">
                        @foreach($messageTrend as $trend)
                            <div class="flex-1 flex flex-col items-center">
                                <div class="w-full bg-clerk-primary/30 rounded-t-lg hover:bg-clerk-primary/50 transition cursor-pointer"
                                     style="height: {{ max(10, min(100, $trend['count'] * 5)) }}%"></div>
                                <span class="text-xs text-gray-500 mt-2">{{ $trend['date'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Recent Conversations -->
            <div class="glass-card p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-white">💬 Percakapan Terbaru</h3>
                    <a href="/conversations" class="text-clerk-primary hover:underline text-sm">Lihat Semua →</a>
                </div>

                @if($recentConversations->isEmpty())
                    <div class="text-center py-12">
                        <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-clerk-border flex items-center justify-center">
                            <svg class="w-8 h-8 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                        </div>
                        <p class="text-gray-400">Belum ada percakapan</p>
                        <a href="/channels/create" class="mt-4 inline-block btn-primary px-4 py-2 rounded-lg text-sm">
                            Tambah Channel
                        </a>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($recentConversations as $conversation)
                            <a href="/conversations/{{ $conversation->id }}"
                               class="flex items-center space-x-4 p-4 rounded-xl bg-clerk-surface hover:bg-clerk-border transition">
                                <div class="w-12 h-12 rounded-xl bg-clerk-primary/20 flex items-center justify-center text-2xl">
                                    {{ $conversation->channel ? App\Models\Channel::getTypeIcon($conversation->channel_type) : '💬' }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <p class="font-medium text-white truncate">{{ $conversation->contact_name }}</p>
                                        <span class="text-xs text-gray-500">{{ $conversation->last_message_at?->diffForHumans() }}</span>
                                    </div>
                                    <p class="text-sm text-gray-400 truncate">{{ $conversation->last_message_preview ?? 'No message' }}</p>
                                </div>
                                <span class="px-2 py-1 text-xs rounded-full
                                    @if($conversation->status === 'open') bg-green-500/20 text-green-400
                                    @elseif($conversation->status === 'pending') bg-yellow-500/20 text-yellow-400
                                    @else bg-gray-500/20 text-gray-400
                                    @endif">
                                    {{ ucfirst($conversation->status) }}
                                </span>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Quick Actions -->
            <div class="grid md:grid-cols-3 gap-6 mt-8">
                <a href="/channels/create" class="glass-card p-6 hover:border-clerk-primary/50 transition cursor-pointer group">
                    <div class="w-12 h-12 rounded-xl bg-clerk-primary/20 flex items-center justify-center mb-4 group-hover:bg-clerk-primary/30 transition">
                        <svg class="w-6 h-6 text-clerk-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                    </div>
                    <h4 class="font-semibold text-white mb-2">Tambah Channel</h4>
                    <p class="text-sm text-gray-400">WhatsApp, Web Chat, dll</p>
                </a>

                <a href="/knowledge-base/create" class="glass-card p-6 hover:border-clerk-secondary/50 transition cursor-pointer group">
                    <div class="w-12 h-12 rounded-xl bg-clerk-secondary/20 flex items-center justify-center mb-4 group-hover:bg-clerk-secondary/30 transition">
                        <svg class="w-6 h-6 text-clerk-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                    </div>
                    <h4 class="font-semibold text-white mb-2">Tambah Knowledge</h4>
                    <p class="text-sm text-gray-400">Upload file atau teks</p>
                </a>

                <a href="/ai/config" class="glass-card p-6 hover:border-clerk-accent/50 transition cursor-pointer group">
                    <div class="w-12 h-12 rounded-xl bg-clerk-accent/20 flex items-center justify-center mb-4 group-hover:bg-clerk-accent/30 transition">
                        <svg class="w-6 h-6 text-clerk-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                    </div>
                    <h4 class="font-semibold text-white mb-2">Konfigurasi AI</h4>
                    <p class="text-sm text-gray-400">Gemini, Claude, OpenAI</p>
                </a>
            </div>
        </div>
    </main>

    <!-- ============================================================
         JAVASCRIPT
         ============================================================ -->
    <script>
        // Simple sidebar active state
        const navItems = document.querySelectorAll('.nav-item');
        navItems.forEach(item => {
            if (item.href === window.location.href) {
                item.classList.add('active');
            }
        });
    </script>
</body>
</html>