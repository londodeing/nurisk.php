@php
$user = Auth::user();
$role = optional($user->peran)->nama_peran;
$roleRelawan = $role === 'relawan';
$rolePcnu = $role === 'pcnu';
$rolePwnu = $role === 'pwnu' || $role === 'super_admin';
$roleSuper = $role === 'super_admin';
@endphp

{{-- ═══════════ RELAWAN NAVIGATION ═══════════ --}}
@if($roleRelawan)
<div class="space-y-1">
    <p class="px-3 text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-2">Beranda</p>
    <x-nav-link href="{{ route('dashboard.relawan') }}" :active="request()->routeIs('dashboard.relawan')" icon="bi-house-door">Dashboard Saya</x-nav-link>

    <p class="px-3 text-[10px] font-semibold text-gray-500 uppercase tracking-wider mt-4 mb-2">Lapangan</p>
    <x-nav-link href="#" icon="bi-clipboard-check">Penugasan Saya</x-nav-link>
    <x-nav-link href="#" icon="bi-diagram-2">Klaster Saya</x-nav-link>

    <p class="px-3 text-[10px] font-semibold text-gray-500 uppercase tracking-wider mt-4 mb-2">Laporkan</p>
    <x-nav-link href="{{ route('public.lapor') }}" icon="bi-send-plus">Buat Laporan Kejadian</x-nav-link>

    <p class="px-3 text-[10px] font-semibold text-gray-500 uppercase tracking-wider mt-4 mb-2">Profil</p>
    <x-nav-link href="{{ route('profile.edit') }}" icon="bi-person">Data Diri & Keahlian</x-nav-link>
</div>
@endif

{{-- ═══════════ PCNU NAVIGATION ═══════════ --}}
@if($rolePcnu)
<div class="space-y-1">
    <p class="px-3 text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-2">Beranda</p>
    <x-nav-link href="{{ route('dashboard.pcnu') }}" :active="request()->routeIs('dashboard.pcnu')" icon="bi-house-door">Dashboard PCNU</x-nav-link>

    <p class="px-3 text-[10px] font-semibold text-gray-500 uppercase tracking-wider mt-4 mb-2">Operasional</p>
    <x-nav-link href="{{ route('dashboard.laporan.index') }}" :active="request()->routeIs('dashboard.laporan.*')" icon="bi-inbox">Laporan Masuk</x-nav-link>
    <x-nav-link href="{{ route('insiden.index') }}" :active="request()->routeIs('insiden.*') && !request()->routeIs('insiden.*.pleno.*')" icon="bi-exclamation-triangle">Insiden Saya</x-nav-link>
    <x-nav-link href="{{ route('posaju.index') }}" :active="request()->routeIs('posaju.*')" icon="bi-geo-alt">Pos Aju</x-nav-link>
    <x-nav-link href="{{ route('logistik.permintaan.index') }}" :active="request()->routeIs('logistik.*')" icon="bi-box-seam">Logistik</x-nav-link>
    <x-nav-link href="#" icon="bi-people">Pengungsian</x-nav-link>

    <p class="px-3 text-[10px] font-semibold text-gray-500 uppercase tracking-wider mt-4 mb-2">Governance</p>
    <x-nav-link href="{{ route('surat.index') }}" :active="request()->routeIs('surat.*')" icon="bi-envelope">Surat Menyurat</x-nav-link>
    <x-nav-link href="{{ route('governance.approval.index') }}" icon="bi-check-circle">Approval Center</x-nav-link>

    <p class="px-3 text-[10px] font-semibold text-gray-500 uppercase tracking-wider mt-4 mb-2">Administrasi</p>
    <x-nav-link href="{{ route('admin.approval.index') }}" icon="bi-person-check">Approval TRC</x-nav-link>
</div>
@endif

{{-- ═══════════ PWNU / SUPER_ADMIN NAVIGATION ═══════════ --}}
@if($rolePwnu)
<div class="space-y-1">
    <p class="px-3 text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-2">Beranda</p>
    <x-nav-link href="{{ route('dashboard.pwnu') }}" :active="request()->routeIs('dashboard.pwnu')" icon="bi-speedometer2">Dashboard</x-nav-link>

    <p class="px-3 text-[10px] font-semibold text-gray-500 uppercase tracking-wider mt-4 mb-2">Operasional</p>
    <x-nav-link href="{{ route('dashboard.laporan.index') }}" :active="request()->routeIs('dashboard.laporan.*')" icon="bi-inbox">Laporan Kejadian</x-nav-link>
    <x-nav-link href="{{ route('insiden.index') }}" :active="request()->routeIs('insiden.*') && !request()->routeIs('insiden.*.assessment.*') && !request()->routeIs('insiden.*.pleno.*')" icon="bi-exclamation-triangle">Insiden</x-nav-link>
    <x-nav-link href="{{ route('posaju.index') }}" :active="request()->routeIs('posaju.*')" icon="bi-geo-alt">Pos Aju</x-nav-link>
    <x-nav-link href="{{ route('logistik.permintaan.index') }}" :active="request()->routeIs('logistik.*')" icon="bi-box-seam">Logistik</x-nav-link>
    <x-nav-link href="#" icon="bi-people">Pengungsian</x-nav-link>
    <x-nav-link href="{{ route('klaster.index') }}" :active="request()->routeIs('klaster.*')" icon="bi-diagram-2">Klaster</x-nav-link>

    <p class="px-3 text-[10px] font-semibold text-gray-500 uppercase tracking-wider mt-4 mb-2">Governance</p>
    <x-nav-link href="{{ route('surat.index') }}" :active="request()->routeIs('surat.*')" icon="bi-envelope">Surat Menyurat</x-nav-link>
    <x-nav-link href="{{ route('governance.approval.index') }}" icon="bi-check-circle">Approval Center</x-nav-link>

    @if($roleSuper)
    <p class="px-3 text-[10px] font-semibold text-gray-500 uppercase tracking-wider mt-4 mb-2">Inventaris</p>
    <x-nav-link href="{{ route('inventaris.index') }}" icon="bi-box">Aset PWNU</x-nav-link>

    <p class="px-3 text-[10px] font-semibold text-gray-500 uppercase tracking-wider mt-4 mb-2">Analisis</p>
    <x-nav-link href="{{ route('histori.index') }}" icon="bi-clock-history">Histori Bencana</x-nav-link>
    <x-nav-link href="#" icon="bi-map">Peta Risiko</x-nav-link>

    <p class="px-3 text-[10px] font-semibold text-gray-500 uppercase tracking-wider mt-4 mb-2">Administrasi</p>
    <x-nav-link href="{{ route('admin.pengguna.index') }}" icon="bi-people-fill">Pengguna</x-nav-link>
    <x-nav-link href="{{ route('admin.approval.index') }}" icon="bi-person-check">Approval Registrasi</x-nav-link>
    {{-- <x-nav-link href="#" icon="bi-journal-text">Audit Log</x-nav-link> --}}
    @endif

    <p class="px-3 text-[10px] font-semibold text-gray-500 uppercase tracking-wider mt-4 mb-2">Monitoring</p>
    <x-nav-link href="{{ route('command-center') }}" icon="bi-display" class="text-yellow-400">Command Center</x-nav-link>

    @if($roleSuper)
    <p class="px-3 text-[10px] font-semibold text-gray-500 uppercase tracking-wider mt-4 mb-2">Master Data</p>
    <x-nav-link href="{{ route('admin.jabatan.index') }}" icon="bi-briefcase">Jabatan</x-nav-link>
    @endif
</div>
@endif
