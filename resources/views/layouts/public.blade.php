<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'NURISK — NU Peduli Jateng')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    @stack('head')
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --nu-green: #157347;
            --nu-green-dark: #0f5c38;
            --nu-green-light: #e8f5ee;
            --bg: #f0f2f5;
            --glass-bg: rgba(255,255,255,0.60);
            --glass-border: rgba(255,255,255,0.35);
            --glass-shadow: 0 8px 32px rgba(0,0,0,0.08);
            --header-height: 48px;
            --nav-height: 76px;
            --sidebar-width: 240px;
            --dock-height: 66px;
            --dock-bottom: 14px;
        }
        html, body {
            height: 100%;
            font-family: 'Outfit', sans-serif;
            background: var(--bg);
            color: #1a1a2e;
            -webkit-font-smoothing: antialiased;
        }

        /* ===== HEADER ===== */
        .public-header {
            position: fixed; top: 0; left: 0; right: 0;
            height: var(--header-height); z-index: 100;
            background: var(--glass-bg);
            backdrop-filter: blur(20px) saturate(200%);
            border-bottom: 1px solid var(--glass-border);
            display: flex; align-items: center;
            justify-content: space-between;
            padding: 0 32px;
            box-shadow: 0 1px 12px rgba(0,0,0,0.04);
        }
        .public-header .logo { display: flex; align-items: center; gap: 12px; text-decoration: none; }
        .public-header .logo-icon {
            width: 36px; height: 36px;
            background: linear-gradient(135deg, var(--nu-green), var(--nu-green-dark));
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-weight: 800; font-size: 15px;
        }
        .public-header .logo-text { font-weight: 700; font-size: 18px; color: #1a1a2e; line-height: 1.2; }
        .public-header .logo-text small { display: block; font-weight: 400; font-size: 11px; color: #888; }
        .public-header .header-actions { display: flex; align-items: center; gap: 10px; }
        .header-btn {
            padding: 8px 20px; border-radius: 20px; font-size: 14px;
            font-weight: 600; text-decoration: none; transition: all 0.2s;
        }
        .header-btn-primary { background: var(--nu-green); color: #fff; }
        .header-btn-primary:hover { background: var(--nu-green-dark); }
        .header-btn-ghost { background: transparent; color: #555; }
        .header-btn-ghost:hover { background: rgba(0,0,0,0.05); color: #1a1a2e; }

        /* ===== LAYOUT: SIDEBAR + CONTENT ===== */
        .public-layout {
            display: flex;
            padding-top: var(--header-height);
            min-height: 100vh;
        }

        /* Hide sidebar when auth pages request it */
        .public-layout.full-width .public-sidebar { display: none; }
        .public-layout.full-width .public-content { max-width: 100%; }

        /* Sidebar navigasi desktop */
        .public-sidebar {
            width: var(--sidebar-width);
            flex-shrink: 0;
            padding: 24px 16px 16px;
            display: flex;
            flex-direction: column;
            gap: 4px;
            border-right: 1px solid rgba(0,0,0,0.04);
            background: rgba(255,255,255,0.4);
            position: sticky;
            top: var(--header-height);
            height: calc(100vh - var(--header-height));
            overflow-y: auto;
        }
        .sidebar-label {
            font-size: 10px; font-weight: 600; text-transform: uppercase;
            letter-spacing: 1px; color: #aaa; margin: 16px 0 8px 12px;
        }
        .sidebar-item {
            display: flex; align-items: center; gap: 12px;
            padding: 10px 14px; border-radius: 12px;
            text-decoration: none; color: #666; font-size: 14px; font-weight: 500;
            transition: all 0.2s;
        }
        .sidebar-item:hover { background: rgba(21,115,71,0.08); color: var(--nu-green); }
        .sidebar-item.active { background: var(--nu-green-light); color: var(--nu-green); font-weight: 600; }
        .sidebar-item .si-icon { font-size: 18px; width: 24px; text-align: center; }
        .sidebar-item.sidebar-fab {
            background: linear-gradient(135deg, var(--nu-green), var(--nu-green-dark));
            color: #fff; margin-top: 8px;
            box-shadow: 0 4px 14px rgba(21,115,71,0.3);
        }
        .sidebar-item.sidebar-fab:hover { transform: translateY(-1px); color: #fff; }

        /* Main content */
        .public-content {
            flex: 1; min-width: 0;
            padding: 24px 32px 100px;
        }
        .page-container { max-width: 1200px; margin: 0 auto; }

        /* Bottom dock — hanya untuk mobile/tablet */
        .bottom-dock {
            display: none;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 1024px) {
            .public-sidebar { display: none; }
            .public-content { padding: 16px 16px 90px; }
            .public-header { padding: 0 16px; }
            .bottom-dock {
                display: flex;
                position: fixed; bottom: var(--dock-bottom); left: 50%;
                transform: translateX(-50%); z-index: 200;
                background: rgba(255,255,255,0.72);
                backdrop-filter: blur(24px) saturate(200%);
                border: 1px solid rgba(255,255,255,0.45);
                border-radius: 18px;
                box-shadow: 0 4px 24px rgba(0,0,0,0.08);
                align-items: center; justify-content: space-around;
                padding: 5px 6px;
                width: calc(100% - 24px);
                max-width: 440px;
                height: var(--dock-height);
            }
            .dock-item {
                display: flex; flex-direction: column; align-items: center;
                justify-content: center; gap: 2px; text-decoration: none;
                color: #aaa; font-size: 9px; font-weight: 500;
                padding: 6px 8px 4px; border-radius: 14px;
                transition: all 0.25s ease; flex: 1; min-width: 44px;
                position: relative;
            }
            .dock-item .dock-icon { font-size: 18px; line-height: 1; color: #bbb; transition: color 0.25s ease; }
            .dock-item .dock-label { font-size: 8px; opacity: 0.7; transition: opacity 0.25s ease; }
            .dock-item:hover { color: var(--nu-green); }
            .dock-item:hover .dock-icon { color: var(--nu-green); }
            .dock-item.active .dock-icon { color: #0a5a30; }
            .dock-item.active .dock-label { opacity: 1; color: #0a5a30; font-weight: 600; }
            .dock-item.dock-fab {
                flex: 1.2;
            }
            .dock-item.dock-fab .dock-icon {
                background: #dc3545;
                color: #fff;
                width: 30px; height: 30px;
                border-radius: 10px;
                display: flex; align-items: center; justify-content: center;
                font-size: 14px;
                box-shadow: 0 2px 10px rgba(220,53,69,0.35);
            }
            .dock-item.dock-fab:hover .dock-icon {
                background: #c82333;
                transform: scale(1.05);
            }
            .dock-item.dock-fab.active .dock-icon {
                background: #c82333;
                box-shadow: 0 2px 14px rgba(220,53,69,0.5);
            }
        }
        @media (max-width: 480px) {
            .bottom-dock { height: 60px; padding: 4px 4px; bottom: 10px; }
            .dock-item { padding: 5px 4px 3px; min-width: 38px; }
            .dock-item .dock-icon { font-size: 16px; }
            .dock-item .dock-label { font-size: 7px; }
            .dock-item.dock-fab .dock-icon { width: 26px; height: 26px; font-size: 12px; }
        }

        /* Utility */
        .glass-card {
            background: var(--glass-bg);
            backdrop-filter: blur(12px);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            box-shadow: var(--glass-shadow);
        }
    </style>
    @stack('styles')
</head>
<body>

    {{-- HEADER --}}
    <header class="public-header">
        <a href="{{ route('public.home') }}" class="logo">
            <div class="logo-icon">NU</div>
            <div class="logo-text">NURISK <small>NU Peduli Jawa Tengah</small></div>
        </a>
        <div class="header-actions">
            <a href="{{ route('login') }}" class="header-btn header-btn-ghost">Masuk</a>
            <a href="{{ route('register') }}" class="header-btn header-btn-primary">Daftar</a>
        </div>
    </header>

    {{-- LAYOUT — add "full-width" class for auth pages --}}
    <div class="public-layout @yield('layout-class')">

        {{-- SIDEBAR (desktop) --}}
        <aside class="public-sidebar">
            <div class="sidebar-label">Menu</div>
            <a href="{{ route('public.home') }}" class="sidebar-item @yield('nav-home', '')">
                <span class="si-icon">🏠</span> Home
            </a>
            <a href="{{ route('public.map') }}" class="sidebar-item @yield('nav-map', '')">
                <span class="si-icon">🗺️</span> Map
            </a>
            <a href="{{ route('public.resource') }}" class="sidebar-item @yield('nav-resource', '')">
                <span class="si-icon">📦</span> Resource
            </a>
            <div class="sidebar-label">Akun</div>
            <a href="{{ route('login') }}" class="sidebar-item @yield('nav-profil', '')">
                <span class="si-icon">👤</span> Profil
            </a>
            <a href="{{ route('public.lapor') }}" class="sidebar-item sidebar-fab @yield('nav-lapor', '')">
                <span class="si-icon">📢</span> Lapor Kejadian
            </a>
        </aside>

        {{-- CONTENT --}}
        <main class="public-content">
            @yield('content')
        </main>

    </div>

    {{-- BOTTOM DOCK (mobile/tablet only) --}}
    <nav class="bottom-dock">
        <a href="{{ route('public.home') }}" class="dock-item @yield('nav-home', '')">
            <span class="dock-icon"><i class="fa-solid fa-house"></i></span>
            <span class="dock-label">Home</span>
        </a>
        <a href="{{ route('public.map') }}" class="dock-item @yield('nav-map', '')">
            <span class="dock-icon"><i class="fa-solid fa-map"></i></span>
            <span class="dock-label">Map</span>
        </a>
        <a href="{{ route('public.lapor') }}" class="dock-item dock-fab @yield('nav-lapor', '')">
            <span class="dock-icon"><i class="fa-solid fa-bullhorn"></i></span>
            <span class="dock-label">Lapor</span>
        </a>
        <a href="{{ route('public.resource') }}" class="dock-item @yield('nav-resource', '')">
            <span class="dock-icon"><i class="fa-solid fa-cubes"></i></span>
            <span class="dock-label">Resource</span>
        </a>
        <a href="{{ route('login') }}" class="dock-item @yield('nav-profil', '')">
            <span class="dock-icon"><i class="fa-solid fa-user"></i></span>
            <span class="dock-label">Profil</span>
        </a>
    </nav>

    @stack('scripts')
</body>
</html>
