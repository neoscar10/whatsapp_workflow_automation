<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $title ?? 'WhatsApp Cloud Panel' }}</title>
    
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-25..0" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-background-light font-display text-slate-900 antialiased dark:bg-background-dark dark:text-slate-100">
    <div 
        class="flex h-screen overflow-hidden"
        x-data="{ sidebarOpen: localStorage.getItem('sidebarOpen') !== 'false' }"
        x-init="$watch('sidebarOpen', value => localStorage.setItem('sidebarOpen', value))"
    >
        @include('partials.panel.sidebar', [
            'activeNav' => $activeNav ?? null,
            'storage' => $storage ?? null,
        ])

        <main class="flex flex-1 flex-col overflow-y-auto">
            @include('partials.panel.topbar', [
                'topbarUser' => $topbarUser ?? null,
                'topbarMode' => $topbarMode ?? 'default',
                'topbarTitle' => $topbarTitle ?? null,
                'topbarSearchPlaceholder' => $topbarSearchPlaceholder ?? 'Search across messages, team, or logs...',
                'topbarPrimaryActionLabel' => $topbarPrimaryActionLabel ?? null,
                'topbarBreadcrumbLabel' => $topbarBreadcrumbLabel ?? null,
            ])

            <div class="flex-1 flex flex-col relative w-full h-full min-h-0">
                @if(session()->has('impersonator_user_id'))
                    <div class="bg-red-600 text-white text-xs font-bold px-8 py-2.5 flex items-center justify-between gap-4 select-none shrink-0">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-[16px] animate-pulse">admin_panel_settings</span>
                            <span>Impersonating <strong>{{ auth()->user()?->company?->name }}</strong> (logged in as {{ auth()->user()?->name }}).</span>
                        </div>
                        <a href="{{ route('superadmin.stop-impersonating') }}" class="bg-white text-red-650 hover:bg-slate-100 text-red-600 text-[11px] font-black px-4 py-1.5 rounded-lg uppercase tracking-wider transition-all select-none">
                            Exit & Return to Admin
                        </a>
                    </div>
                @endif
                @if(auth()->user()?->company?->status === 'demo')
                    <div class="bg-amber-500 text-white text-xs font-bold px-8 py-2 flex items-center justify-between gap-4 select-none shrink-0">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-[16px] animate-pulse">info</span>
                            <span>This company account is currently in <strong>Demo Mode</strong>. Features consumed will use demo credits. Real payments are disabled.</span>
                        </div>
                    </div>
                @endif
                {{ $slot }}
            </div>
        </main>
    </div>

    @livewireScripts
    @stack('scripts')
</body>
</html>
