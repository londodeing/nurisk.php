@extends('layouts.public')

@section('title', 'Pendaftaran Terkirim — NURISK')
@section('layout-class', 'full-width')

@push('head')
<style>
    .auth-split {
        display: flex; min-height: calc(100vh - var(--header-height));
        margin: calc(-24px - 32px); margin-top: calc(-24px);
    }
    .auth-hero {
        flex: 1; display: flex; flex-direction: column;
        justify-content: center; padding: 48px 56px;
        background: linear-gradient(135deg, #157347 0%, #0d4f2e 100%);
        color: #fff; position: relative; overflow: hidden;
    }
    .auth-hero::before {
        content: ''; position: absolute; top: -120px; right: -120px;
        width: 400px; height: 400px;
        background: rgba(255,255,255,0.04); border-radius: 50%;
    }
    .auth-hero h1 { font-size: 28px; font-weight: 800; margin-bottom: 6px; }
    .auth-hero p { font-size: 14px; opacity: 0.8; line-height: 1.6; max-width: 380px; }
    .auth-hero .hero-icon { font-size: 56px; margin-bottom: 16px; }

    .auth-form-wrap {
        flex: 0 0 480px; display: flex; flex-direction: column;
        justify-content: center; padding: 48px;
        background: #fff; text-align: center;
    }
    .waiting-icon { font-size: 56px; margin-bottom: 16px; }
    .auth-form-wrap h2 { font-size: 22px; font-weight: 700; color: #1a1a2e; margin-bottom: 6px; }
    .auth-form-wrap .subtitle { font-size: 14px; color: #999; line-height: 1.6; margin-bottom: 24px; }

    .timeline { text-align: left; background: #f9fafb; border-radius: 12px; padding: 20px; margin-bottom: 24px; }
    .timeline-item { display: flex; align-items: center; gap: 12px; padding: 8px 0; }
    .timeline-dot {
        width: 24px; height: 24px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 11px; font-weight: 700; flex-shrink: 0;
    }
    .timeline-dot.done { background: var(--nu-green); color: #fff; }
    .timeline-dot.pending { background: #e0e0e0; color: #999; }
    .timeline-dot.active { background: #fef9e7; color: #b7950b; border: 2px solid #f0d060; }
    .timeline-text { font-size: 13px; color: #666; }
    .timeline-text strong { color: #1a1a2e; }

    .alert { padding: 10px 14px; border-radius: 8px; font-size: 13px; margin-bottom: 18px; text-align: left; }
    .alert-info { background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; }

    .auth-actions { display: flex; flex-direction: column; gap: 10px; }
    .auth-actions .btn {
        padding: 11px; border-radius: 10px; font-size: 14px; font-weight: 600;
        cursor: pointer; transition: all 0.2s; display: block; text-align: center;
        text-decoration: none;
    }
    .auth-actions .btn-primary { background: var(--nu-green); color: #fff; border: none; }
    .auth-actions .btn-primary:hover { background: var(--nu-green-dark); }
    .auth-actions .btn-secondary { background: #f0f0f0; color: #666; border: none; }
    .auth-actions .btn-secondary:hover { background: #e0e0e0; }

    .help-text { font-size: 12px; color: #bbb; margin-top: 16px; }

    @media (max-width: 900px) {
        .auth-split { flex-direction: column; margin: -16px; margin-top: -16px; }
        .auth-hero { padding: 24px 20px; }
        .auth-hero h1 { font-size: 22px; }
        .auth-form-wrap { flex: 1; padding: 28px 20px; }
    }
</style>
@endpush

@section('content')
<div class="auth-split">
    <div class="auth-hero">
        <div class="hero-icon">📋</div>
        <h1>Pendaftaran Terkirim</h1>
        <p>Akun Anda sedang dalam proses verifikasi oleh administrator. Kami akan memberitahu Anda setelah akun disetujui.</p>
    </div>
    <div class="auth-form-wrap">
        <div class="waiting-icon">⏳</div>
        <h2>Menunggu Persetujuan</h2>
        <p class="subtitle">Pendaftaran Anda berhasil dikirim dan sedang menunggu verifikasi.</p>

        @if (session('info'))
            <div class="alert alert-info">{{ session('info') }}</div>
        @endif

        <div class="timeline">
            <div class="timeline-item">
                <div class="timeline-dot done">✓</div>
                <div class="timeline-text"><strong>Pendaftaran terkirim</strong><br><span style="font-size:12px;color:#999;">Data Anda telah kami terima</span></div>
            </div>
            <div class="timeline-item">
                <div class="timeline-dot active">2</div>
                <div class="timeline-text"><strong>Verifikasi oleh administrator</strong><br><span style="font-size:12px;color:#999;">Admin akan memeriksa kelengkapan data</span></div>
            </div>
            <div class="timeline-item">
                <div class="timeline-dot pending">3</div>
                <div class="timeline-text"><strong>Akun aktif</strong><br><span style="font-size:12px;color:#999;">Anda bisa login dan mulai berkontribusi</span></div>
            </div>
        </div>

        <div class="auth-actions">
            <a href="{{ route('login') }}" class="btn btn-primary">Kembali ke Halaman Login</a>
            <a href="{{ route('register') }}" class="btn btn-secondary">Daftar dengan Akun Lain</a>
        </div>

        <p class="help-text">Butuh bantuan? Hubungi administrator PCNU Anda.</p>
    </div>
</div>
@endsection
