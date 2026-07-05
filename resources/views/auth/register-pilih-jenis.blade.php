@extends('layouts.public')

@section('title', 'Daftar — NURISK')
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
    .auth-hero h1 { font-size: 32px; font-weight: 800; margin-bottom: 6px; }
    .auth-hero p { font-size: 15px; opacity: 0.8; line-height: 1.6; max-width: 400px; }
    .auth-hero .hero-icon { font-size: 56px; margin-bottom: 20px; }

    .auth-form-wrap {
        flex: 0 0 520px; display: flex; flex-direction: column;
        justify-content: center; padding: 40px 48px;
        background: #fff; overflow-y: auto;
    }
    .auth-form-wrap .form-header { margin-bottom: 24px; }
    .auth-form-wrap .form-header h2 { font-size: 22px; font-weight: 700; color: #1a1a2e; }
    .auth-form-wrap .form-header p { font-size: 14px; color: #999; margin-top: 4px; }

    .register-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .register-card {
        display: flex; flex-direction: column; padding: 20px;
        border: 1.5px solid #eee; border-radius: 14px;
        text-decoration: none; transition: all 0.2s;
    }
    .register-card:hover { border-color: var(--nu-green); box-shadow: 0 4px 16px rgba(21,115,71,0.06); transform: translateY(-2px); }
    .register-card .rc-icon { font-size: 28px; margin-bottom: 10px; }
    .register-card .rc-title { font-size: 15px; font-weight: 600; color: #1a1a2e; }
    .register-card .rc-desc { font-size: 12px; color: #999; margin-top: 4px; line-height: 1.5; }
    .register-card .rc-badge {
        display: inline-block; margin-top: 10px;
        font-size: 10px; font-weight: 600; padding: 3px 10px;
        border-radius: 20px; align-self: flex-start;
    }
    .rc-badge-green { background: #e8f5ee; color: #157347; }
    .rc-badge-yellow { background: #fef9e7; color: #b7950b; }
    .rc-badge-orange { background: #fef5e7; color: #e67e22; }
    .rc-badge-purple { background: #f3e8ff; color: #7c3aed; }
    .rc-badge-red { background: #fef2f2; color: #b91c1c; }
    .register-card.full { grid-column: 1 / -1; }

    .form-footer { margin-top: 24px; text-align: center; }
    .form-footer p { font-size: 13px; color: #999; }
    .form-footer a { color: var(--nu-green); font-weight: 600; text-decoration: none; }

    .alert { padding: 10px 14px; border-radius: 8px; font-size: 13px; margin-bottom: 18px; }
    .alert-error { background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; }

    @media (max-width: 900px) {
        .auth-split { flex-direction: column; margin: -16px; margin-top: -16px; }
        .auth-hero { padding: 28px 24px; }
        .auth-hero h1 { font-size: 24px; }
        .auth-form-wrap { flex: 1; padding: 24px 20px; }
        .register-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')
<div class="auth-split">
    <div class="auth-hero">
        <div class="hero-icon">🤝</div>
        <h1>Bergabung dengan NU Peduli</h1>
        <p>Daftar sebagai relawan, TRC, atau administrator untuk berkontribusi dalam penanggulangan bencana.</p>
    </div>
    <div class="auth-form-wrap">
        <div class="form-header">
            <h2>Pilih Jenis Akun</h2>
            <p>Sesuaikan dengan peran Anda di NU Peduli Jawa Tengah</p>
        </div>

        @if (session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        <div class="register-grid">
            <a href="{{ route('register.form', 'relawan') }}" class="register-card">
                <div class="rc-icon">🤝</div>
                <div class="rc-title">Relawan Umum</div>
                <div class="rc-desc">Bergabung sebagai relawan NU dalam kegiatan kemanusiaan</div>
                <span class="rc-badge rc-badge-green">Aktif langsung</span>
            </a>
            <a href="{{ route('register.form', 'trc_pcnu') }}" class="register-card">
                <div class="rc-icon">🚑</div>
                <div class="rc-title">TRC PCNU</div>
                <div class="rc-desc">Tim Reaksi Cepat tingkat cabang — relawan terlatih</div>
                <span class="rc-badge rc-badge-yellow">Perlu persetujuan Admin PCNU</span>
            </a>
            <a href="{{ route('register.form', 'trc_pwnu') }}" class="register-card">
                <div class="rc-icon">⚡</div>
                <div class="rc-title">TRC PWNU</div>
                <div class="rc-desc">Tim Reaksi Cepat tingkat wilayah — lintas cabang</div>
                <span class="rc-badge rc-badge-orange">Perlu persetujuan PCNU + PWNU</span>
            </a>
            <a href="{{ route('register.form', 'admin_pcnu') }}" class="register-card">
                <div class="rc-icon">🏢</div>
                <div class="rc-title">Admin PCNU</div>
                <div class="rc-desc">Operator sistem level cabang</div>
                <span class="rc-badge rc-badge-purple">Perlu persetujuan Admin PWNU</span>
            </a>
            <a href="{{ route('register.form', 'admin_pwnu') }}" class="register-card full">
                <div class="rc-icon">🏛️</div>
                <div class="rc-title">Admin PWNU</div>
                <div class="rc-desc">Operator sistem tingkat wilayah — akses penuh Jawa Tengah</div>
                <span class="rc-badge rc-badge-red">Perlu persetujuan Super Admin</span>
            </a>
        </div>

        <div class="form-footer">
            <p>Sudah punya akun? <a href="{{ route('login') }}">Masuk di sini</a></p>
        </div>
    </div>
</div>
@endsection
