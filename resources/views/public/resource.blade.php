@extends('layouts.public')

@section('title', 'Resource — NURISK')
@section('nav-resource', 'active')

@push('head')
<style>
    .resource-header { padding: 24px 0 20px; }
    .resource-header h1 { font-size: 28px; font-weight: 800; color: #1a1a2e; }
    .resource-header p { font-size: 15px; color: #888; margin-top: 4px; }

    .resource-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
    .resource-item {
        background: #fff; border-radius: 14px; padding: 24px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04); border: 1px solid #f0f0f0;
        transition: all 0.2s;
    }
    .resource-item:hover { border-color: var(--nu-green); transform: translateY(-2px); box-shadow: 0 6px 20px rgba(21,115,71,0.06); }
    .resource-item .ri-icon { font-size: 32px; margin-bottom: 12px; }
    .resource-item .ri-title { font-size: 16px; font-weight: 600; color: #1a1a2e; }
    .resource-item .ri-desc { font-size: 13px; color: #999; margin-top: 6px; line-height: 1.5; }
    .resource-item.coming-soon { position: relative; }
    .resource-item.coming-soon::after {
        content: 'Segera Hadir';
        position: absolute; top: 12px; right: 12px;
        font-size: 10px; font-weight: 600;
        background: #eee; color: #888;
        padding: 3px 10px; border-radius: 20px;
    }
    .resource-footer {
        margin-top: 32px; padding: 32px; text-align: center;
        border: 1px dashed #ddd; border-radius: 14px;
        color: #aaa; font-size: 14px; line-height: 1.6;
    }

    @media (max-width: 900px) {
        .resource-grid { grid-template-columns: 1fr 1fr; }
    }
    @media (max-width: 600px) {
        .resource-grid { grid-template-columns: 1fr; }
        .resource-header h1 { font-size: 22px; }
    }
</style>
@endpush

@section('content')
<div class="page-container">
    <div class="resource-header">
        <h1>📦 Resource & Layanan</h1>
        <p>Direktori sumber daya dan layanan NU Peduli Jawa Tengah</p>
    </div>

    <div class="resource-grid">
        <div class="resource-item">
            <div class="ri-icon">🏥</div>
            <div class="ri-title">Posko Kesehatan</div>
            <div class="ri-desc">Lokasi dan informasi posko kesehatan, rumah sakit rujukan, dan fasilitas medis terdekat.</div>
        </div>
        <div class="resource-item">
            <div class="ri-icon">🚛</div>
            <div class="ri-title">Distribusi Logistik</div>
            <div class="ri-desc">Informasi pusat distribusi bantuan, gudang logistik, dan rantai pasokan.</div>
        </div>
        <div class="resource-item">
            <div class="ri-icon">🚑</div>
            <div class="ri-title">Tim Reaksi Cepat</div>
            <div class="ri-desc">Database TRC (Tim Reaksi Cepat) NU yang siap diterjunkan ke lokasi bencana.</div>
        </div>
        <div class="resource-item">
            <div class="ri-icon">📋</div>
            <div class="ri-title">SOP & Panduan</div>
            <div class="ri-desc">Panduan tanggap darurat, prosedur evakuasi, dan standar operasi penanggulangan bencana.</div>
        </div>
        <div class="resource-item">
            <div class="ri-icon">📡</div>
            <div class="ri-title">Komunikasi Darurat</div>
            <div class="ri-desc">Saluran komunikasi darurat, frekuensi radio, dan kontak personel kunci.</div>
        </div>
        <div class="resource-item">
            <div class="ri-icon">👥</div>
            <div class="ri-title">Relawan Terdaftar</div>
            <div class="ri-desc">Data relawan NU yang sudah terverifikasi dan siap ditugaskan berdasarkan keahlian.</div>
        </div>
        <div class="resource-item coming-soon">
            <div class="ri-icon">🍲</div>
            <div class="ri-title">Dapur Umum</div>
            <div class="ri-desc">Lokasi dapur umum, kebutuhan bahan makanan, dan jadwal distribusi makanan.</div>
        </div>
        <div class="resource-item coming-soon">
            <div class="ri-icon">🏠</div>
            <div class="ri-title">Pengungsian</div>
            <div class="ri-desc">Data lokasi pengungsian, kapasitas, dan kebutuhan dasar pengungsi.</div>
        </div>
        <div class="resource-item coming-soon">
            <div class="ri-icon">📊</div>
            <div class="ri-title">Statistik & Laporan</div>
            <div class="ri-desc">Dashboard statistik bencana, laporan periodik, dan analisis data kebencanaan.</div>
        </div>
    </div>

    <div class="resource-footer">
        🔧 Halaman Resource sedang dalam pengembangan aktif.<br>
        Fitur lengkap akan tersedia secara bertahap.
    </div>
</div>
@endsection
