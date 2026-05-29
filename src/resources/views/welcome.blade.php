<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Virtual PTSP - Omnichannel Customer Service Platform with AI">
    <title>Virtual PTSP - Pelayanan Prima, Tanpa Batas</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Three.js for 3D -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>

    <!-- Particle.js -->
    <script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        // Clerk-inspired palette
                        clerk: {
                            dark: '#0A0F1C',
                            primary: '#6366F1',
                            secondary: '#8B5CF6',
                            accent: '#EC4899',
                            surface: '#111827',
                            muted: '#6B7280',
                            border: '#1F2937',
                            success: '#10B981',
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
        /* ============================================================
           Virtual PTSP - Landing Page Styles
           Built with ❤️ by zhayyn (+6281317361689)
           Inspired by clerk.com design
           ============================================================ */

        * {
            font-family: 'Inter', system-ui, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #0A0F1C 0%, #111827 50%, #0A0F1C 100%);
            color: #F9FAFB;
            overflow-x: hidden;
        }

        /* Smooth scroll */
        html {
            scroll-behavior: smooth;
        }

        /* Custom cursor - clerk-like glow effect */
        .cursor-glow {
            cursor: pointer;
            position: relative;
        }

        .cursor-glow::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.3) 0%, transparent 70%);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: all 0.3s ease;
            pointer-events: none;
        }

        .cursor-glow:hover::before {
            width: 300%;
            height: 300%;
        }

        /* Particle container */
        #particles-js {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            pointer-events: none;
        }

        /* 3D Canvas */
        #3d-canvas {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
            pointer-events: none;
        }

        /* Glass morphism cards - clerk style */
        .glass-card {
            background: rgba(17, 24, 39, 0.7);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .glass-card:hover {
            background: rgba(17, 24, 39, 0.9);
            border-color: rgba(99, 102, 241, 0.5);
            transform: translateY(-4px);
            box-shadow: 0 20px 40px -10px rgba(99, 102, 241, 0.2);
        }

        /* Gradient text animation */
        .gradient-text {
            background: linear-gradient(135deg, #6366F1 0%, #8B5CF6 50%, #EC4899 100%);
            background-size: 200% 200%;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: gradientShift 3s ease infinite;
        }

        @keyframes gradientShift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        /* Floating animation */
        .floating {
            animation: floating 6s ease-in-out infinite;
        }

        @keyframes floating {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(2deg); }
        }

        /* Pulse glow effect */
        .pulse-glow {
            animation: pulseGlow 2s ease-in-out infinite;
        }

        @keyframes pulseGlow {
            0%, 100% {
                box-shadow: 0 0 20px rgba(99, 102, 241, 0.3),
                            0 0 40px rgba(139, 92, 246, 0.2),
                            0 0 60px rgba(236, 72, 153, 0.1);
            }
            50% {
                box-shadow: 0 0 30px rgba(99, 102, 241, 0.5),
                            0 0 60px rgba(139, 92, 246, 0.3),
                            0 0 100px rgba(236, 72, 153, 0.2);
            }
        }

        /* Button styles - clerk-like */
        .btn-primary {
            background: linear-gradient(135deg, #6366F1 0%, #6366F1 100%);
            color: white;
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.2s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-primary:hover {
            transform: scale(1.02);
            box-shadow: 0 10px 30px -5px rgba(99, 102, 241, 0.5);
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, transparent 100%);
        }

        .btn-primary:active {
            transform: scale(0.98);
        }

        /* Glow border animation */
        .glow-border {
            position: relative;
            background: #111827;
            border-radius: 16px;
        }

        .glow-border::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(135deg, #6366F1, #8B5CF6, #EC4899);
            border-radius: 18px;
            z-index: -1;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .glow-border:hover::before {
            opacity: 1;
        }

        /* Fade in animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .animate-in {
            animation: fadeIn 0.6s ease forwards;
        }

        /* Stagger delay */
        .delay-100 { animation-delay: 100ms; }
        .delay-200 { animation-delay: 200ms; }
        .delay-300 { animation-delay: 300ms; }
        .delay-400 { animation-delay: 400ms; }
        .delay-500 { animation-delay: 500ms; }

        /* Scroll reveal */
        .reveal {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .reveal.active {
            opacity: 1;
            transform: translateY(0);
        }

        /* Navbar gradient */
        .navbar-gradient {
            background: linear-gradient(180deg, rgba(10, 15, 28, 0.95) 0%, rgba(10, 15, 28, 0) 100%);
        }

        /* Feature card hover effect */
        .feature-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.2) 0%, rgba(139, 92, 246, 0.2) 100%);
            border: 1px solid rgba(99, 102, 241, 0.3);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
        }

        /* Stats counter animation */
        .counter {
            font-variant-numeric: tabular-nums;
        }

        /* Code block styling */
        .code-block {
            background: #0D1117;
            border: 1px solid #30363D;
            border-radius: 12px;
            font-family: 'JetBrains Mono', 'Fira Code', monospace;
            font-size: 14px;
            line-height: 1.6;
        }

        /* Social proof logos */
        .logo-container {
            filter: grayscale(100%);
            opacity: 0.5;
            transition: all 0.3s ease;
        }

        .logo-container:hover {
            filter: grayscale(0%);
            opacity: 1;
        }

        /* Footer gradient */
        .footer-gradient {
            background: linear-gradient(0deg, #0A0F1C 0%, transparent 100%);
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #0A0F1C;
        }

        ::-webkit-scrollbar-thumb {
            background: #30363D;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #6366F1;
        }

        /* Hero badge */
        .hero-badge {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(139, 92, 246, 0.1) 100%);
            border: 1px solid rgba(99, 102, 241, 0.3);
            padding: 8px 16px;
            border-radius: 100px;
            font-size: 14px;
            color: #A5B4FC;
        }

        /* Input focus glow */
        .input-glow:focus {
            outline: none;
            border-color: #6366F1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
        }

        /* Marquee animation */
        .marquee {
            animation: marquee 30s linear infinite;
        }

        @keyframes marquee {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }

        /* Live indicator */
        .live-dot {
            width: 8px;
            height: 8px;
            background: #10B981;
            border-radius: 50%;
            animation: livePulse 2s ease-in-out infinite;
        }

        @keyframes livePulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(1.2); }
        }
    </style>
