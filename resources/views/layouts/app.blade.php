<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'NURISK'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800" rel="stylesheet" />
    <style>
        body { font-family: 'Inter', system-ui, sans-serif; }
        [x-cloak] { display: none !important; }
        html { scroll-behavior: smooth; }
    </style>
    @stack('styles')
</head>
<body class="bg-gray-100 h-screen overflow-hidden flex antialiased text-gray-900" x-data="{ sidebarOpen: window.innerWidth >= 1024 }" x-init="window.addEventListener('resize', () => { if (window.innerWidth >= 1024) sidebarOpen = true })">

    <x-toast />
    <x-confirm-modal />

    <aside id="sidebar" x-show="sidebarOpen" x-cloak class="w-64 flex-none bg-gray-900 h-screen overflow-y-auto flex flex-col fixed lg:static inset-y-0 left-0 z-30 transition-all duration-200">
        <div class="h-16 flex-none bg-gray-950 flex items-center px-4 gap-3">
            <div class="w-8 h-8 bg-primary-600 rounded-lg flex items-center justify-center flex-shrink-0">
                <i class="bi bi-shield-check text-white text-sm"></i>
            </div>
            <div class="flex-1 min-w-0">
                <span class="text-white font-bold text-sm">NURISK</span>
                <span class="text-gray-500 text-[10px] ml-1">ver 1.0</span>
            </div>
            <button @click="sidebarOpen = false" class="lg:hidden text-gray-500 hover:text-white">
                <i class="bi bi-x-lg text-sm"></i>
            </button>
        </div>

        <div class="px-4 py-3 flex items-center gap-3 border-b border-gray-800">
            <div class="w-8 h-8 rounded-full bg-gray-700 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                {{ strtoupper(substr(Auth::user()->profil->nama_lengkap ?? 'G', 0, 1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-white truncate">{{ Auth::user()->profil->nama_lengkap ?? Auth::user()->no_hp }}</p>
                <x-badge-status :status="optional(Auth::user()->peran)->nama_peran ?? 'unknown'" map="akun" />
            </div>
        </div>

        <nav class="flex-1 py-4 px-3 space-y-1 overflow-y-auto">
            @include('layouts.navigation-menu')
        </nav>

        <div class="px-3 py-4 border-t border-gray-800 space-y-1">
            <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-3 py-2 text-sm text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition-colors">
                <i class="bi bi-gear text-base"></i>
                Pengaturan Akun
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex items-center gap-3 px-3 py-2 text-sm text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition-colors w-full">
                    <i class="bi bi-box-arrow-left text-base"></i>
                    Keluar
                </button>
            </form>
        </div>
    </aside>

    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        <header class="h-14 flex-none bg-white border-b border-gray-200 flex items-center justify-between px-4 lg:px-6 z-10">
            <div class="flex items-center gap-3">
                <button @click="sidebarOpen = !sidebarOpen" class="text-gray-500 hover:text-gray-700 lg:hidden">
                    <i class="bi bi-list text-xl"></i>
                </button>
                @if(isset($breadcrumb))
                <div class="text-sm text-gray-500">
                    {{ $breadcrumb }}
                </div>
                @endif
                @if(isset($header))
                <h1 class="text-lg font-bold text-gray-900">{{ $header }}</h1>
                @endif
            </div>
            <div class="flex items-center gap-4">
                <div class="relative" x-data="{ notifOpen: false }">
                    <button @click="notifOpen = !notifOpen" class="relative text-gray-500 hover:text-gray-700">
                        <i class="bi bi-bell text-xl"></i>
                        @auth
                        @php
                        $notifCount = Cache::remember("notif_count_" . Auth::id(), 60, function() {
                            return 0;
                        });
                        @endphp
                        @if($notifCount > 0 && !auth()->user()->hasRole(['relawan']))
                        <span class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center">{{ $notifCount }}</span>
                        @endif
                        @endauth
                    </button>
                </div>
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" @click.away="open = false" class="flex items-center gap-2 text-sm font-medium text-gray-700 hover:text-gray-900">
                        <span>{{ Auth::user()->profil->nama_lengkap ?? Auth::user()->no_hp }}</span>
                        <i class="bi bi-chevron-down text-xs" :class="open ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="open" x-cloak class="absolute right-0 mt-2 w-48 bg-white rounded-xl border border-gray-200 shadow-lg z-50 py-1">
                        <a href="{{ route('profile.edit') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                            <i class="bi bi-person me-2"></i> Profil Saya
                        </a>
                        <a href="#" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                            <i class="bi bi-key me-2"></i> Ganti Kata Sandi
                        </a>
                        <hr class="my-1 border-gray-100">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="flex w-full items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                <i class="bi bi-box-arrow-right me-2"></i> Keluar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-6">
            <x-alert-bar />
            @if(isset($actions))
            <div class="mb-4 flex justify-end">
                {{ $actions }}
            </div>
            @endif
            {{ $slot ?? '' }}
            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>
