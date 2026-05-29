<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Virtual PTSP') - Register</title>

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
        body {
            background: linear-gradient(135deg, #0A0F1C 0%, #111827 50%, #0A0F1C 100%);
            color: #F9FAFB;
            min-height: 100vh;
        }
        .glass-card {
            background: rgba(17, 24, 39, 0.8);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .gradient-text {
            background: linear-gradient(135deg, #6366F1 0%, #8B5CF6 50%, #EC4899 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .btn-primary {
            background: linear-gradient(135deg, #6366F1 0%, #6366F1 100%);
            transition: all 0.2s ease;
        }
        .btn-primary:hover {
            transform: scale(1.02);
            box-shadow: 0 10px 30px -5px rgba(99, 102, 241, 0.5);
        }
        .input-field {
            background: rgba(17, 24, 39, 0.9);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.2s ease;
        }
        .input-field:focus {
            border-color: #6366F1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
            outline: none;
        }
        .social-btn {
            background: rgba(17, 24, 39, 0.9);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.2s ease;
        }
        .social-btn:hover {
            border-color: #6366F1;
            background: rgba(99, 102, 241, 0.1);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Logo -->
        <div class="text-center mb-8">
            <a href="/" class="inline-flex items-center space-x-3">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-clerk-primary to-clerk-secondary flex items-center justify-center">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                </div>
                <span class="text-2xl font-bold text-white">Virtual PTSP</span>
            </a>
        </div>

        <!-- Register Card -->
        <div class="glass-card rounded-2xl p-8">
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-white mb-2">Buat Akun Baru</h1>
                <p class="text-gray-400">Mulai 14 hari gratis — tidak perlu kartu kredit</p>
            </div>

            <!-- Google OAuth Button -->
            <a href="{{ route('auth.google') }}"
               class="social-btn flex items-center justify-center space-x-3 w-full py-3 rounded-xl mb-6 cursor-pointer">
                <svg class="w-5 h-5" viewBox="0 0 24 24">
                    <path fill="#EA4335" d="M5.26620003,9.76452917 C6.19878754,6.93863203 8.85444915,4.90990991 12,4.90990991 C13.6909091,4.90990991 15.2181818,5.50909091 16.4181818,6.49090909 L19.9090909,3 C17.7818182,1.14545455 15.0545455,0 12,0 C7.27006974,0 3.1977497,2.69829785 1.23999023,6.65002441 L5.26620003,9.76452917 Z"/>
                    <path fill="#34A853" d="M16.0407269,18.0125889 C14.9509167,18.7163016 13.5660892,19.0909091 12,19.0909091 C8.86648613,19.0909091 6.21911939,17.076871 5.27698177,14.2678769 L1.23746264,17.3349879 C3.19279051,21.2936291 7.26000237,24 12,24 C14.9328362,24 17.7353462,22.9573905 19.834237,20.9995801 L16.0407269,18.0125889 Z"/>
                    <path fill="#4A90E2" d="M19.834237,20.9995801 C22.0291676,18.9520994 23.4545455,15.903663 23.4545455,12 C23.4545455,11.2909091 23.3454545,10.5272727 23.1818182,9.81818182 L12,9.81818182 L12,14.4545455 L18.4363636,14.4545455 C18.7597737,16.2136263 18.中心9363,18.1559091 17.6363636,20 L12,20 C8.90477273,20 6.32127491,18.6134517 4.68119482,16.1639851 L1.57033045,18.5322588 C3.53219301,21.5183216 7.00020259,24 12,24 C14.9328362,24 17.7353462,22.9573905 19.834237,20.9995801 Z"/>
                </svg>
                <span class="text-white font-medium">Daftar dengan Google</span>
            </a>

            <!-- Divider -->
            <div class="relative my-6">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-700"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-4 bg-clerk-surface text-gray-400">atau</span>
                </div>
            </div>

            <!-- Error Messages -->
            @if ($errors->any())
                <div class="mb-4 p-4 rounded-xl bg-red-500/10 border border-red-500/30">
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="text-red-400">{{ $errors->first() }}</span>
                    </div>
                </div>
            @endif

            <!-- Register Form -->
            <form method="POST" action="{{ route('register') }}">
                @csrf

                <div class="space-y-4">
                    <!-- Name -->
                    <div>
                        <label class="block text-sm text-gray-400 mb-2">Nama Lengkap</label>
                        <input type="text"
                               name="name"
                               value="{{ old('name') }}"
                               class="input-field w-full px-4 py-3 rounded-xl text-white placeholder-gray-500"
                               placeholder="Masukkan nama lengkap"
                               required
                               autofocus>
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-sm text-gray-400 mb-2">Email</label>
                        <input type="email"
                               name="email"
                               value="{{ old('email') }}"
                               class="input-field w-full px-4 py-3 rounded-xl text-white placeholder-gray-500"
                               placeholder="nama@email.com"
                               required>
                    </div>

                    <!-- Password -->
                    <div>
                        <label class="block text-sm text-gray-400 mb-2">Password</label>
                        <input type="password"
                               name="password"
                               class="input-field w-full px-4 py-3 rounded-xl text-white placeholder-gray-500"
                               placeholder="Minimal 8 karakter"
                               required>
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label class="block text-sm text-gray-400 mb-2">Konfirmasi Password</label>
                        <input type="password"
                               name="password_confirmation"
                               class="input-field w-full px-4 py-3 rounded-xl text-white placeholder-gray-500"
                               placeholder="Ulangi password"
                               required>
                    </div>

                    <!-- Terms -->
                    <div class="flex items-start space-x-2">
                        <input type="checkbox"
                               name="terms"
                               id="terms"
                               class="w-4 h-4 mt-0.5 rounded border-gray-600 bg-clerk-surface text-clerk-primary focus:ring-clerk-primary"
                               required>
                        <label for="terms" class="text-sm text-gray-400">
                            Saya setuju dengan <a href="#" class="text-clerk-primary hover:underline">Syarat & Ketentuan</a>
                            dan <a href="#" class="text-clerk-primary hover:underline">Kebijakan Privasi</a>
                        </label>
                    </div>

                    <!-- Submit -->
                    <button type="submit" class="btn-primary w-full py-3 rounded-xl text-white font-semibold">
                        Daftar Sekarang
                    </button>
                </div>
            </form>

            <!-- Login Link -->
            <div class="mt-6 text-center">
                <span class="text-gray-400">Sudah punya akun?</span>
                <a href="{{ route('login') }}" class="text-clerk-primary hover:underline ml-1">Masuk</a>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-6 text-gray-500 text-sm">
            Built with ❤️ by <span class="text-clerk-primary">zhayyn</span> (+6281317361689)
        </div>
    </div>
</body>
</html>