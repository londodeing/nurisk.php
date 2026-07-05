<x-app-layout>
    <x-slot name="header">Dashboard Saya</x-slot>
    <x-slot name="breadcrumb">Home / Dashboard</x-slot>

    <div class="max-w-2xl mx-auto space-y-6">
        <div class="bg-white rounded-xl border border-gray-200 p-6 text-center">
            <div class="w-16 h-16 rounded-full bg-green-100 text-green-600 flex items-center justify-center mx-auto mb-3">
                <i class="bi bi-person-circle text-3xl"></i>
            </div>
            <h2 class="text-xl font-bold text-gray-900">Selamat datang, {{ Auth::user()->profil->nama_lengkap ?? Auth::user()->no_hp }}</h2>
            <div class="mt-2">
                <x-badge-status :status="Auth::user()->status_akun" map="akun" />
            </div>
        </div>

        @php
        $activeAssignment = \App\Models\OperasiPenugasan::where('id_pengguna', Auth::id())
            ->where('status_penugasan', 'aktif')
            ->whereNull('waktu_selesai')
            ->with(['insiden', 'posaju'])
            ->first();
        @endphp

        @if($activeAssignment)
        <div class="bg-white rounded-xl border border-l-4 border-l-green-500 border-gray-200 p-6">
            <div class="flex items-center gap-3 mb-4">
                <span class="w-3 h-3 rounded-full bg-green-500 animate-pulse"></span>
                <h3 class="text-sm font-semibold text-green-700">⚡ Sedang Bertugas</h3>
            </div>
            <div class="space-y-2">
                <p class="text-sm"><span class="text-gray-500">Insiden:</span> <span class="font-semibold">{{ $activeAssignment->insiden?->kode_kejadian ?? '-' }}</span></p>
                <p class="text-sm"><span class="text-gray-500">Peran:</span> <span class="font-semibold">{{ ucfirst($activeAssignment->peran) }}</span></p>
                <p class="text-sm"><span class="text-gray-500">Lokasi:</span> <span class="font-semibold">{{ $activeAssignment->posaju?->nama_posaju ?? '-' }}</span></p>
                <p class="text-sm"><span class="text-gray-500">Sejak:</span> <span class="font-semibold">{{ $activeAssignment->waktu_mulai ? \Carbon\Carbon::parse($activeAssignment->waktu_mulai)->locale('id')->isoFormat('D MMM YYYY') : '-' }}</span></p>
            </div>
        </div>
        @else
        <div class="bg-white rounded-xl border border-gray-200 p-6 text-center">
            <div class="text-4xl mb-3">📋</div>
            <p class="text-sm text-gray-500">Tidak ada penugasan aktif saat ini.</p>
            <p class="text-xs text-gray-400 mt-1">Periksa slot kebutuhan relawan.</p>
        </div>
        @endif

        @php
        $riwayat = \App\Models\OperasiPenugasan::where('id_pengguna', Auth::id())
            ->orderBy('dibuat_pada', 'desc')
            ->take(3)
            ->get();
        @endphp
        @if($riwayat->count())
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Riwayat Penugasan</h3>
            <div class="space-y-3">
                @foreach($riwayat as $r)
                <div class="flex items-center justify-between text-sm">
                    <div>
                        <span class="font-medium text-gray-800">{{ $r->insiden?->kode_kejadian ?? '-' }}</span>
                        <span class="text-gray-400 ml-2">{{ ucfirst($r->peran) }}</span>
                    </div>
                    <x-badge-status :status="$r->status_penugasan" map="insiden" />
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</x-app-layout>
