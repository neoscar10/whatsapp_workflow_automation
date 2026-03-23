<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>WhatsApp Workflow Automation</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            200: '#bbf7d0',
                            300: '#86efac',
                            400: '#4ade80',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
                            800: '#166534',
                            900: '#14532d',
                            950: '#052e16',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .bg-grid-pattern {
            background-image: linear-gradient(to right, rgba(255,255,255,0.05) 1px, transparent 1px),
                              linear-gradient(to bottom, rgba(255,255,255,0.05) 1px, transparent 1px);
            background-size: 40px 40px;
        }
        .hero-glow {
            position: absolute;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(34,197,94,0.15) 0%, rgba(0,0,0,0) 70%);
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 0;
            pointer-events: none;
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-900 dark:bg-zinc-950 dark:text-zinc-100 antialiased selection:bg-primary-500 selection:text-white relative overflow-x-hidden">

    <!-- Navbar -->
    <nav class="absolute w-full top-0 left-0 z-50">
        <div class="max-w-7xl mx-auto px-6 lg:px-8 py-6 flex items-center justify-between border-b border-gray-200/50 dark:border-white/5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-primary-400 to-primary-600 rounded-xl flex items-center justify-center text-white font-bold shadow-lg shadow-primary-500/30">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                </div>
                <span class="text-xl font-bold tracking-tight text-gray-900 dark:text-white">Workflow<span class="text-primary-500">Auto</span></span>
            </div>

            <div class="flex items-center gap-4">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}" class="text-sm font-semibold text-gray-600 hover:text-gray-900 dark:text-zinc-300 dark:hover:text-white transition">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-semibold text-gray-600 hover:text-gray-900 dark:text-zinc-300 dark:hover:text-white transition hidden sm:block">Log in</a>
                        @if (Route::has('company.register'))
                            <a href="{{ route('company.register') }}" class="text-sm font-semibold text-white bg-primary-500 hover:bg-primary-600 px-5 py-2.5 rounded-lg shadow-lg shadow-primary-500/20 transition-all hover:scale-105 active:scale-95">Get Started</a>
                        @endif
                    @endauth
                @endif
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="relative z-10 min-h-screen flex flex-col justify-center pt-24 pb-12">
        <!-- Background elements dark mode -->
        <div class="absolute inset-0 z-0 hidden dark:block">
            <div class="absolute inset-0 bg-grid-pattern opacity-30"></div>
            <div class="hero-glow"></div>
        </div>
        <!-- Background elements light mode -->
        <div class="absolute inset-0 z-0 dark:hidden">
            <div class="absolute inset-0 bg-[radial-gradient(#e5e7eb_1px,transparent_1px)] [background-size:16px_16px] opacity-40"></div>
            <div class="absolute top-0 right-0 -mr-40 -mt-20 w-[600px] h-[600px] bg-primary-200 rounded-full blur-3xl opacity-30 mix-blend-multiply filter pointer-events-none"></div>
            <div class="absolute bottom-0 left-0 -ml-40 -mb-20 w-[600px] h-[600px] bg-blue-200 rounded-full blur-3xl opacity-30 mix-blend-multiply filter pointer-events-none"></div>
        </div>

        <div class="max-w-7xl mx-auto px-6 lg:px-8 relative z-10 w-full mt-12 md:mt-0">
            <div class="text-center max-w-4xl mx-auto">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-primary-500/10 text-primary-600 dark:text-primary-400 text-sm font-medium mb-8 border border-primary-500/20 shadow-sm backdrop-blur-sm">
                    <span class="flex h-2 w-2 relative">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-primary-500"></span>
                    </span>
                    WhatsApp Automation is now live
                </div>
                
                <h1 class="text-5xl md:text-7xl font-extrabold tracking-tight text-gray-900 dark:text-white mb-8 leading-[1.1]">
                    Supercharge your <br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary-400 to-primary-600 dark:from-primary-400 dark:to-primary-500">WhatsApp</span> workflows.
                </h1>
                
                <p class="text-lg md:text-xl text-gray-600 dark:text-zinc-400 mb-10 max-w-2xl mx-auto leading-relaxed">
                    Automate your customer communication, send personalized templates at scale, and streamline your business operations all from one intuitive dashboard.
                </p>
                
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                    @if (Route::has('company.register'))
                        <a href="{{ route('company.register') }}" class="w-full sm:w-auto text-base font-semibold text-white bg-primary-500 hover:bg-primary-600 px-8 py-4 rounded-xl shadow-xl shadow-primary-500/20 transition-all hover:scale-105 active:scale-95 flex items-center justify-center gap-3">
                            Start Automating Today
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                        </a>
                    @endif
                    
                    @if (Route::has('login'))
                        <a href="{{ route('login') }}" class="w-full sm:w-auto text-base font-semibold text-gray-700 bg-white dark:text-zinc-200 dark:bg-zinc-800/50 hover:bg-gray-50 dark:hover:bg-zinc-800 border border-gray-200 dark:border-zinc-700 px-8 py-4 rounded-xl shadow-sm transition-all hover:scale-105 active:scale-95 backdrop-blur-sm flex items-center justify-center">
                            Sign In to Portal
                        </a>
                    @endif
                </div>
            </div>

            <!-- Features Grid -->
            <div class="mt-24 grid md:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="bg-white/60 dark:bg-zinc-900/40 border border-gray-200 dark:border-zinc-800 rounded-2xl p-8 backdrop-blur-sm transition-all hover:bg-white dark:hover:bg-zinc-900 shadow-sm hover:shadow-xl hover:-translate-y-1">
                    <div class="w-12 h-12 bg-primary-100 dark:bg-primary-500/10 text-primary-600 dark:text-primary-400 rounded-xl flex items-center justify-center mb-6">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Template Management</h3>
                    <p class="text-gray-600 dark:text-zinc-400 leading-relaxed">Create, manage, and sync WhatsApp approved message templates seamlessly to engage customers.</p>
                </div>
                <!-- Feature 2 -->
                <div class="bg-white/60 dark:bg-zinc-900/40 border border-gray-200 dark:border-zinc-800 rounded-2xl p-8 backdrop-blur-sm transition-all hover:bg-white dark:hover:bg-zinc-900 shadow-sm hover:shadow-xl hover:-translate-y-1">
                    <div class="w-12 h-12 bg-orange-100 dark:bg-orange-500/10 text-orange-600 dark:text-orange-400 rounded-xl flex items-center justify-center mb-6">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Trigger Workflows</h3>
                    <p class="text-gray-600 dark:text-zinc-400 leading-relaxed">Automate responses and trigger specific actions based on incoming messages or system events.</p>
                </div>
                <!-- Feature 3 -->
                <div class="bg-white/60 dark:bg-zinc-900/40 border border-gray-200 dark:border-zinc-800 rounded-2xl p-8 backdrop-blur-sm transition-all hover:bg-white dark:hover:bg-zinc-900 shadow-sm hover:shadow-xl hover:-translate-y-1">
                    <div class="w-12 h-12 bg-blue-100 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 rounded-xl flex items-center justify-center mb-6">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Advanced Analytics</h3>
                    <p class="text-gray-600 dark:text-zinc-400 leading-relaxed">Track message delivery, open rates, and user engagement with detailed, real-time analytics.</p>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="relative z-10 border-t border-gray-200 dark:border-zinc-800 bg-white/50 dark:bg-zinc-950/50 backdrop-blur-md">
        <div class="max-w-7xl mx-auto px-6 lg:px-8 py-8 flex flex-col md:flex-row items-center justify-between">
            <p class="text-sm text-gray-500 dark:text-zinc-500 mb-4 md:mb-0">
                &copy; {{ date('Y') }} WorkflowAuto. All rights reserved.
            </p>
            <div class="flex gap-6">
                <a href="{{ route('privacy-policy') }}" class="text-sm text-gray-500 hover:text-gray-900 dark:text-zinc-500 dark:hover:text-zinc-300 transition">Privacy Policy</a>
                <a href="#" class="text-sm text-gray-500 hover:text-gray-900 dark:text-zinc-500 dark:hover:text-zinc-300 transition">Terms of Service</a>
            </div>
        </div>
    </footer>

    <script>
        // Check for dark mode preference
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            document.documentElement.classList.add('dark');
        }
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', event => {
            if (event.matches) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        });
    </script>
</body>
</html>
