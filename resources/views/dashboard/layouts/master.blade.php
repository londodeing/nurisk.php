<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>NURISK Command Center</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    @stack('styles')
</head>
<body class="bg-light">
    <div class="d-flex" style="min-height: 100vh;">
        {{-- Sidebar --}}
        <nav class="bg-dark text-white" style="width: 240px; min-height: 100vh; position: fixed; top: 0; left: 0; overflow-y: auto;">
            <div class="p-3 border-bottom border-secondary">
                <h5 class="mb-0"><i class="bi bi-shield-check me-2"></i>NURISK CC</h5>
                <small class="text-secondary">{{ strtoupper($role ?? '') }}</small>
            </div>
            <div class="p-3">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->routeIs('dashboard.*') ? 'active bg-primary rounded' : '' }}" href="#">
                            <i class="bi bi-speedometer2 me-2"></i>Dashboard
                        </a>
                    </li>
                    @if(in_array($role ?? '', ['super_admin', 'pwnu', 'pcnu']))
                    <li class="nav-item mt-1">
                        <a class="nav-link text-white text-secondary" href="{{ url('/insiden') }}">
                            <i class="bi bi-list-check me-2"></i>Insiden
                        </a>
                    </li>
                    <li class="nav-item mt-1">
                        <a class="nav-link text-white text-secondary" href="{{ url('/surat') }}">
                            <i class="bi bi-envelope me-2"></i>Surat
                        </a>
                    </li>
                    @endif
                </ul>
            </div>
        </nav>

        {{-- Main Content --}}
        <div style="margin-left: 240px; flex: 1;">
            {{-- Top Navbar --}}
            <nav class="navbar navbar-expand navbar-light bg-white shadow-sm px-4">
                <div class="container-fluid">
                    <span class="navbar-brand mb-0 h6">{{ $pageTitle ?? 'Command Center' }}</span>
                    <div class="d-flex align-items-center gap-2">
                        <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="offcanvas" data-bs-target="#contactDirectory">
                            <i class="bi bi-telephone"></i>
                        </button>
                        <span class="badge bg-primary rounded-pill">{{ auth()->user()?->profil?->nama_lengkap ?? 'User' }}</span>
                    </div>
                </div>
            </nav>

            {{-- Decision Queue Row --}}
            <div class="px-4 pt-3">
                <x-dashboard-decision-queue />
            </div>

            {{-- Alert Bar --}}
            <div class="px-4 pt-2">
                <x-dashboard-alert-bar />
            </div>

            {{-- Quick Actions --}}
            <div class="px-4 pt-2">
                <x-dashboard-quick-actions />
            </div>

            {{-- Page Content --}}
            <main class="p-4">
                @yield('content')
            </main>
        </div>
    </div>

    {{-- Contact Directory Offcanvas --}}
    <x-dashboard-contact-directory />

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/dashboard.js') }}"></script>
    @stack('scripts')
</body>
</html>
