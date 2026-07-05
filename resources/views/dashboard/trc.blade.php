<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <span class="font-bold">TRC: ACTIVE</span>
            <span class="px-3 py-1 bg-emerald-100 text-emerald-700 text-xs font-bold rounded-full border border-emerald-200 flex items-center gap-2 shadow-sm" id="gps-status">
                <i class="bi bi-geo-alt-fill"></i> GPS: OK
            </span>
        </div>
    </x-slot>

    @push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        #incident-map { height: 200px; border-radius: 12px; z-index: 1; }
        .trc-card { background: rgba(255,255,255,0.85); backdrop-filter: blur(20px); border-radius: 16px; overflow: hidden; box-shadow: 0 10px 40px rgba(0,0,0,.08); }
        .status-badge { display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; border-radius: 8px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; }
        .badge-assigned  { background: #fef3c7; color: #d97706; border: 1px solid #fde68a; }
        .badge-on_route  { background: #dbeafe; color: #2563eb; border: 1px solid #bfdbfe; }
        .badge-on_site   { background: #d1fae5; color: #059669; border: 1px solid #a7f3d0; }
        .badge-sedang    { background: #fef3c7; color: #d97706; border: 1px solid #fde68a; }
        .badge-tinggi    { background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }
        .badge-kritis    { background: #f3e8ff; color: #7c3aed; border: 1px solid #ddd6fe; }
        .btn-primary { display:block; background: linear-gradient(135deg, #6366f1, #4f46e5); color: #fff; border: none; border-radius: 12px; padding: 14px 20px; font-size: 15px; font-weight: 700; cursor: pointer; width: 100%; text-decoration: none; text-align: center; transition: opacity .15s; }
        .btn-primary:hover { opacity: .9; color: #fff; }
        .sitrep-item { padding: 12px 16px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; }
        .sitrep-item:last-child { border-bottom: none; }
    </style>
    @endpush

    @php
        $assignment  = $initialData['assignment'] ?? null;
        $hasAssign   = $assignment && !empty($assignment['title']);
        $lat         = $hasAssign ? ($assignment['latitude'] ?? null)  : null;
        $lng         = $hasAssign ? ($assignment['longitude'] ?? null) : null;
        $hasMap      = $lat && $lng;
        $status      = strtolower($assignment['status'] ?? '');
        $insidenId   = $assignment['id_insiden'] ?? null;
    @endphp

    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="mb-4 px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl font-semibold flex items-center gap-2" id="flash-success">
        <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="mb-4 px-4 py-3 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl font-semibold flex items-center gap-2">
        <i class="bi bi-exclamation-circle-fill"></i> {{ session('error') }}
    </div>
    @endif

    {{-- ==================== KARTU PENUGASAN AKTIF ==================== --}}
    <div class="mb-5">
        <div class="trc-card border-l-4 border-rose-500 p-5 relative">
            <div class="absolute top-0 right-0 w-28 h-28 rounded-full bg-rose-500/10 blur-2xl pointer-events-none -mr-6 -mt-6"></div>

            <div class="flex items-start justify-between mb-3">
                <div>
                    <p class="text-slate-500 font-bold text-xs uppercase tracking-wider mb-1">Tugas Asesmen Saat Ini</p>
                    <h5 class="font-bold text-lg text-slate-800 leading-tight">
                        {{ $hasAssign ? ($assignment['title']) : 'Tidak ada penugasan aktif' }}
                    </h5>
                </div>
                @if($hasAssign)
                <span class="status-badge badge-{{ $status }} ml-3 flex-shrink-0">{{ strtoupper($status) }}</span>
                @endif
            </div>

            @if($hasAssign)
            {{-- Info Grid --}}
            <div class="bg-slate-50/70 border border-slate-100 rounded-xl p-4 mb-4 grid grid-cols-3 gap-3 text-sm">
                <div>
                    <p class="text-slate-400 text-xs font-bold uppercase mb-1">No. SPK</p>
                    <p class="font-semibold text-slate-700">{{ $assignment['nomor_spk'] ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-slate-400 text-xs font-bold uppercase mb-1">Prioritas</p>
                    <p class="font-semibold text-rose-600">{{ $assignment['priority'] ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-slate-400 text-xs font-bold uppercase mb-1">Alamat</p>
                    <p class="font-semibold text-slate-700 text-xs leading-tight">{{ $assignment['alamat'] ?? '-' }}</p>
                </div>
            </div>

            {{-- Laporan Asal Ringkasan --}}
            @if(!empty($assignment['deskripsi_laporan']))
            <div class="bg-amber-50/60 border border-amber-100 rounded-xl p-4 mb-4">
                <p class="text-amber-700 text-xs font-bold uppercase mb-1"><i class="bi bi-exclamation-triangle-fill"></i> Situasi Laporan Awal</p>
                <p class="text-slate-700 text-sm leading-relaxed">{{ $assignment['deskripsi_laporan'] }}</p>
            </div>
            @endif

            {{-- Peta Lokasi --}}
            @if($hasMap)
            <div class="mb-4">
                <p class="text-slate-500 font-bold text-xs uppercase mb-2"><i class="bi bi-map-fill text-indigo-500"></i> Peta Lokasi Kejadian</p>
                <div id="incident-map" class="w-full rounded-xl border border-slate-200"></div>
                <a href="https://maps.google.com/?q={{ $lat }},{{ $lng }}" target="_blank"
                   class="mt-2 text-xs font-semibold text-indigo-600 flex items-center gap-1 hover:underline">
                    <i class="bi bi-navigation-fill"></i> Buka di Google Maps / GPS
                </a>
            </div>
            @endif

            {{-- Kontak Pelapor --}}
            @if(!empty($assignment['hp_pelapor']))
            <div class="mb-4">
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $assignment['hp_pelapor']) }}" target="_blank"
                   class="flex items-center gap-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold py-2 px-4 rounded-xl border border-emerald-200 transition-colors text-sm">
                    <i class="bi bi-whatsapp text-lg"></i>
                    Chat Pelapor: {{ $assignment['nama_pelapor'] ?? '-' }} ({{ $assignment['hp_pelapor'] }})
                </a>
            </div>
            @endif

            {{-- Surat Tugas PDF --}}
            @if(!empty($assignment['surat_tugas_url']))
            <a href="{{ $assignment['surat_tugas_url'] }}" target="_blank"
               class="mb-3 flex items-center gap-2 justify-center bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 px-4 rounded-xl shadow-lg shadow-emerald-500/30 transition-all text-sm">
                <i class="bi bi-file-earmark-pdf-fill"></i> Lihat Surat Tugas PDF ({{ $assignment['nomor_surat_tugas'] ?? '-' }})
            </a>
            @endif


            {{-- Tombol Aksi Status --}}
            @if(in_array($status, ['assigned', 'ditugaskan', 'aktif', 'draft']))
            <form action="{{ route('dashboard.trc.mulai-penugasan') }}" method="POST">
                @csrf
                <button type="submit" class="btn-primary">
                    <i class="bi bi-play-circle-fill mr-1"></i> MULAI PENUGASAN (Berangkat)
                </button>
            </form>
            @elseif(in_array($status, ['on_route', 'on_site', 'aktif']))
            <a href="{{ route('dashboard.trc.assessment.create', $insidenId) }}"
               class="btn-primary block text-center mb-2">
                <i class="bi bi-clipboard-check-fill mr-1"></i> ISI ASSESSMENT LAPANGAN
            </a>
            @if(in_array($status, ['on_route']))
            <form action="{{ route('dashboard.trc.tiba-lokasi') }}" method="POST" class="mt-2">
                @csrf
                <button type="submit" class="w-full py-2 px-4 rounded-xl border-2 border-indigo-300 text-indigo-600 font-bold text-sm hover:bg-indigo-50 transition-colors">
                    <i class="bi bi-geo-fill"></i> Saya Sudah Tiba di Lokasi (On Site)
                </button>
            </form>
            @endif
            @endif

            @else
            {{-- Tidak ada penugasan --}}
            <div class="text-center py-6">
                <i class="bi bi-inbox text-4xl text-slate-300"></i>
                <p class="text-slate-400 font-semibold mt-2">Belum ada penugasan aktif</p>
                <p class="text-slate-400 text-sm">Tunggu penugasan dari koordinator.</p>
            </div>
            @endif
        </div>
    </div>

    {{-- ==================== RIWAYAT ASSESSMENT / SITREP ==================== --}}
    @if($hasAssign && $insidenId)
    <div class="mb-5">
        <div class="trc-card overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 bg-white/60 flex justify-between items-center">
                <span class="font-bold text-slate-700 flex items-center gap-2">
                    <i class="bi bi-journal-check text-indigo-500"></i> Riwayat Assessment & Sitrep
                </span>
                <span class="text-xs text-slate-400 font-semibold">{{ count($initialData['assessments'] ?? []) }} entri</span>
            </div>
            <div>
                @forelse($initialData['assessments'] ?? [] as $ass)
                <div class="sitrep-item">
                    <div>
                        <p class="font-semibold text-slate-700 text-sm">
                            Assessment #{{ $loop->iteration }} —
                            <span class="text-indigo-600">{{ $ass['jenis_laporan'] }}</span>
                        </p>
                        <p class="text-slate-400 text-xs">{{ $ass['waktu_assessment'] }} · {{ $ass['cakupan'] }}</p>
                        @if($ass['is_latest'])
                        <span class="text-[10px] font-bold uppercase bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-full">Terbaru</span>
                        @endif
                    </div>
                        <div class="flex gap-2 items-center">
                            @if($ass['sitrep_nomor'])
                            <span class="text-xs font-bold text-emerald-600 bg-emerald-50 border border-emerald-200 px-2 py-1 rounded-lg">
                                {{ $ass['sitrep_nomor'] }}
                            </span>
                            @endif
                            <a href="{{ route('insiden.assessment.show', [$insidenId, $ass['id']]) }}"
                               class="text-xs font-bold text-indigo-600 hover:underline flex items-center gap-1">
                                <i class="bi bi-eye"></i> Lihat
                            </a>
                        </div>
                </div>
                @empty
                <div class="text-center py-8">
                    <i class="bi bi-clipboard text-3xl text-slate-200"></i>
                    <p class="text-slate-400 text-sm font-semibold mt-2">Belum ada assessment.</p>
                    <p class="text-slate-400 text-xs">Isi assessment pertama setelah tiba di lokasi.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
    @endif

    {{-- ==================== KONTAK DARURAT ==================== --}}
    <div class="mb-8">
        <div class="trc-card overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 bg-white/60">
                <span class="font-bold text-slate-700 flex items-center gap-2">
                    <i class="bi bi-telephone-fill text-emerald-500"></i> Kontak Darurat
                </span>
            </div>
            <ul class="divide-y divide-slate-100" id="contacts-list">
                @foreach($initialData['contacts'] as $contact)
                <li class="px-5 py-4 flex justify-between items-center hover:bg-slate-50 transition-colors">
                    <div>
                        <h6 class="font-bold text-slate-700 text-sm">{{ $contact['role'] }}</h6>
                        <small class="text-slate-500">{{ $contact['name'] }}</small>
                    </div>
                    <a href="tel:{{ $contact['phone'] }}" class="w-10 h-10 flex items-center justify-center rounded-full bg-emerald-100 text-emerald-600 hover:bg-emerald-500 hover:text-white transition-colors shadow-sm">
                        <i class="bi bi-telephone"></i>
                    </a>
                </li>
                @endforeach
            </ul>
        </div>
    </div>


    @push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
    (function() {
        "use strict";

        // ========= LEAFLET MAP =========
        @if($hasMap)
        var mapLat = {!! json_encode((float) $lat) !!};
        var mapLng = {!! json_encode((float) $lng) !!};
        var mapTitle = {!! json_encode($assignment['title'] ?? 'Lokasi Kejadian') !!};

        function initMap() {
            var mapEl = document.getElementById('incident-map');
            if (!mapEl) return;

            var map = L.map('incident-map', { zoomControl: true, scrollWheelZoom: false })
                       .setView([mapLat, mapLng], 14);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            var marker = L.marker([mapLat, mapLng]).addTo(map);
            marker.bindPopup(
                '<div style="font-size:13px;font-weight:700;color:#e11d48;margin-bottom:4px">📍 Lokasi Kejadian</div>' +
                '<div style="font-size:12px;color:#475569">' + mapTitle + '</div>'
            ).openPopup();

            setTimeout(function() { map.invalidateSize(); }, 300);
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initMap);
        } else {
            initMap();
        }
        @endif

        // ========= AUTO-CLOSE FLASH =========
        var flash = document.getElementById('flash-success');
        if (flash) setTimeout(function() {
            flash.style.transition = 'opacity .5s';
            flash.style.opacity = '0';
            setTimeout(function() { flash.remove(); }, 500);
        }, 4000);

    })();
    </script>
    @endpush
</x-app-layout>
