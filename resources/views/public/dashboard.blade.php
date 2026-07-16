@extends('layouts.public')

@section('title', 'NURISK — Dashboard Publik')
@section('nav-home', 'active')

@push('styles')
<style>
    .kpi-row {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 14px;
        margin-bottom: 24px;
    }
    .kpi-card {
        background: rgba(255,255,255,0.75);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border-radius: 16px;
        padding: 18px 20px;
        display: flex;
        align-items: center;
        gap: 14px;
        border: 1px solid rgba(255,255,255,0.5);
        box-shadow: 0 2px 16px rgba(0,0,0,0.04);
        cursor: pointer;
        transition: all 0.2s;
    }
    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 32px rgba(0,0,0,0.08);
        border-color: rgba(21,115,71,0.2);
    }
    .kpi-card:active { transform: translateY(0); }
    .kpi-icon {
        width: 44px; height: 44px;
        border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        font-size: 18px; color: #fff;
        flex-shrink: 0;
    }
    .kpi-icon.green { background: linear-gradient(135deg, #157347, #0f5c38); }
    .kpi-icon.orange { background: linear-gradient(135deg, #e67e22, #d35400); }
    .kpi-icon.red { background: linear-gradient(135deg, #e74c3c, #c0392b); }
    .kpi-body { min-width: 0; }
    .kpi-value { font-size: 26px; font-weight: 800; color: #1a1a2e; line-height: 1.1; }
    .kpi-label { font-size: 11px; color: #888; font-weight: 500; text-transform: uppercase; letter-spacing: 0.3px; margin-top: 2px; }

    .weather-strip {
        background: rgba(255,255,255,0.70);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border-radius: 16px;
        padding: 14px 18px;
        border: 1px solid rgba(255,255,255,0.5);
        box-shadow: 0 2px 16px rgba(0,0,0,0.04);
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        gap: 16px;
    }
    .weather-now {
        display: flex; align-items: center; gap: 10px;
        flex-shrink: 0; padding-right: 16px;
        border-right: 1px solid rgba(0,0,0,0.06);
    }
    .weather-now .wn-temp { font-size: 28px; font-weight: 800; color: #1a1a2e; line-height: 1; }
    .weather-now .wn-icon { font-size: 26px; color: #157347; }
    .weather-now .wn-city { font-size: 10px; color: #999; margin-top: 1px; }
    .weather-scroll {
        display: flex; gap: 6px; overflow-x: auto; flex: 1;
        padding: 2px 0; scrollbar-width: none;
    }
    .weather-scroll::-webkit-scrollbar { display: none; }
    .wf-day {
        display: flex; align-items: center; gap: 4px;
        flex-shrink: 0; margin: 0 2px;
    }
    .wf-day-label {
        font-size: 9px; font-weight: 700; color: #1a1a2e;
        writing-mode: vertical-lr; text-orientation: mixed;
        padding: 4px 2px; letter-spacing: 1px;
        opacity: 0.5; min-width: 14px; text-align: center;
    }
    .wf-day-items { display: flex; gap: 4px; }
    .wf-item {
        flex-shrink: 0; min-width: 48px;
        text-align: center; padding: 4px 4px;
        border-radius: 8px;
        transition: background 0.2s;
    }
    .wf-item:hover { background: rgba(0,0,0,0.03); }
    .wf-item .wf-label { font-size: 7px; color: #aaa; text-transform: uppercase; letter-spacing: 0.2px; }
    .wf-item .wf-icon { font-size: 14px; color: #555; margin: 1px 0; }
    .wf-item .wf-temp { font-size: 11px; font-weight: 700; color: #1a1a2e; }
    .wf-item .wf-cond { font-size: 6px; color: #aaa; }

    .early-warning { margin-bottom: 12px; }
    .ew-bar {
        display: flex; align-items: center; gap: 10px;
        padding: 10px 16px; border-radius: 12px;
        font-size: 13px; font-weight: 600;
        cursor: pointer; transition: all 0.2s;
    }
    .ew-bar:hover { transform: translateY(-1px); }
    .ew-bar.ew-low { background: #e8f5e9; color: #2e7d32; }
    .ew-bar.ew-medium { background: #fff8e1; color: #f57f17; }
    .ew-bar.ew-high { background: #fbe9e7; color: #d84315; }
    .ew-bar.ew-critical { background: #ffebee; color: #c62828; animation: ew-pulse 1.5s ease-in-out infinite; }
    @keyframes ew-pulse { 0%,100% { opacity:1; } 50% { opacity:0.7; } }
    .ew-icon { font-size: 16px; }
    .risk-detail { display: none; margin-top: 8px; padding: 10px 16px; background: rgba(0,0,0,0.03); border-radius: 8px; font-size: 11px; font-weight: 400; line-height: 1.5; }

    .section-label {
        font-size: 13px; font-weight: 600; color: #666;
        margin-bottom: 10px; display: flex; align-items: center; gap: 6px;
        text-transform: uppercase; letter-spacing: 0.5px;
    }

    .incard-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
        margin-bottom: 24px;
    }
    .incard {
        background: rgba(255,255,255,0.75);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border-radius: 14px;
        padding: 16px;
        border: 1px solid rgba(255,255,255,0.5);
        box-shadow: 0 2px 16px rgba(0,0,0,0.03);
        text-decoration: none;
        transition: all 0.2s;
        cursor: pointer;
    }
    .incard:hover {
        transform: translateY(-2px);
        border-color: rgba(21,115,71,0.2);
        box-shadow: 0 8px 32px rgba(0,0,0,0.06);
    }
    .incard-top { display: flex; align-items: center; justify-content: space-between; gap: 6px; margin-bottom: 6px; }
    .incard-type { font-size: 12px; font-weight: 700; color: #1a1a2e; text-transform: uppercase; }
    .incard-badge {
        font-size: 8px; font-weight: 700; padding: 2px 7px;
        border-radius: 5px; text-transform: uppercase; flex-shrink: 0;
    }
    .incard-badge.respon { background: #fde8e8; color: #dc2626; }
    .incard-badge.pemulihan { background: #fef3c7; color: #d97706; }
    .incard-badge.terverifikasi { background: #dbeafe; color: #2563eb; }
    .incard-badge.default { background: #f3f4f6; color: #6b7280; }
    .incard-loc { font-size: 10px; color: #999; display: flex; align-items: center; gap: 3px; margin-bottom: 6px; }
    .incard-loc i { font-size: 9px; }
    .incard-desc { font-size: 11px; color: #777; line-height: 1.5; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .incard-date { font-size: 9px; color: #bbb; margin-top: 8px; display: flex; align-items: center; gap: 3px; }
    .incard-empty {
        grid-column: 1 / -1; text-align: center; padding: 32px 0;
        color: #ccc; font-size: 13px;
    }

    .bottom-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        margin-bottom: 24px;
    }
    .qrcard {
        background: rgba(255,255,255,0.75);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border-radius: 16px;
        padding: 18px 20px;
        border: 1px solid rgba(255,255,255,0.5);
        box-shadow: 0 2px 16px rgba(0,0,0,0.03);
        display: flex; align-items: center; gap: 16px;
    }
    .qrcard-img {
        width: 80px; height: 80px; flex-shrink: 0;
        border-radius: 12px; overflow: hidden;
        background: #f8f9fa;
    }
    .qrcard-img img { width: 100%; height: 100%; object-fit: contain; }
    .qrcard-body h4 { font-size: 13px; font-weight: 700; color: #1a1a2e; margin-bottom: 3px; }
    .qrcard-body p { font-size: 11px; color: #999; line-height: 1.5; }
    .qrcard-btn {
        display: inline-block; margin-top: 8px;
        font-size: 11px; font-weight: 600; padding: 5px 14px;
        background: var(--nu-green); color: #fff; text-decoration: none;
        border-radius: 8px; transition: all 0.2s;
    }
    .qrcard-btn:hover { background: var(--nu-green-dark); }

    .shortcuts {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }
    .shortcut {
        background: rgba(255,255,255,0.75);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border-radius: 14px;
        padding: 14px;
        text-decoration: none;
        border: 1px solid rgba(255,255,255,0.5);
        box-shadow: 0 2px 16px rgba(0,0,0,0.03);
        transition: all 0.2s;
        display: flex; align-items: center; gap: 12px;
    }
    .shortcut:hover {
        border-color: rgba(21,115,71,0.2);
        transform: translateY(-2px);
        box-shadow: 0 8px 32px rgba(0,0,0,0.06);
    }
    .shortcut .sc-icon {
        width: 36px; height: 36px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 16px; color: #fff; flex-shrink: 0;
    }
    .shortcut .sc-icon.green { background: linear-gradient(135deg, #157347, #0f5c38); }
    .shortcut .sc-icon.blue { background: linear-gradient(135deg, #3b82f6, #1d4ed8); }
    .shortcut .sc-icon.orange { background: linear-gradient(135deg, #f59e0b, #d97706); }
    .shortcut .sc-icon.purple { background: linear-gradient(135deg, #8b5cf6, #6d28d9); }
    .shortcut .sc-icon.red { background: linear-gradient(135deg, #ef4444, #dc2626); }
    .shortcut .sc-body { min-width: 0; }
    .shortcut .sc-title { font-size: 12px; font-weight: 600; color: #1a1a2e; }
    .shortcut .sc-desc { font-size: 9px; color: #aaa; margin-top: 1px; }

    /* Overlay drawer */
    .overlay {
        position: fixed; inset: 0; z-index: 9998;
        background: rgba(0,0,0,0.3);
        backdrop-filter: blur(4px);
        -webkit-backdrop-filter: blur(4px);
        opacity: 0; visibility: hidden;
        transition: all 0.3s;
    }
    .overlay.open { opacity: 1; visibility: visible; }
    .drawer {
        position: fixed; bottom: 0; left: 0; right: 0; z-index: 9999;
        max-height: 80vh;
        background: rgba(255,255,255,0.88);
        backdrop-filter: blur(24px) saturate(200%);
        -webkit-backdrop-filter: blur(24px) saturate(200%);
        border-radius: 20px 20px 0 0;
        box-shadow: 0 -8px 40px rgba(0,0,0,0.12);
        transform: translateY(100%);
        transition: transform 0.35s cubic-bezier(0.32,0.72,0,1);
        overflow: hidden;
        display: flex; flex-direction: column;
    }
    .drawer.open { transform: translateY(0); }
    .drawer-handle {
        width: 36px; height: 4px; border-radius: 2px;
        background: rgba(0,0,0,0.15);
        margin: 10px auto 6px; flex-shrink: 0;
    }
    .drawer-header {
        display: flex; align-items: center; justify-content: space-between;
        padding: 6px 20px 12px; flex-shrink: 0;
    }
    .drawer-title { font-size: 16px; font-weight: 700; color: #1a1a2e; }
    .drawer-close {
        width: 32px; height: 32px; border-radius: 10px;
        border: none; background: rgba(0,0,0,0.05);
        cursor: pointer; display: flex; align-items: center; justify-content: center;
        color: #888; font-size: 16px; transition: all 0.2s;
    }
    .drawer-close:hover { background: rgba(0,0,0,0.1); color: #333; }
    .drawer-body {
        flex: 1; overflow-y: auto; padding: 0 20px 20px;
        scrollbar-width: thin;
    }
    .drawer-body::-webkit-scrollbar { width: 4px; }
    .drawer-body::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.1); border-radius: 2px; }

    .dlist-item {
        background: rgba(255,255,255,0.6);
        border-radius: 12px; padding: 14px 16px; margin-bottom: 8px;
        border: 1px solid rgba(0,0,0,0.04);
        cursor: pointer; transition: all 0.2s;
    }
    .dlist-item:hover { border-color: rgba(21,115,71,0.15); background: rgba(255,255,255,0.9); }
    .dlist-item:active { transform: scale(0.98); }
    .dlist-top { display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 4px; }
    .dlist-title { font-size: 13px; font-weight: 700; color: #1a1a2e; }
    .dlist-meta { font-size: 10px; color: #999; display: flex; align-items: center; gap: 10px; }
    .dlist-arrow { color: #ccc; font-size: 12px; transition: transform 0.2s; }
    .dlist-arrow.open { transform: rotate(180deg); }

    /* Summary detail panel */
    .detail-panel {
        background: rgba(0,0,0,0.02);
        border-radius: 12px; padding: 16px;
        margin-top: 8px; margin-bottom: 8px;
        border: 1px solid rgba(0,0,0,0.04);
        display: none;
    }
    .detail-panel.open { display: block; }
    .detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
    .detail-stat {
        background: #fff; border-radius: 10px; padding: 10px 12px;
        text-align: center; border: 1px solid #f0f0f0;
    }
    .detail-stat .ds-value { font-size: 18px; font-weight: 800; color: #1a1a2e; }
    .detail-stat .ds-label { font-size: 8px; color: #999; text-transform: uppercase; letter-spacing: 0.3px; margin-top: 1px; }
    .detail-desc {
        font-size: 12px; color: #555; line-height: 1.6;
        background: #fff; border-radius: 10px; padding: 12px;
        margin: 8px 0; border: 1px solid #f0f0f0;
    }
    .detail-needs {
        margin-top: 8px;
    }
    .detail-needs-item {
        display: flex; align-items: center; justify-content: space-between;
        padding: 6px 10px; background: rgba(245,158,11,0.06);
        border-radius: 8px; margin-bottom: 4px;
        font-size: 11px;
    }
    .detail-needs-item .dn-name { font-weight: 600; color: #333; }
    .detail-needs-item .dn-gap { font-weight: 700; color: #d97706; }
    .detail-loading {
        text-align: center; padding: 24px; color: #999; font-size: 13px;
    }

    @media (max-width: 900px) {
        .kpi-row { gap: 10px; }
        .kpi-card { padding: 14px 16px; }
        .kpi-icon { width: 38px; height: 38px; font-size: 15px; }
        .kpi-value { font-size: 22px; }
        .incard-grid { grid-template-columns: repeat(2, 1fr); }
        .bottom-row { grid-template-columns: 1fr; }
        .weather-now .wn-temp { font-size: 22px; }
    }
    @media (max-width: 600px) {
        .kpi-row { grid-template-columns: 1fr; gap: 8px; }
        .kpi-card { padding: 12px 14px; }
        .weather-strip { flex-direction: column; align-items: stretch; gap: 10px; }
        .weather-now { border-right: none; border-bottom: 1px solid rgba(0,0,0,0.06); padding-right: 0; padding-bottom: 8px; }
        .incard-grid { grid-template-columns: 1fr; }
        .qrcard { flex-direction: column; text-align: center; }
        .shortcuts { grid-template-columns: 1fr; }
        .bottom-row { gap: 12px; }
    }
</style>
@endpush

@section('content')
<div class="page-container">

    {{-- KPI --}}
    <div class="kpi-row" id="kpiRow">
        <div class="kpi-card" data-filter="all" onclick="openDrawer('all')">
            <div class="kpi-icon red"><i class="fa-solid fa-triangle-exclamation"></i></div>
            <div class="kpi-body">
                <div class="kpi-value" id="kpiInsiden">0</div>
                <div class="kpi-label">Kejadian Aktif</div>
            </div>
        </div>
        <div class="kpi-card" data-filter="needs" onclick="openDrawer('needs')">
            <div class="kpi-icon orange"><i class="fa-solid fa-hand-holding-heart"></i></div>
            <div class="kpi-body">
                <div class="kpi-value" id="kpiGap">0</div>
                <div class="kpi-label">Gap Kebutuhan</div>
            </div>
        </div>
        <div class="kpi-card" data-filter="casualties" onclick="openDrawer('casualties')">
            <div class="kpi-icon green"><i class="fa-solid fa-users"></i></div>
            <div class="kpi-body">
                <div class="kpi-value" id="kpiKorban">0</div>
                <div class="kpi-label">Korban Terdampak</div>
            </div>
        </div>
    </div>

    {{-- Early Warning --}}
    <div class="early-warning" id="earlyWarning" style="display:none;">
        <div class="ew-bar" id="ewBar">
            <span class="ew-icon"><i class="fa-solid fa-triangle-exclamation"></i></span>
            <span class="ew-text" id="ewText">Memuat peringatan...</span>
        </div>
    </div>

    {{-- Weather --}}
    <div class="weather-strip" id="weatherStrip">
        <div class="weather-now" id="weatherNow">
            <div class="wn-icon"><i class="fa-solid fa-spinner fa-spin"></i></div>
            <div>
                <div class="wn-temp">--&deg;</div>
                <div class="wn-city" id="weatherCity">Jawa Tengah</div>
            </div>
        </div>
        <div class="weather-scroll" id="weatherScroll">
            <div style="color:#ccc;font-size:11px;padding:8px;">Memuat...</div>
        </div>
    </div>

    {{-- Latest Incidents --}}
    <div class="section-label"><i class="fa-regular fa-clock"></i> Kejadian Terbaru</div>
    <div class="incard-grid" id="incidentCards">
        <div class="incard-empty"><i class="fa-solid fa-spinner fa-spin"></i> Memuat...</div>
    </div>

    {{-- Bottom Row --}}
    <div class="bottom-row">
        <div class="qrcard">
            <div class="qrcard-img">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=https://laziznu.org/donasi-bencana" alt="QR" loading="lazy">
            </div>
            <div class="qrcard-body">
                <h4><i class="fa-solid fa-hand-holding-heart" style="color:var(--nu-green);"></i> Bantuan Bencana</h4>
                <p>Donasi kemanusiaan melalui Laziznu NU Peduli.</p>
                <a href="https://laziznu.org/donasi-bencana" target="_blank" rel="noopener" class="qrcard-btn">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i> Donasi
                </a>
            </div>
        </div>

        <div class="shortcuts">
            <a href="{{ route('public.map') }}" class="shortcut">
                <div class="sc-icon green"><i class="fa-solid fa-map"></i></div>
                <div class="sc-body">
                    <div class="sc-title">Peta Bencana</div>
                    <div class="sc-desc">Lihat sebaran & status</div>
                </div>
            </a>
            <a href="{{ route('public.lapor') }}" class="shortcut">
                <div class="sc-icon red"><i class="fa-solid fa-bullhorn"></i></div>
                <div class="sc-body">
                    <div class="sc-title">Lapor Kejadian</div>
                    <div class="sc-desc">Kirim laporan</div>
                </div>
            </a>
            <a href="{{ route('public.resource') }}" class="shortcut">
                <div class="sc-icon blue"><i class="fa-solid fa-cubes"></i></div>
                <div class="sc-body">
                    <div class="sc-title">Resource</div>
                    <div class="sc-desc">Posko & layanan</div>
                </div>
            </a>
            <a href="{{ route('register') }}" class="shortcut">
                <div class="sc-icon purple"><i class="fa-solid fa-user-plus"></i></div>
                <div class="sc-body">
                    <div class="sc-title">Daftar Relawan</div>
                    <div class="sc-desc">Gabung tim darurat</div>
                </div>
            </a>
        </div>
    </div>

</div>

{{-- Overlay + Drawer --}}
<div class="overlay" id="overlay" onclick="closeDrawer()"></div>
<div class="drawer" id="drawer">
    <div class="drawer-handle"></div>
    <div class="drawer-header">
        <div class="drawer-title" id="drawerTitle">Daftar Kejadian</div>
        <button class="drawer-close" onclick="closeDrawer()"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="drawer-body" id="drawerBody"></div>
</div>
@endsection

@push('scripts')
<script>
// ===== Weather icons FA map =====
var WEATHER_ICONS = {
    '01d': 'fa-sun', '01n': 'fa-moon',
    '02d': 'fa-cloud-sun', '02n': 'fa-cloud-moon',
    '03d': 'fa-cloud', '03n': 'fa-cloud',
    '04d': 'fa-cloud', '04n': 'fa-cloud',
    '09d': 'fa-cloud-rain', '09n': 'fa-cloud-rain',
    '10d': 'fa-cloud-showers-heavy', '10n': 'fa-cloud-showers-heavy',
    '11d': 'fa-cloud-bolt', '11n': 'fa-cloud-bolt',
    '13d': 'fa-snowflake', '13n': 'fa-snowflake',
    '50d': 'fa-smog', '50n': 'fa-smog',
};
function weatherIcon(code) { return WEATHER_ICONS[code] || 'fa-cloud'; }

// ===== Data store =====
var dashboardData = null;
var incidentDetailCache = {};

// ===== KPI + Incidents fetch =====
fetch('/api/public/dashboard')
    .then(function (r) { return r.json(); })
    .then(function (data) {
        dashboardData = data;
        document.getElementById('kpiInsiden').textContent = data.kpi.total_insiden ?? 0;
        document.getElementById('kpiGap').textContent = data.kpi.kebutuhan_gap ?? 0;
        document.getElementById('kpiKorban').textContent = data.kpi.korban_terdampak ?? 0;

        var list = document.getElementById('incidentCards');
        if (!data.insiden || data.insiden.length === 0) {
            list.innerHTML = '<div class="incard-empty">✅ Tidak ada insiden aktif.</div>';
            return;
        }
        if (data.insiden && data.insiden.length > 0) {
            var top6 = data.insiden.slice(0, 6);
            list.innerHTML = '';
            top6.forEach(function (item) {
                list.appendChild(incard(item));
            });
        }
    })
    .catch(function () {
        document.getElementById('kpiInsiden').textContent = '—';
        document.getElementById('kpiGap').textContent = '—';
        document.getElementById('kpiKorban').textContent = '—';
        document.getElementById('incidentCards').innerHTML = '<div class="incard-empty">⚠️ Gagal memuat data.</div>';
    });

function incard(item) {
    var badgeClass = item.status === 'respon' ? 'respon' :
                     item.status === 'pemulihan' ? 'pemulihan' :
                     item.status === 'terverifikasi' ? 'terverifikasi' : 'default';
    var date = item.waktu_mulai ? new Date(item.waktu_mulai).toLocaleDateString('id-ID', {
        day: 'numeric', month: 'short', year: 'numeric'
    }) : '—';
    var desc = item.description || 'Sedang dalam penanganan.';
    var card = document.createElement('div');
    card.className = 'incard';
    card.onclick = function () { openDrawer('all', item.id); };
    card.innerHTML =
        '<div class="incard-top">' +
            '<span class="incard-type">' + (item.jenis || 'Kejadian') + '</span>' +
            '<span class="incard-badge ' + badgeClass + '">' + (item.status || '') + '</span>' +
        '</div>' +
        '<div class="incard-loc"><i class="fa-solid fa-location-dot"></i> ' + (item.pcnu || '—') + '</div>' +
        '<div class="incard-desc">' + desc + '</div>' +
        '<div class="incard-date"><i class="fa-regular fa-calendar"></i> ' + date + '</div>';
    return card;
}

// ===== Drawer =====
var allIncidents = [];

function closeDrawer() {
    document.getElementById('overlay').classList.remove('open');
    document.getElementById('drawer').classList.remove('open');
}

function openDrawer(filter, highlightId) {
    if (!dashboardData || !dashboardData.insiden) return;

    var items = dashboardData.insiden;
    allIncidents = items;

    var title = 'Semua Kejadian';
    if (filter === 'needs') {
        items = items.filter(function (i) { return i.needs_numeric && Object.keys(i.needs_numeric).length > 0; });
        items.sort(function (a, b) {
            var ga = a.needs_numeric ? Object.values(a.needs_numeric).reduce(function(s,v){return s+v;},0) : 0;
            var gb = b.needs_numeric ? Object.values(b.needs_numeric).reduce(function(s,v){return s+v;},0) : 0;
            return gb - ga;
        });
        title = 'Gap Kebutuhan Tertinggi';
    } else if (filter === 'casualties') {
        items = items.filter(function (i) { return (i.korban_summary || 0) > 0; });
        items.sort(function (a, b) {
            return (b.korban_summary || 0) - (a.korban_summary || 0);
        });
        title = 'Kejadian dengan Korban';
    }

    document.getElementById('drawerTitle').textContent = title;
    var body = document.getElementById('drawerBody');
    body.innerHTML = '';

    if (items.length === 0) {
        body.innerHTML = '<div style="text-align:center;padding:32px 0;color:#ccc;">Tidak ada data.</div>';
    } else {
        items.forEach(function (item) {
            var badgeClass = item.status === 'respon' ? 'respon' :
                             item.status === 'pemulihan' ? 'pemulihan' :
                             item.status === 'terverifikasi' ? 'terverifikasi' : 'default';
            var date = item.waktu_mulai ? new Date(item.waktu_mulai).toLocaleDateString('id-ID', {
                day: 'numeric', month: 'short', year: 'numeric'
            }) : '—';
            var gapTotal = 0;
            if (item.needs_numeric) {
                var vals = Object.values(item.needs_numeric);
                for (var i = 0; i < vals.length; i++) gapTotal += vals[i];
            }

            var el = document.createElement('div');
            el.className = 'dlist-item';
            el.setAttribute('data-id', item.id);
            el.innerHTML =
                '<div class="dlist-top">' +
                    '<div class="dlist-title">' + (item.jenis || 'Kejadian') + '</div>' +
                    '<span class="incard-badge ' + badgeClass + '">' + (item.status || '') + '</span>' +
                '</div>' +
                '<div class="dlist-meta">' +
                    '<span><i class="fa-solid fa-location-dot"></i> ' + (item.pcnu || '—') + '</span>' +
                    '<span><i class="fa-regular fa-calendar"></i> ' + date + '</span>' +
                    (gapTotal > 0 ? '<span style="color:#d97706;"><i class="fa-solid fa-box"></i> Gap: ' + gapTotal + '</span>' : '') +
                    ((item.korban_summary || 0) > 0 ? '<span style="color:#157347;"><i class="fa-solid fa-users"></i> Korban: ' + item.korban_summary + '</span>' : '') +
                '</div>' +
                '<div style="margin-top:6px;"><span class="dlist-arrow"><i class="fa-solid fa-chevron-down"></i></span></div>' +
                '<div class="detail-panel" id="detail_' + item.id + '"></div>';

            el.onclick = function (e) {
                toggleDetail(item.id, el);
            };
            body.appendChild(el);

            if (highlightId && highlightId == item.id) {
                toggleDetail(item.id, el);
            }
        });
    }

    document.getElementById('overlay').classList.add('open');
    document.getElementById('drawer').classList.add('open');
}

function toggleDetail(id, containerEl) {
    var panel = document.getElementById('detail_' + id);
    if (!panel) return;
    var arrow = containerEl.querySelector('.dlist-arrow');
    if (!arrow) return;

    if (panel.classList.contains('open')) {
        panel.classList.remove('open');
        arrow.classList.remove('open');
        return;
    }

    arrow.classList.add('open');

    if (incidentDetailCache[id]) {
        renderDetail(panel, incidentDetailCache[id]);
        panel.classList.add('open');
        return;
    }

    panel.innerHTML = '<div class="detail-loading"><i class="fa-solid fa-spinner fa-spin"></i> Memuat detail...</div>';
    panel.classList.add('open');

    fetch('/api/public/incident/' + id + '/detail')
        .then(function (r) { return r.json(); })
        .then(function (data) {
            incidentDetailCache[id] = data;
            renderDetail(panel, data);
        })
        .catch(function () {
            panel.innerHTML = '<div class="detail-loading" style="color:#e74c3c;">Gagal memuat detail.</div>';
        });
}

function renderDetail(panel, data) {
    var html = '';

    // Header info (Kode, Status)
    if (data.kode) {
        html += '<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 8px;">' +
            '<span style="font-size:10px; font-weight:700; color:#666; background:#f0f0f0; padding:3px 8px; border-radius:4px;">' + data.kode + '</span>';
        if (data.status) {
            var badgeClass = data.status === 'respon' ? 'respon' :
                             data.status === 'pemulihan' ? 'pemulihan' :
                             data.status === 'terverifikasi' ? 'terverifikasi' : 'default';
            // simple inline style for status badge
            var bg = badgeClass === 'respon' ? '#fde8e8' : badgeClass === 'pemulihan' ? '#fef3c7' : badgeClass === 'terverifikasi' ? '#dbeafe' : '#f3f4f6';
            var col = badgeClass === 'respon' ? '#dc2626' : badgeClass === 'pemulihan' ? '#d97706' : badgeClass === 'terverifikasi' ? '#2563eb' : '#6b7280';
            html += '<span style="font-size:9px; font-weight:700; padding:2px 7px; border-radius:5px; text-transform:uppercase; background:'+bg+'; color:'+col+';">' + data.status + '</span>';
        }
        html += '</div>';
    }

    // Dampak stats
    var d = data.dampak;
    if (d) {
        html += '<div class="detail-grid">' +
            '<div class="detail-stat"><div class="ds-value">' + (d.meninggal||0) + '</div><div class="ds-label">Meninggal</div></div>' +
            '<div class="detail-stat"><div class="ds-value">' + (d.luka_berat||0) + '</div><div class="ds-label">Luka Berat</div></div>' +
            '<div class="detail-stat"><div class="ds-value">' + (d.luka_ringan||0) + '</div><div class="ds-label">Luka Ringan</div></div>' +
            '<div class="detail-stat"><div class="ds-value">' + (d.mengungsi||0) + '</div><div class="ds-label">Mengungsi</div></div>' +
        '</div>';
    }

    // Keterangan
    if (data.laporan && data.laporan.keterangan) {
        html += '<div class="detail-desc"><strong style="font-size:10px;color:#999;text-transform:uppercase;">Situasi:</strong><br>' + data.laporan.keterangan + '</div>';
    }

    // Pelapor
    if (data.laporan && data.laporan.pelapor) {
        html += '<div style="font-size:10px;color:#999;margin:4px 0;"><i class="fa-solid fa-user"></i> ' + data.laporan.pelapor + ' &middot; ' + data.laporan.hp_pelapor + '</div>';
    }

    // Needs
    var needs = data.needs_numeric;
    if (needs && Object.keys(needs).length > 0) {
        html += '<div class="detail-needs"><div style="font-size:10px;font-weight:600;color:#d97706;margin-bottom:4px;"><i class="fa-solid fa-box"></i> Kebutuhan:</div>';
        for (var k in needs) {
            var nv = needs[k];
            var gapVal = typeof nv === 'object' ? nv.gap : nv;
            html += '<div class="detail-needs-item"><span class="dn-name">' + k + '</span><span class="dn-gap">' + gapVal + '</span></div>';
        }
        html += '</div>';
    }

    // Personel
    if (data.personel_count > 0) {
        html += '<div style="font-size:10px;color:#999;margin-top:6px;"><i class="fa-solid fa-users"></i> ' + data.personel_count + ' personel ditugaskan</div>';
    }

    panel.innerHTML = html;
}

// ===== Early Warning =====
function renderEarlyWarning(data) {
    var ew = document.getElementById('earlyWarning');
    var bar = document.getElementById('ewBar');
    var text = document.getElementById('ewText');
    if (!data || !data.risk) { ew.style.display = 'none'; return; }
    var level = data.highest_risk_level || 'LOW';
    var risks = data.risk || {};
    var labels = { 'LOW':'Aman', 'MEDIUM':'Siaga', 'HIGH':'Waspada', 'CRITICAL':'Bahaya' };
    var classes = { 'LOW':'ew-low', 'MEDIUM':'ew-medium', 'HIGH':'ew-high', 'CRITICAL':'ew-critical' };
    var activeTypes = [];
    var detailHtml = '';
    for (var key in risks) {
        var r = risks[key];
        if (r.level === 'LOW') continue;
        var names = { heavy_rain:'Hujan Lebat', flood:'Banjir', strong_wind:'Angin Kencang', thunderstorm:'Petir' };
        activeTypes.push(names[key] || key);
        detailHtml += '<div><strong>' + (names[key] || key) + ':</strong> ' + (r.reason || '') + '</div>';
    }
    if (activeTypes.length === 0) { ew.style.display = 'none'; return; }
    ew.style.display = 'block';
    bar.className = 'ew-bar ' + (classes[level] || 'ew-low');
    text.innerHTML = (labels[level] || level) + ': ' + activeTypes.join(', ');
    bar.onclick = function() {
        var detail = document.getElementById('riskDetail');
        if (!detail) {
            detail = document.createElement('div');
            detail.id = 'riskDetail';
            detail.className = 'risk-detail';
            detail.innerHTML = detailHtml;
            bar.parentNode.insertBefore(detail, bar.nextSibling);
        }
        detail.style.display = detail.style.display === 'none' ? 'block' : 'none';
    };
}

// ===== Weather =====
(function loadWeather() {
    var cityEl = document.getElementById('weatherCity');
    var nowEl = document.getElementById('weatherNow');
    var scrollEl = document.getElementById('weatherScroll');

    fetch('/api/internal/weather/summary?territory_code=pwnu:0')
        .then(function (r) { return r.json(); })
        .then(function (res) {
            var w = res.data;
            if (!w) {
                scrollEl.innerHTML = '<div style="color:#ccc;font-size:11px;padding:8px;">Data cuaca belum tersedia.</div>';
                return;
            }

            // Early Warning
            renderEarlyWarning(w);

            // Current Weather
            var cur = w.current;
            if (cur && cur.temperature) {
                nowEl.innerHTML =
                    '<div class="wn-icon"><i class="fa-solid ' + weatherIcon(cur.condition_code) + '"></i></div>' +
                    '<div><div class="wn-temp">' + cur.temperature + '&deg;</div>' +
                    '<div class="wn-city">' + (cur.condition || '') + ' &middot; ' + (res.last_update ? new Date(res.last_update).toLocaleTimeString('id-ID', {hour:'2-digit',minute:'2-digit'}) : '') + '</div></div>';
            }

            // Daily Summary
            var ds = w.daily_summary || {};
            if (ds.rain_probability_today !== undefined) {
                cityEl.textContent = 'Hujan: ' + ds.rain_probability_today + '% | ' + (ds.temp_min_today || '--') + '&deg; ~ ' + (ds.temp_max_today || '--') + '&deg;';
            }

            // Hourly Forecast
            fetch('/api/weather/forecast?lat=-7.5&lon=110.0')
                .then(function (r) { return r.json(); })
                .then(function (w2) {
                    if (!w2 || !w2.list) {
                        scrollEl.innerHTML = '<div style="color:#ccc;font-size:11px;padding:8px;">Tidak ada data prakiraan.</div>';
                        return;
                    }
                    var dayNames = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
                    var html = '';
                    var prevDay = '';
                    (w2.list || []).forEach(function (f) {
                        if (!f.time) return;
                        var dayKey = f.time.substring(0, 10);
                        if (dayKey !== prevDay) {
                            var d = new Date(f.time);
                            if (isNaN(d.getTime())) return;
                            var dayName = dayNames[d.getDay()];
                            var dateStr = d.toLocaleDateString('id-ID', { day:'numeric', month:'short' });
                            if (html !== '') html += '</div></div>';
                            html += '<div class="wf-day">' +
                                '<div class="wf-day-label">' + dayName + '<br>' + dateStr + '</div>' +
                                '<div class="wf-day-items">';
                            prevDay = dayKey;
                        }
                        html += '<div class="wf-item">' +
                            '<div class="wf-label">' + f.label + '</div>' +
                            '<div class="wf-icon"><i class="fa-solid ' + weatherIcon(f.icon) + '"></i></div>' +
                            '<div class="wf-temp">' + f.temp + '&deg;</div>' +
                            '<div class="wf-cond">' + f.condition + '</div>' +
                        '</div>';
                    });
                    if (html !== '') html += '</div></div>';
                    scrollEl.innerHTML = html || '<div style="color:#ccc;font-size:11px;padding:8px;">Tidak ada data prakiraan.</div>';
                })
                .catch(function () {
                    scrollEl.innerHTML = '<div style="color:#ccc;font-size:11px;padding:8px;">Gagal memuat prakiraan cuaca.</div>';
                });
        })
        .catch(function () {
            cityEl.textContent = 'Gagal memuat cuaca';
            scrollEl.innerHTML = '<div style="color:#ccc;font-size:11px;padding:8px;">Gagal memuat cuaca. Coba lagi nanti.</div>';
        });
})();
</script>
@endpush