</head>
<body class="min-h-screen">
    <!-- Particles Background -->
    <div id="particles-js"></div>

    <!-- 3D Canvas -->
    <canvas id="3d-canvas"></canvas>

    <!-- Content Container -->
    <div class="relative z-10">

        <!-- ============================================================
             NAVIGATION
             ============================================================ -->
        <nav class="fixed top-0 left-0 right-0 z-50 navbar-gradient">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-20">
                    <!-- Logo -->
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-clerk-primary to-clerk-secondary flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                        </div>
                        <span class="text-xl font-bold text-white">Virtual PTSP</span>
                    </div>

                    <!-- Desktop Nav -->
                    <div class="hidden md:flex items-center space-x-8">
                        <a href="#features" class="text-gray-300 hover:text-white transition cursor-glow px-3 py-2">Features</a>
                        <a href="#pricing" class="text-gray-300 hover:text-white transition cursor-glow px-3 py-2">Pricing</a>
                        <a href="#docs" class="text-gray-300 hover:text-white transition cursor-glow px-3 py-2">Docs</a>
                        <a href="#demo" class="text-gray-300 hover:text-white transition cursor-glow px-3 py-2">Demo</a>
                    </div>

                    <!-- CTA Buttons -->
                    <div class="flex items-center space-x-4">
                        <a href="/login" class="text-gray-300 hover:text-white transition hidden sm:block">Sign In</a>
                        <a href="/register" class="btn-primary text-sm">Get Started Free</a>
                    </div>

                    <!-- Mobile Menu Button -->
                    <button class="md:hidden text-white" id="mobile-menu-btn">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </nav>

        <!-- ============================================================
             HERO SECTION
             ============================================================ -->
        <section class="relative min-h-screen flex items-center justify-center pt-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <!-- Badge -->
                <div class="hero-badge inline-flex items-center space-x-2 mb-8 animate-in">
                    <span class="live-dot"></span>
                    <span>Now with AI-Powered Auto Reply</span>
                </div>

                <!-- Main Heading -->
                <h1 class="text-5xl sm:text-6xl lg:text-7xl font-bold mb-6 animate-in delay-100">
                    <span class="text-white">Omnichannel</span>
                    <br>
                    <span class="gradient-text">Customer Service Platform</span>
                </h1>

                <!-- Subheading -->
                <p class="text-xl sm:text-2xl text-gray-400 max-w-3xl mx-auto mb-8 animate-in delay-200">
                   elola pelanggan dari berbagai channel dalam satu tempat.
                    WhatsApp, Web Chat, Instagram, dan lainnya — dengan AI yang bekerja 24/7.
                </p>

                <!-- CTA Buttons -->
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mb-12 animate-in delay-300">
                    <a href="/register" class="btn-primary text-lg px-8 py-4 cursor-glow">
                        Mulai Gratis — Tidak Perlu Kartu Kredit
                    </a>
                    <a href="#demo" class="text-gray-300 hover:text-white transition flex items-center space-x-2 px-4 py-4 cursor-glow">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>Lihat Demo</span>
                    </a>
                </div>

                <!-- 3D Emoji Container -->
                <div class="relative w-full h-64 animate-in delay-400 floating">
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="relative">
                            <div class="w-32 h-32 rounded-full bg-gradient-to-br from-clerk-primary via-clerk-secondary to-clerk-accent pulse-glow flex items-center justify-center">
                                <span class="text-5xl">🤖</span>
                            </div>
                            <div class="absolute -top-4 -right-4 w-10 h-10 rounded-full bg-gradient-to-br from-clerk-primary to-clerk-secondary flex items-center justify-center text-lg animate-bounce">
                                24/7
                            </div>
                            <div class="absolute -bottom-2 -left-6 w-8 h-8 rounded-full bg-gradient-to-br from-clerk-accent to-pink-500 flex items-center justify-center text-sm animate-bounce" style="animation-delay: 0.5s;">
                                ⚡
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Social Proof -->
                <div class="animate-in delay-500">
                    <p class="text-gray-500 text-sm mb-4">Dipercaya oleh tim support di seluruh Indonesia</p>
                    <div class="flex flex-wrap items-center justify-center gap-8 opacity-60">
                        <span class="text-gray-400 font-medium">PTSG Jakarta</span>
                        <span class="text-gray-600">•</span>
                        <span class="text-gray-400 font-medium">PA Medan</span>
                        <span class="text-gray-600">•</span>
                        <span class="text-gray-400 font-medium">Distributor ABC</span>
                        <span class="text-gray-600">•</span>
                        <span class="text-gray-400 font-medium">Dan 50+ lainnya</span>
                    </div>
                </div>
            </div>

            <!-- Scroll indicator -->
            <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 animate-bounce">
                <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                </svg>
            </div>
        </section>

        <!-- ============================================================
             FEATURES SECTION
             ============================================================ -->
        <section id="features" class="py-24">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Section Header -->
                <div class="text-center mb-16 reveal">
                    <span class="hero-badge inline-block mb-4">FEATURES</span>
                    <h2 class="text-4xl sm:text-5xl font-bold text-white mb-4">
                        Semua yang Anda butuhkan untuk
                        <span class="gradient-text">Customer Service</span>
                    </h2>
                    <p class="text-xl text-gray-400 max-w-2xl mx-auto">
                        Platform lengkap dengan AI bawaan untuk mengelola percakapan pelanggan dari berbagai channel
                    </p>
                </div>

                <!-- Features Grid -->
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Feature 1: Omnichannel -->
                    <div class="glass-card p-6 reveal cursor-glow">
                        <div class="feature-icon">
                            <svg class="w-6 h-6 text-clerk-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-white mb-2">Omnichannel Inbox</h3>
                        <p class="text-gray-400">WhatsApp, Web Chat, Instagram, Facebook, Telegram — semua dalam satu unified inbox</p>
                    </div>

                    <!-- Feature 2: AI Auto Reply -->
                    <div class="glass-card p-6 reveal cursor-glow" style="animation-delay: 100ms;">
                        <div class="feature-icon">
                            <svg class="w-6 h-6 text-clerk-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-white mb-2">AI Auto-Reply with RAG</h3>
                        <p class="text-gray-400">Intelligent chatbot yang menjawab dari knowledge base Anda sendiri 24/7</p>
                    </div>

                    <!-- Feature 3: Knowledge Base -->
                    <div class="glass-card p-6 reveal cursor-glow" style="animation-delay: 200ms;">
                        <div class="feature-icon">
                            <svg class="w-6 h-6 text-clerk-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-white mb-2">Knowledge Base</h3>
                        <p class="text-gray-400">Upload file, teks manual, atau scraping web untuk AI belajar dari data Anda</p>
                    </div>

                    <!-- Feature 4: Multi AI Provider -->
                    <div class="glass-card p-6 reveal cursor-glow" style="animation-delay: 300ms;">
                        <div class="feature-icon">
                            <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-white mb-2">Multi AI Provider</h3>
                        <p class="text-gray-400">Pilih Gemini, Claude, atau OpenAI. Ganti provider kapan saja dengan satu klik</p>
                    </div>

                    <!-- Feature 5: Team Collaboration -->
                    <div class="glass-card p-6 reveal cursor-glow" style="animation-delay: 400ms;">
                        <div class="feature-icon">
                            <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-white mb-2">Team Collaboration</h3>
                        <p class="text-gray-400">Assign percakapan ke agent, catat private notes, dan.track performa tim</p>
                    </div>

                    <!-- Feature 6: Analytics -->
                    <div class="glass-card p-6 reveal cursor-glow" style="animation-delay: 500ms;">
                        <div class="feature-icon">
                            <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-white mb-2">Real-time Analytics</h3>
                        <p class="text-gray-400">Dashboard dengan metrik response time, resolution rate, dan customer satisfaction</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================
             AI CONFIG SECTION
             ============================================================ -->
        <section class="py-24 relative">
            <div class="absolute inset-0 bg-gradient-to-b from-transparent via-clerk-primary/5 to-transparent"></div>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
                <div class="grid lg:grid-cols-2 gap-12 items-center">
                    <!-- Image/Demo -->
                    <div class="reveal">
                        <div class="glass-card p-4 glow-border">
                            <!-- Mock Dashboard -->
                            <div class="bg-clerk-surface rounded-xl p-6">
                                <!-- Mock Header -->
                                <div class="flex items-center justify-between mb-6">
                                    <div class="flex items-center space-x-2">
                                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-clerk-primary to-clerk-secondary"></div>
                                        <span class="font-semibold">AI Configuration</span>
                                    </div>
                                    <span class="live-dot"></span>
                                </div>

                                <!-- Mock Config Form -->
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm text-gray-400 mb-2">AI Provider</label>
                                        <div class="flex gap-2">
                                            <input type="radio" id="gemini" name="provider" checked class="hidden peer/gemini">
                                            <label for="gemini" class="px-4 py-2 rounded-lg bg-clerk-dark border border-clerk-border cursor-pointer peer-checked/gemini:border-clerk-primary peer-checked/gemini:bg-clerk-primary/20 transition">
                                                🤖 Gemini
                                            </label>
                                            <input type="radio" id="claude" name="provider" class="hidden peer/claude">
                                            <label for="claude" class="px-4 py-2 rounded-lg bg-clerk-dark border border-clerk-border cursor-pointer peer-checked/claude:border-clerk-secondary peer-checked/claude:bg-clerk-secondary/20 transition">
                                                🧠 Claude
                                            </label>
                                            <input type="radio" id="openai" name="provider" class="hidden peer/openai">
                                            <label for="openai" class="px-4 py-2 rounded-lg bg-clerk-dark border border-clerk-border cursor-pointer peer-checked/openai:border-clerk-accent peer-checked/openai:bg-clerk-accent/20 transition">
                                                💬 OpenAI
                                            </label>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm text-gray-400 mb-2">API Key</label>
                                        <input type="password" value="AIza_xxxxxxxxxxxx" class="w-full bg-clerk-dark border border-clerk-border rounded-lg px-4 py-3 text-white input-glow" disabled>
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm text-gray-400 mb-2">Model</label>
                                            <select class="w-full bg-clerk-dark border border-clerk-border rounded-lg px-4 py-3 text-white">
                                                <option>gemini-2.0-flash</option>
                                                <option>gemini-1.5-pro</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm text-gray-400 mb-2">Temperature</label>
                                            <input type="range" min="0" max="1" step="0.1" value="0.7" class="w-full">
                                        </div>
                                    </div>

                                    <button class="w-full btn-primary">💾 Save Configuration</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Text Content -->
                    <div class="reveal" style="animation-delay: 200ms;">
                        <span class="hero-badge inline-block mb-4">AI CONFIGURATION</span>
                        <h2 class="text-4xl font-bold text-white mb-6">
                            Pilih AI Provider
                            <span class="gradient-text"> sesuka Anda</span>
                        </h2>
                        <p class="text-xl text-gray-400 mb-6">
                            Tidak terikat dengan satu provider. Ganti Gemini ke Claude ke OpenAI
                            dengan mudah — cukup update API key di dashboard.
                        </p>

                        <ul class="space-y-4 mb-8">
                            <li class="flex items-start space-x-3">
                                <svg class="w-6 h-6 text-green-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="text-gray-300">Gemini — Gratis & Cepat (Rekomendasi)</span>
                            </li>
                            <li class="flex items-start space-x-3">
                                <svg class="w-6 h-6 text-green-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="text-gray-300">Claude — Kualitas Premium</span>
                            </li>
                            <li class="flex items-start space-x-3">
                                <svg class="w-6 h-6 text-green-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="text-gray-300">OpenAI — Standar Industri</span>
                            </li>
                        </ul>

                        <a href="/register" class="btn-primary inline-block">Mulai dengan Gemini Gratis</a>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================
             KNOWLEDGE BASE SECTION
             ============================================================ -->
        <section class="py-24">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16 reveal">
                    <span class="hero-badge inline-block mb-4">KNOWLEDGE BASE</span>
                    <h2 class="text-4xl sm:text-5xl font-bold text-white mb-4">
                        AI Belajar dari
                        <span class="gradient-text">Data Anda</span>
                    </h2>
                    <p class="text-xl text-gray-400">
                        Upload dokumen, masukkan teks, atau scrape website — AI akan menggunakan data ini untuk menjawab pertanyaan pelanggan
                    </p>
                </div>

                <!-- KB Types Grid -->
                <div class="grid md:grid-cols-3 gap-6 reveal">
                    <!-- PDF/Document Upload -->
                    <div class="glass-card p-6 text-center cursor-glow">
                        <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-gradient-to-br from-red-500/20 to-orange-500/20 border border-red-500/30 flex items-center justify-center">
                            <span class="text-3xl">📄</span>
                        </div>
                        <h3 class="text-lg font-semibold text-white mb-2">Upload Dokumen</h3>
                        <p class="text-gray-400 text-sm">PDF, DOCX, TXT, XLSX — AI akan ekstrak dan pelajari isinya</p>
                    </div>

                    <!-- Manual Text -->
                    <div class="glass-card p-6 text-center cursor-glow">
                        <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-gradient-to-br from-blue-500/20 to-cyan-500/20 border border-blue-500/30 flex items-center justify-center">
                            <span class="text-3xl">✍️</span>
                        </div>
                        <h3 class="text-lg font-semibold text-white mb-2">Manual Text</h3>
                        <p class="text-gray-400 text-sm">Ketik atau paste teks FAQ, SOP, atau informasi lainnya</p>
                    </div>

                    <!-- Web Scraping -->
                    <div class="glass-card p-6 text-center cursor-glow">
                        <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-gradient-to-br from-green-500/20 to-emerald-500/20 border border-green-500/30 flex items-center justify-center">
                            <span class="text-3xl">🌐</span>
                        </div>
                        <h3 class="text-lg font-semibold text-white mb-2">Web Scraping</h3>
                        <p class="text-gray-400 text-sm">Masukkan URL — AI akan scrape dan pelajari otomatis</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================
             PRICING SECTION
             ============================================================ -->
        <section id="pricing" class="py-24">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16 reveal">
                    <span class="hero-badge inline-block mb-4">PRICING</span>
                    <h2 class="text-4xl sm:text-5xl font-bold text-white mb-4">
                        Simple, <span class="gradient-text">Transparent</span> Pricing
                    </h2>
                    <p class="text-xl text-gray-400">
                        Mulai gratis, upgrade kapan saja. Tidak ada biaya tersembunyi.
                    </p>
                </div>

                <!-- Pricing Cards -->
                <div class="grid md:grid-cols-3 gap-8 max-w-5xl mx-auto">
                    <!-- Free Tier -->
                    <div class="glass-card p-6 reveal cursor-glow">
                        <div class="text-sm text-gray-400 mb-2">Free</div>
                        <div class="flex items-baseline mb-6">
                            <span class="text-4xl font-bold text-white">Rp 0</span>
                            <span class="text-gray-400 ml-2">/selamanya</span>
                        </div>
                        <ul class="space-y-3 mb-8">
                            <li class="flex items-center text-gray-300"><span class="w-5 h-5 mr-3 text-green-400">✓</span> 1 Channel WhatsApp</li>
                            <li class="flex items-center text-gray-300"><span class="w-5 h-5 mr-3 text-green-400">✓</span> 100 messages/bulan</li>
                            <li class="flex items-center text-gray-300"><span class="w-5 h-5 mr-3 text-green-400">✓</span> Gemini AI</li>
                            <li class="flex items-center text-gray-300"><span class="w-5 h-5 mr-3 text-green-400">✓</span> 1 Knowledge Base</li>
                        </ul>
                        <a href="/register" class="block w-full text-center py-3 rounded-lg border border-clerk-border text-white hover:bg-clerk-surface transition">
                            Mulai Gratis
                        </a>
                    </div>

                    <!-- Pro Tier -->
                    <div class="glass-card p-6 relative reveal cursor-glow" style="animation-delay: 100ms;">
                        <div class="absolute -top-3 left-1/2 transform -translate-x-1/2 px-3 py-1 bg-gradient-to-r from-clerk-primary to-clerk-secondary rounded-full text-xs font-semibold text-white">
                            POPULER
                        </div>
                        <div class="text-sm text-clerk-primary mb-2">Pro</div>
                        <div class="flex items-baseline mb-6">
                            <span class="text-4xl font-bold text-white">Rp 199k</span>
                            <span class="text-gray-400 ml-2">/bulan</span>
                        </div>
                        <ul class="space-y-3 mb-8">
                            <li class="flex items-center text-gray-300"><span class="w-5 h-5 mr-3 text-green-400">✓</span> 5 Channels</li>
                            <li class="flex items-center text-gray-300"><span class="w-5 h-5 mr-3 text-green-400">✓</span> Unlimited messages</li>
                            <li class="flex items-center text-gray-300"><span class="w-5 h-5 mr-3 text-green-400">✓</span> Semua AI Provider</li>
                            <li class="flex items-center text-gray-300"><span class="w-5 h-5 mr-3 text-green-400">✓</span> Unlimited KB</li>
                            <li class="flex items-center text-gray-300"><span class="w-5 h-5 mr-3 text-green-400">✓</span> Priority Support</li>
                        </ul>
                        <a href="/register?plan=pro" class="btn-primary block w-full text-center">Mulai 14 Hari Gratis</a>
                    </div>

                    <!-- Enterprise Tier -->
                    <div class="glass-card p-6 reveal cursor-glow" style="animation-delay: 200ms;">
                        <div class="text-sm text-gray-400 mb-2">Enterprise</div>
                        <div class="flex items-baseline mb-6">
                            <span class="text-4xl font-bold text-white">Custom</span>
                        </div>
                        <ul class="space-y-3 mb-8">
                            <li class="flex items-center text-gray-300"><span class="w-5 h-5 mr-3 text-green-400">✓</span> Unlimited Channels</li>
                            <li class="flex items-center text-gray-300"><span class="w-5 h-5 mr-3 text-green-400">✓</span> Self-hosted Option</li>
                            <li class="flex items-center text-gray-300"><span class="w-5 h-5 mr-3 text-green-400">✓</span> Custom AI Model</li>
                            <li class="flex items-center text-gray-300"><span class="w-5 h-5 mr-3 text-green-400">✓</span> Dedicated Support</li>
                            <li class="flex items-center text-gray-300"><span class="w-5 h-5 mr-3 text-green-400">✓</span> SLA Guarantee</li>
                        </ul>
                        <a href="/contact" class="block w-full text-center py-3 rounded-lg border border-clerk-border text-white hover:bg-clerk-surface transition">
                            Hubungi Sales
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================
             CTA SECTION
             ============================================================ -->
        <section class="py-24">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <div class="glass-card p-12 reveal glow-border">
                    <h2 class="text-4xl font-bold text-white mb-4">
                        Siap revolutionize
                        <span class="gradient-text">customer service</span> Anda?
                    </h2>
                    <p class="text-xl text-gray-400 mb-8">
                        Bergabung dengan 50+ tim yang sudah menggunakan Virtual PTSP untuk melayani pelanggan lebih baik
                    </p>
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                        <a href="/register" class="btn-primary text-lg px-8 py-4 cursor-glow">
                            🚀 Mulai Gratis Sekarang
                        </a>
                        <a href="https://wa.me/6281317361689" target="_blank" class="flex items-center space-x-2 text-gray-300 hover:text-white transition">
                            <span>atau</span>
                            <span class="text-green-400">chat kami di WhatsApp</span>
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================
             FOOTER
             ============================================================ -->
        <footer class="py-12 footer-gradient">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row items-center justify-between">
                    <div class="flex items-center space-x-3 mb-4 md:mb-0">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-clerk-primary to-clerk-secondary flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                        </div>
                        <span class="font-semibold text-white">Virtual PTSP</span>
                    </div>

                    <div class="text-gray-400 text-sm mb-4 md:mb-0">
                        Built with ❤️ by <span class="text-clerk-primary">zhayyn</span> (+6281317361689)
                    </div>

                    <div class="flex items-center space-x-6">
                        <a href="#" class="text-gray-400 hover:text-white transition">Docs</a>
                        <a href="#" class="text-gray-400 hover:text-white transition">Privacy</a>
                        <a href="#" class="text-gray-400 hover:text-white transition">Terms</a>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <!-- ============================================================
         SCRIPTS
         ============================================================ -->
    <script>
        // ============================================================
        // Virtual PTSP - Landing Page JavaScript
        // Built with ❤️ by zhayyn (+6281317361689)
        // ============================================================

        // Particles.js Configuration
        particlesJS("particles-js", {
            particles: {
                number: { value: 50, density: { enable: true, value_area: 800 } },
                color: { value: "#6366F1" },
                shape: { type: "circle" },
                opacity: { value: 0.3, random: true },
                size: { value: 3, random: true },
                line_linked: {
                    enable: true,
                    distance: 150,
                    color: "#6366F1",
                    opacity: 0.1,
                    width: 1
                },
                move: {
                    enable: true,
                    speed: 1,
                    direction: "none",
                    random: true,
                    straight: false,
                    out_mode: "out"
                }
            },
            interactivity: {
                detect_on: "canvas",
                events: {
                    onhover: { enable: true, mode: "grab" },
                    onclick: { enable: true, mode: "push" }
                },
                modes: {
                    grab: { distance: 140, line_linked: { opacity: 0.3 } },
                    push: { particles_nb: 4 }
                }
            },
            retina_detect: true
        });

        // ============================================================
        // 3D Scene with Three.js
        // ============================================================
        (function() {
            const canvas = document.getElementById('3d-canvas');
            const renderer = new THREE.WebGLRenderer({ canvas, alpha: true, antialias: true });
            renderer.setSize(window.innerWidth, window.innerHeight);
            renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));

            const scene = new THREE.Scene();
            const camera = new THREE.PerspectiveCamera(45, window.innerWidth / window.innerHeight, 0.1, 1000);
            camera.position.z = 30;

            // Floating orbs
            const orbs = [];
            const orbGeometry = new THREE.SphereGeometry(1, 32, 32);
            const colors = [0x6366F1, 0x8B5CF6, 0xEC4899, 0x10B981];

            for (let i = 0; i < 8; i++) {
                const color = colors[i % colors.length];
                const orbMaterial = new THREE.MeshPhongMaterial({
                    color: color,
                    transparent: true,
                    opacity: 0.4,
                    emissive: color,
                    emissiveIntensity: 0.2
                });
                const orb = new THREE.Mesh(orbGeometry, orbMaterial);
                orb.position.set(
                    (Math.random() - 0.5) * 50,
                    (Math.random() - 0.5) * 40,
                    (Math.random() - 0.5) * 20 - 10
                );
                orb.scale.setScalar(Math.random() * 1.5 + 0.5);
                orb.userData = {
                    originalY: orb.position.y,
                    speed: Math.random() * 0.5 + 0.2,
                    amplitude: Math.random() * 2 + 1
                };
                orbs.push(orb);
                scene.add(orb);
            }

            // Light
            const ambientLight = new THREE.AmbientLight(0xffffff, 0.5);
            scene.add(ambientLight);

            const pointLight = new THREE.PointLight(0x6366F1, 1, 100);
            pointLight.position.set(10, 10, 10);
            scene.add(pointLight);

            // Animation
            function animate() {
                requestAnimationFrame(animate);

                const time = Date.now() * 0.001;

                orbs.forEach((orb, i) => {
                    orb.position.y = orb.userData.originalY + Math.sin(time * orb.userData.speed) * orb.userData.amplitude;
                    orb.rotation.x += 0.002;
                    orb.rotation.y += 0.003;
                });

                renderer.render(scene, camera);
            }
            animate();

            // Resize handler
            window.addEventListener('resize', () => {
                camera.aspect = window.innerWidth / window.innerHeight;
                camera.updateProjectionMatrix();
                renderer.setSize(window.innerWidth, window.innerHeight);
            });
        })();

        // ============================================================
        // Scroll Reveal Animation
        // ============================================================
        const observerOptions = {
            root: null,
            rootMargin: '0px',
            threshold: 0.1
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('active');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.reveal').forEach(el => {
            observer.observe(el);
        });

        // ============================================================
        // Smooth Scroll for Nav Links
        // ============================================================
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth' });
                }
            });
        });

        // ============================================================
        // Mobile Menu Toggle
        // ============================================================
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        if (mobileMenuBtn) {
            mobileMenuBtn.addEventListener('click', () => {
                // Toggle mobile menu - implement as needed
                console.log('Mobile menu clicked');
            });
        }
    </script>
</body>
</html>