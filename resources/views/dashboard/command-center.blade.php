<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>NURISK Command Center</title>

    {{-- Vite Tailwind --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Leaflet CSS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />

    <style>
        #peta { height: 100%; width: 100%; z-index: 10; }
        .jurnal-row { animation: fadeInLeft 0.4s ease; }
        @keyframes fadeInLeft {
            from { opacity: 0; transform: translateX(-10px); }
            to   { opacity: 1; transform: translateX(0); }
        }
        .live-dot { animation: blink 1.5s infinite; }
        @keyframes blink { 0%,100% { opacity:1 } 50% { opacity:0.3 } }
        
        /* Custom map dark mode */
        .leaflet-layer,
        .leaflet-control-zoom-in,
        .leaflet-control-zoom-out,
        .leaflet-control-attribution {
            filter: invert(100%) hue-rotate(180deg) brightness(95%) contrast(90%);
        }
        
        .priority-kritis { border-left: 4px solid #f43f5e; } /* rose-500 */
        .priority-tinggi  { border-left: 4px solid #f59e0b; } /* amber-500 */
        .priority-sedang  { border-left: 4px solid #eab308; } /* yellow-500 */
        .priority-rendah  { border-left: 4px solid #10b981; } /* emerald-500 */

        /* Glassmorphic Map Container */
        .glass-overlay {
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
    </style>
</head>
<body class="bg-slate-950 text-slate-200 h-screen overflow-hidden flex flex-col font-sans selection:bg-indigo-500/30">

{{-- ============================= --}}
{{-- HEADER BAR                    --}}
{{-- ============================= --}}
<header class="flex-none flex items-center justify-between px-6 py-3 glass-overlay z-50 shadow-2xl relative">
    <div class="absolute inset-0 bg-gradient-to-r from-indigo-500/10 to-rose-500/10 pointer-events-none"></div>
    <div class="flex items-center gap-4 relative z-10">
        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-rose-500 flex items-center justify-center shadow-lg shadow-indigo-500/30">
            <i class="bi bi-globe-americas text-white text-lg"></i>
        </div>
        <div class="flex flex-col">
            <span class="text-white font-bold text-lg tracking-wider leading-none">NURISK</span>
            <span class="text-indigo-400 text-xs font-semibold tracking-widest">COMMAND CENTER</span>
        </div>
        @if(Auth::user()->profil ?? false)
            <div class="h-6 w-px bg-slate-700 mx-2"></div>
            <span class="text-slate-400 text-sm font-medium"><i class="bi bi-person-badge mr-1"></i> {{ Auth::user()->profil->nama_lengkap }}</span>
        @endif
    </div>
    <div class="flex items-center gap-6 relative z-10">
        {{-- KPI Bar ringkasan --}}
        <div class="flex items-center gap-8 text-sm">
            <div class="flex flex-col items-center">
                <span class="text-white font-mono font-bold text-lg leading-none" id="kpi-insiden">{{ $kpi['total_insiden'] }}</span>
                <span class="text-[10px] text-slate-500 uppercase tracking-widest font-bold">Insiden</span>
            </div>
            <div class="flex flex-col items-center">
                <span class="text-amber-400 font-mono font-bold text-lg leading-none" id="kpi-personel">{{ $kpi['total_personel'] }}</span>
                <span class="text-[10px] text-slate-500 uppercase tracking-widest font-bold">Personel</span>
            </div>
            <div class="flex flex-col items-center">
                <span class="text-indigo-400 font-mono font-bold text-lg leading-none" id="kpi-posaju">{{ $kpi['total_posaju'] }}</span>
                <span class="text-[10px] text-slate-500 uppercase tracking-widest font-bold">Pos Aju</span>
            </div>
            <div class="flex flex-col items-center">
                <span class="text-rose-500 font-mono font-bold text-lg leading-none" id="kpi-stok-kritis">{{ $kpi['stok_kritis_count'] }}</span>
                <span class="text-[10px] text-slate-500 uppercase tracking-widest font-bold">Stok Kritis</span>
            </div>
        </div>
        
        <div class="h-8 w-px bg-slate-700"></div>

        {{-- Clock + Live indicator --}}
        <div class="flex flex-col items-end">
            <div class="flex items-center gap-2">
                <span class="live-dot text-emerald-500 text-[10px]">●</span>
                <span class="text-emerald-500 text-[10px] font-bold tracking-widest uppercase">LIVE SYSTEM</span>
            </div>
            <span class="text-slate-300 text-xs font-mono font-bold tracking-wider" id="clock">{{ now()->format('d M Y H:i:s') }}</span>
        </div>
    </div>
</header>

{{-- ============================= --}}
{{-- MAIN CONTENT AREA             --}}
{{-- ============================= --}}
<main class="flex-1 flex overflow-hidden relative">

    {{-- KIRI: PETA --}}
    <div class="flex-1 relative">
        <div id="peta"></div>

        {{-- Overlay Gradient Peta --}}
        <div class="absolute inset-0 pointer-events-none shadow-[inset_0_0_100px_rgba(15,23,42,0.8)] z-20"></div>

        {{-- Legend overlay --}}
        <div class="absolute bottom-6 left-6 z-[1000] glass-overlay rounded-xl px-4 py-3 text-xs space-y-2 shadow-2xl">
            <div class="text-slate-400 font-bold uppercase tracking-widest mb-3 border-b border-slate-700 pb-1">Legend</div>
            <div class="flex items-center gap-3"><span class="w-3 h-3 rounded-full bg-rose-500 inline-block shadow-[0_0_8px_rgba(244,63,94,0.6)]"></span> <span class="text-slate-200">Insiden (Respon)</span></div>
            <div class="flex items-center gap-3"><span class="w-3 h-3 rounded-full bg-amber-500 inline-block shadow-[0_0_8px_rgba(245,158,11,0.6)]"></span> <span class="text-slate-200">Insiden (Pemulihan)</span></div>
            <div class="flex items-center gap-3"><span class="w-3 h-3 rounded-sm bg-indigo-500 inline-block shadow-[0_0_8px_rgba(99,102,241,0.6)]"></span> <span class="text-slate-200">Pos Aju Aktif</span></div>
        </div>
    </div>

    {{-- KANAN: PANELS --}}
    <div class="w-96 flex-none glass-overlay border-l border-slate-800 flex flex-col overflow-hidden z-30 shadow-[-10px_0_30px_rgba(0,0,0,0.5)]">

        {{-- KPI Cards Besar --}}
        <div class="p-4 space-y-3 border-b border-slate-800/50 bg-slate-900/50">
            <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl p-4 border border-slate-700/50 shadow-inner flex justify-between items-center group hover:border-amber-500/30 transition-colors">
                <div>
                    <p class="text-[10px] text-slate-400 uppercase tracking-widest font-bold">Personel Tergerak</p>
                    <p class="text-3xl font-mono font-bold text-amber-400 mt-1 drop-shadow-[0_0_8px_rgba(251,191,36,0.3)]" id="kpi-card-personel">{{ $kpi['total_personel'] }}</p>
                </div>
                <div class="w-12 h-12 rounded-full bg-amber-500/10 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i class="bi bi-people-fill text-2xl text-amber-500"></i>
                </div>
            </div>
            <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl p-4 border border-slate-700/50 shadow-inner flex justify-between items-center group hover:border-rose-500/30 transition-colors">
                <div>
                    <p class="text-[10px] text-slate-400 uppercase tracking-widest font-bold">Korban Terdampak</p>
                    <p class="text-3xl font-mono font-bold text-rose-500 mt-1 drop-shadow-[0_0_8px_rgba(244,63,94,0.3)]" id="kpi-card-korban">–</p>
                </div>
                <div class="w-12 h-12 rounded-full bg-rose-500/10 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i class="bi bi-heart-pulse-fill text-2xl text-rose-500"></i>
                </div>
            </div>
        </div>

        {{-- Stok Kritis --}}
        <div class="flex-none p-4 border-b border-slate-800/50 bg-rose-950/10">
            <h3 class="text-[10px] text-rose-400 uppercase tracking-widest font-bold mb-3 flex items-center gap-2">
                <i class="bi bi-exclamation-triangle-fill"></i> Peringatan Stok Kritis
            </h3>
            <div id="list-stok-kritis" class="space-y-2 text-xs">
                <div class="text-slate-500 italic flex items-center justify-center py-2"><i class="bi bi-arrow-repeat animate-spin mr-2"></i> Memuat data logistik...</div>
            </div>
        </div>

        {{-- Insiden Aktif List --}}
        <div class="flex-1 overflow-y-auto p-4 custom-scrollbar">
            <h3 class="text-[10px] text-indigo-400 uppercase tracking-widest font-bold mb-3 flex items-center gap-2">
                <i class="bi bi-geo-alt-fill"></i> Insiden Aktif Hari Ini
            </h3>
            <div id="list-insiden" class="space-y-3">
                @forelse($insidenAktif as $insiden)
                <div class="bg-slate-800/50 rounded-xl p-3 priority-{{ $insiden->prioritas }} border border-slate-700/50
                             cursor-pointer hover:bg-slate-700 hover:border-indigo-500/30 hover:shadow-[0_0_15px_rgba(99,102,241,0.1)] transition-all transform hover:-translate-y-0.5"
                     onclick="focusInsiden({{ $insiden->id_insiden }})">
                    <div class="flex justify-between items-start mb-1">
                        <p class="font-mono text-slate-200 font-bold text-sm">{{ $insiden->kode_kejadian }}</p>
                        <span @class([
                            'text-[10px] px-2 py-0.5 rounded-md font-bold uppercase tracking-wider',
                            'bg-rose-500/20 text-rose-400 border border-rose-500/30' => $insiden->status_insiden === 'respon',
                            'bg-amber-500/20 text-amber-400 border border-amber-500/30' => $insiden->status_insiden === 'pemulihan',
                        ])>{{ ucfirst($insiden->status_insiden) }}</span>
                    </div>
                    <p class="text-slate-400 text-xs font-medium mb-2">{{ $insiden->jenisBencana?->nama_bencana ?? 'Bencana Tidak Diketahui' }}</p>
                    <div class="flex items-center gap-2 text-[10px] text-slate-500">
                        <i class="bi bi-building"></i> <span>{{ $insiden->pcnu?->nama_pcnu ?? 'Wilayah N/A' }}</span>
                    </div>
                </div>
                @empty
                <div class="text-center py-8 text-slate-500 text-xs">
                    <i class="bi bi-shield-check text-3xl block mb-2 text-emerald-500/50"></i>
                    Tidak ada insiden aktif saat ini.
                </div>
                @endforelse
            </div>
        </div>
    </div>

</main>

{{-- ============================= --}}
{{-- FOOTER: JURNAL TICKER         --}}
{{-- ============================= --}}
<footer class="flex-none glass-overlay border-t border-slate-800 px-6 py-3 z-50">
    <div class="flex items-center gap-4">
        <div class="flex items-center gap-2 bg-slate-800 px-3 py-1 rounded-md border border-slate-700 shrink-0 shadow-inner">
            <span class="live-dot text-indigo-400 text-[10px]">●</span>
            <span class="text-[10px] text-indigo-400 uppercase tracking-widest font-bold">JURNAL LIVE</span>
        </div>
        <div id="jurnal-ticker" class="flex gap-8 overflow-hidden text-xs text-slate-300 w-full">
            <span class="text-slate-500 italic animate-pulse">Menghubungkan ke aliran jurnal...</span>
        </div>
    </div>
</footer>

<style>
    /* Custom Scrollbar for dark theme */
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: rgba(15, 23, 42, 0.5);
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: rgba(71, 85, 105, 0.8);
        border-radius: 10px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: rgba(99, 102, 241, 0.8);
    }
</style>

{{-- Leaflet JS --}}
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>

<script>
// ==================== INISIALISASI PETA ====================
const peta = L.map('peta', {
    center: [-6.9667, 110.4167],  // Semarang, Jawa Tengah
    zoom: 10,
    zoomControl: false, // Disembunyikan, bisa ditambahkan custom jika perlu
    attributionControl: false,
});

L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
    maxZoom: 19,
}).addTo(peta);

const clusterGroup = L.markerClusterGroup({
    maxClusterRadius: 50,
    disableClusteringAtZoom: 14,
    spiderfyOnMaxZoom: true,
    showCoverageOnHover: false,
});
peta.addLayer(clusterGroup);

const markerData = {}; // id_insiden → L.Marker
const posajuGroup = L.layerGroup().addTo(peta);

// Icons
function buatIcon(status, prioritas) {
    const colors = {
        respon: '#f43f5e', // rose-500
        pemulihan: '#f59e0b', // amber-500
    };
    const sizes = { kritis: 22, tinggi: 18, sedang: 14, rendah: 12 };
    const color = colors[status] || '#64748b'; // slate-500
    const size  = sizes[prioritas] || 16;
    return L.divIcon({
        className: 'custom-map-icon',
        html: `<div style="width:${size}px;height:${size}px;border-radius:50%;background:${color};
                           border:2px solid rgba(255,255,255,0.8);box-shadow:0 0 15px ${color};"></div>`,
        iconSize: [size, size],
        iconAnchor: [size/2, size/2],
    });
}

function posajuIcon() {
    return L.divIcon({
        className: 'custom-posaju-icon',
        html: `<div style="width:12px;height:12px;background:#6366f1;
                           border:2px solid rgba(255,255,255,0.8);box-shadow:0 0 10px #6366f1; border-radius:3px; transform: rotate(45deg);"></div>`,
        iconSize: [12, 12],
        iconAnchor: [6, 6],
    });
}

function updateMap(insidenList) {
    const idsServer = new Set(insidenList.map(i => i.id));
    const idsLokal  = new Set(Object.keys(markerData).map(Number));

    // Hapus marker yang sudah tidak aktif
    idsLokal.forEach(id => {
        if (!idsServer.has(id)) {
            clusterGroup.removeLayer(markerData[id]);
            delete markerData[id];
        }
    });

    posajuGroup.clearLayers();

    insidenList.forEach(insiden => {
        if (!insiden.has_koordinat) return;

        const popup = `
            <div class="font-sans">
                <strong class="font-mono text-indigo-600 block mb-1 text-sm border-b border-slate-100 pb-1">${insiden.kode}</strong>
                <span class="text-slate-700 font-bold text-xs">${insiden.jenis ?? ''}</span><br>
                <div class="mt-2 flex gap-2">
                    <span class="text-[10px] px-2 py-1 bg-slate-100 rounded border font-bold uppercase" style="color:${insiden.status === 'respon' ? '#f43f5e' : '#f59e0b'}">
                        ${insiden.status?.toUpperCase()}
                    </span>
                    ${insiden.prioritas ? '<span class="text-[10px] px-2 py-1 bg-slate-100 text-slate-500 rounded border font-bold uppercase">' + insiden.prioritas.toUpperCase() + '</span>' : ''}
                </div>
                <small class="text-slate-500 block mt-2 pt-2 border-t border-slate-100"><i class="bi bi-building mr-1"></i> ${insiden.pcnu ?? ''}</small>
            </div>
        `;

        if (!markerData[insiden.id]) {
            const marker = L.marker([insiden.lat, insiden.lng], { icon: buatIcon(insiden.status, insiden.prioritas) })
                .bindPopup(popup, { className: 'custom-popup' });
            clusterGroup.addLayer(marker);
            markerData[insiden.id] = marker;
        } else {
            markerData[insiden.id].setPopupContent(popup);
        }

        // Tambah marker pos aju
        (insiden.posaju || []).forEach(p => {
            if (p.lat && p.lng) {
                L.marker([p.lat, p.lng], { icon: posajuIcon() })
                    .bindPopup(`<div class="font-bold text-indigo-700 border-b border-slate-100 pb-1 mb-1"><i class="bi bi-geo-alt-fill mr-1"></i> POS AJU</div><span class="text-slate-600 text-xs">${p.nama}</span>`)
                    .addTo(posajuGroup);
            }
        });
    });
}

function focusInsiden(idInsiden) {
    const marker = markerData[idInsiden];
    if (marker) {
        peta.setView(marker.getLatLng(), 15, { animate: true, duration: 1.5 });
        setTimeout(() => marker.openPopup(), 1500);
    }
}

// ==================== KPI UPDATE ====================
function updateKpi(data) {
    document.getElementById('kpi-insiden').textContent  = data.total_insiden ?? '–';
    document.getElementById('kpi-personel').textContent = data.total_personel ?? '–';
    document.getElementById('kpi-posaju').textContent   = data.total_posaju ?? '–';
    document.getElementById('kpi-card-personel').textContent = data.total_personel ?? '–';
    document.getElementById('kpi-card-korban').textContent   = data.korban_terdampak ?? '–';
}

// ==================== STOK KRITIS ====================
function updateStokKritis(data) {
    const el = document.getElementById('list-stok-kritis');
    if (!data?.length) {
        el.innerHTML = '<div class="p-3 bg-emerald-500/10 border border-emerald-500/20 rounded-lg text-emerald-400 text-xs flex items-center justify-center gap-2"><i class="bi bi-check-circle-fill"></i> Semua stok aman</div>';
        document.getElementById('kpi-stok-kritis').textContent = '0';
        return;
    }
    document.getElementById('kpi-stok-kritis').textContent = data.length;
    el.innerHTML = data.slice(0, 5).map(s =>
        `<div class="flex justify-between items-center bg-rose-500/10 border border-rose-500/20 rounded-lg px-3 py-2 text-rose-300">
            <span class="font-semibold truncate pr-2">${s.nama_barang}</span>
            <span class="font-mono bg-rose-950/50 px-2 py-0.5 rounded text-[10px] font-bold border border-rose-500/30 whitespace-nowrap">${s.tersedia} ${s.satuan}</span>
        </div>`
    ).join('');
}

// ==================== JURNAL TICKER ====================
let jurnalIndex = 0;
function updateJurnal(data) {
    if (!data?.length) return;
    const el = document.getElementById('jurnal-ticker');
    el.innerHTML = data.map(j =>
        `<span class="jurnal-row shrink-0 flex items-center">
            <span class="text-indigo-400 font-mono text-[10px] bg-slate-800 px-1.5 py-0.5 rounded mr-2">${j.waktu}</span>
            <span class="text-emerald-400 font-bold uppercase tracking-wider text-[10px] mr-2">[${j.kategori}]</span>
            <span class="text-slate-200 font-medium">${j.judul}</span>
            <span class="text-slate-500 text-[10px] ml-2 italic">— ${j.oleh}</span>
        </span>`
    ).join('<span class="text-slate-700 mx-4">•</span>');
}

// ==================== POLLING ====================
async function fetchAll() {
    try {
        const [insidenRes, statRes, stokRes, jurnalRes] = await Promise.allSettled([
            fetch('/api/command-center/insiden-aktif', { headers: { 'Accept': 'application/json' } }),
            fetch('/api/command-center/statistik', { headers: { 'Accept': 'application/json' } }),
            fetch('/api/command-center/stok-kritis', { headers: { 'Accept': 'application/json' } }),
            fetch('/api/command-center/jurnal-terbaru', { headers: { 'Accept': 'application/json' } }),
        ]);

        if (insidenRes.status === 'fulfilled' && insidenRes.value.ok) {
            const data = await insidenRes.value.json();
            updateMap(data.data ?? []);
        }
        if (statRes.status === 'fulfilled' && statRes.value.ok) {
            const data = await statRes.value.json();
            updateKpi(data);
        }
        if (stokRes.status === 'fulfilled' && stokRes.value.ok) {
            const data = await stokRes.value.json();
            updateStokKritis(data.data ?? []);
        }
        if (jurnalRes.status === 'fulfilled' && jurnalRes.value.ok) {
            const data = await jurnalRes.value.json();
            updateJurnal(data.data ?? []);
        }

        if (insidenRes.status === 'rejected' || !insidenRes.value.ok) {
            throw new Error('Jaringan terputus atau server tidak merespons');
        }
    } catch (e) {
        console.warn('Command center polling error:', e.message);
        throw e;
    }
}

// ==================== INISIALISASI ====================
@php
$insidenAwalData = $insidenAktif->map(fn($i) => [
    'id'           => $i->id_insiden,
    'kode'         => $i->kode_kejadian,
    'status'       => $i->status_insiden,
    'prioritas'    => $i->prioritas,
    'jenis'        => $i->jenisBencana?->nama_bencana,
    'lat'          => $i->laporanAsal?->latitude ?? $i->posaju->first()?->latitude,
    'lng'          => $i->laporanAsal?->longitude ?? $i->posaju->first()?->longitude,
    'has_koordinat' => ($i->laporanAsal?->latitude !== null) || ($i->posaju->first()?->latitude !== null),
    'posaju'       => $i->posaju->map(fn($p) => ['nama' => $p->nama_posaju, 'lat' => $p->latitude, 'lng' => $p->longitude]),
    'pcnu'         => $i->pcnu?->nama_pcnu,
])->values()->toArray();
@endphp
const insidenAwal = @json($insidenAwalData);
updateMap(insidenAwal);

// Update jam setiap detik
setInterval(() => {
    document.getElementById('clock').textContent = new Date().toLocaleString('id-ID', {
        day: '2-digit', month: 'short', year: 'numeric',
        hour: '2-digit', minute: '2-digit', second: '2-digit',
    });
}, 1000);

// ==================== POLLING (EXPONENTIAL BACKOFF) ====================
let currentInterval = 30000;
const MAX_INTERVAL = 300000;

async function runPollingWithBackoff() {
    try {
        await fetchAll();
        currentInterval = 30000;
    } catch (e) {
        currentInterval = Math.min(currentInterval * 2, MAX_INTERVAL);
        console.warn(`Polling gagal. Mencoba lagi dalam ${currentInterval/1000} detik.`);
    } finally {
        setTimeout(runPollingWithBackoff, currentInterval);
    }
}

setTimeout(runPollingWithBackoff, currentInterval);

// Tambahkan CSS custom dinamis untuk Leaflet Popup agar sesuai dengan tema
const style = document.createElement('style');
style.innerHTML = `
    .leaflet-popup-content-wrapper {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(8px);
        border-radius: 12px;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5);
    }
    .leaflet-popup-tip {
        background: rgba(255, 255, 255, 0.95);
    }
`;
document.head.appendChild(style);
</script>

</body>
</html>
