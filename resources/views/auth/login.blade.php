@extends('layouts.public')

@section('title', 'Masuk — NURISK')
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
    .auth-hero::after {
        content: ''; position: absolute; bottom: -80px; left: -80px;
        width: 280px; height: 280px;
        background: rgba(255,255,255,0.03); border-radius: 50%;
    }
    .auth-hero .hero-icon { font-size: 64px; margin-bottom: 24px; }
    .auth-hero h1 { font-size: 32px; font-weight: 800; margin-bottom: 6px; }
    .auth-hero p { font-size: 15px; opacity: 0.8; line-height: 1.6; max-width: 400px; margin-bottom: 32px; }
    .auth-hero .hero-features { display: flex; flex-direction: column; gap: 14px; }
    .auth-hero .hero-feature { display: flex; align-items: center; gap: 12px; font-size: 14px; opacity: 0.85; }
    .auth-hero .hero-feature .hf-icon { font-size: 18px; }

    .auth-form-wrap {
        flex: 0 0 420px; display: flex; flex-direction: column;
        justify-content: center; padding: 48px;
        background: #fff;
    }
    .auth-form-wrap .form-header { margin-bottom: 28px; }
    .auth-form-wrap .form-header h2 { font-size: 22px; font-weight: 700; color: #1a1a2e; }
    .auth-form-wrap .form-header p { font-size: 14px; color: #999; margin-top: 4px; }

    .auth-form .form-group { margin-bottom: 18px; }
    .auth-form .form-group label { display: block; font-size: 13px; font-weight: 600; color: #555; margin-bottom: 6px; }
    .auth-form .form-group .form-input {
        width: 100%; padding: 11px 14px;
        border: 1.5px solid #e0e0e0; border-radius: 10px;
        font-size: 14px; transition: all 0.2s; background: #fafafa;
    }
    .auth-form .form-group .form-input:focus {
        outline: none; border-color: var(--nu-green);
        box-shadow: 0 0 0 3px rgba(21,115,71,0.1); background: #fff;
    }
    .auth-form .form-group .form-input.error { border-color: #e74c3c; }

    .auth-form .btn-submit {
        width: 100%; padding: 12px; border: none; border-radius: 10px;
        background: linear-gradient(135deg, var(--nu-green), var(--nu-green-dark));
        color: #fff; font-size: 15px; font-weight: 600;
        cursor: pointer; transition: all 0.2s; margin-top: 4px;
    }
    .auth-form .btn-submit:hover { transform: translateY(-1px); box-shadow: 0 4px 14px rgba(21,115,71,0.3); }

    .auth-form .form-footer { margin-top: 24px; text-align: center; }
    .auth-form .form-footer p { font-size: 13px; color: #999; }
    .auth-form .form-footer a { color: var(--nu-green); font-weight: 600; text-decoration: none; }
    .auth-form .form-footer a:hover { text-decoration: underline; }

    .auth-form .alert {
        padding: 10px 14px; border-radius: 8px; font-size: 13px;
        margin-bottom: 18px; line-height: 1.5;
    }
    .auth-form .alert-error { background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; }
    .auth-form .alert-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; }

    .password-toggle {
        position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
        background: none; border: none; color: #999; font-size: 12px;
        cursor: pointer; padding: 4px 8px; font-weight: 500;
    }
    .password-wrap { position: relative; }

    @media (max-width: 900px) {
        .auth-split { flex-direction: column; margin: -16px; margin-top: -16px; }
        .auth-hero { padding: 32px 24px; }
        .auth-hero h1 { font-size: 24px; }
        .auth-hero .hero-features { display: none; }
        .auth-form-wrap { flex: 1; padding: 28px 24px; }
    }
</style>
@endpush

@section('content')
<div class="auth-split">
    <div class="auth-hero">
        <div class="hero-icon">🏥</div>
        <h1>NU Peduli Jawa Tengah</h1>
        <p>Sistem Informasi Tanggap Darurat — Platform koordinasi penanggulangan bencana berbasis data.</p>
        <div class="hero-features">
            <div class="hero-feature"><span class="hf-icon">📡</span> Pelaporan kejadian real-time</div>
            <div class="hero-feature"><span class="hf-icon">🗺️</span> Peta bencana terintegrasi</div>
            <div class="hero-feature"><span class="hf-icon">🤝</span> Manajemen relawan & TRC</div>
            <div class="hero-feature"><span class="hf-icon">📊</span> Analisis & data kebencanaan</div>
        </div>
    </div>
    <div class="auth-form-wrap">
        <div class="form-header">
            <h2>Masuk ke Sistem</h2>
            <p>Gunakan nomor HP dan kata sandi Anda</p>
        </div>

        <form method="POST" action="{{ route('login') }}" class="auth-form">
            @csrf

            @if (session('error'))
                <div class="alert alert-error">{{ session('error') }}</div>
            @endif
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="form-group">
                <label for="no_hp">Nomor HP</label>
                <input type="tel" id="no_hp" name="no_hp" value="{{ old('no_hp') }}"
                    placeholder="Contoh: 08123456789" autocomplete="tel"
                    class="form-input @error('no_hp') error @enderror" required>
                @error('no_hp') <p style="font-size:12px;color:#e74c3c;margin-top:4px;">{{ $message }}</p> @enderror
            </div>

            <div class="form-group">
                <label for="kata_sandi">Kata Sandi</label>
                <div class="password-wrap">
                    <input type="password" id="kata_sandi" name="kata_sandi"
                        placeholder="Kata sandi Anda" autocomplete="current-password"
                        class="form-input @error('kata_sandi') error @enderror pr-20" required>
                    <button type="button" onclick="togglePassword()" class="password-toggle">Tampilkan</button>
                </div>
                @error('kata_sandi') <p style="font-size:12px;color:#e74c3c;margin-top:4px;">{{ $message }}</p> @enderror
            </div>

            <button type="submit" class="btn-submit">Masuk</button>

            <div class="form-footer">
                <p>Belum punya akun? <a href="{{ route('register') }}">Daftar sekarang</a></p>
                <p style="margin-top:8px;font-size:12px;color:#bbb;">
                    Lupa kata sandi? Hubungi Admin PCNU Anda.
                </p>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function togglePassword() {
    const f = document.getElementById('kata_sandi');
    f.type = f.type === 'password' ? 'text' : 'password';
}
</script>
@endpush
