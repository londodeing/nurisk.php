@extends('layouts.public')

@section('title', 'Konfirmasi Sandi — NURISK')
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
    .auth-hero .hero-icon { font-size: 48px; margin-bottom: 16px; }

    .auth-form-wrap {
        flex: 0 0 420px; display: flex; flex-direction: column;
        justify-content: center; padding: 48px;
        background: #fff;
    }
    .auth-form-wrap h2 { font-size: 22px; font-weight: 700; color: #1a1a2e; margin-bottom: 4px; }
    .auth-form-wrap .subtitle { font-size: 14px; color: #999; line-height: 1.6; margin-bottom: 24px; }

    .form-group { margin-bottom: 18px; }
    .form-group label { display: block; font-size: 13px; font-weight: 600; color: #555; margin-bottom: 6px; }
    .form-group .form-input {
        width: 100%; padding: 11px 14px;
        border: 1.5px solid #e0e0e0; border-radius: 10px;
        font-size: 14px; transition: all 0.2s; background: #fafafa;
    }
    .form-group .form-input:focus {
        outline: none; border-color: var(--nu-green);
        box-shadow: 0 0 0 3px rgba(21,115,71,0.1); background: #fff;
    }

    .auth-form-wrap .btn {
        width: 100%; padding: 12px; border: none; border-radius: 10px;
        background: linear-gradient(135deg, var(--nu-green), var(--nu-green-dark));
        color: #fff; font-size: 15px; font-weight: 600;
        cursor: pointer; transition: all 0.2s;
    }
    .auth-form-wrap .btn:hover { transform: translateY(-1px); box-shadow: 0 4px 14px rgba(21,115,71,0.3); }

    @media (max-width: 900px) {
        .auth-split { flex-direction: column; margin: -16px; margin-top: -16px; }
        .auth-hero { padding: 24px 20px; }
        .auth-hero h1 { font-size: 22px; }
        .auth-form-wrap { flex: 1; padding: 28px 24px; }
    }
</style>
@endpush

@section('content')
<div class="auth-split">
    <div class="auth-hero">
        <div class="hero-icon">🔒</div>
        <h1>Konfirmasi Keamanan</h1>
        <p>Ini adalah area aman. Harap konfirmasi kata sandi Anda sebelum melanjutkan.</p>
    </div>
    <div class="auth-form-wrap">
        <h2>Konfirmasi Kata Sandi</h2>
        <p class="subtitle">Masukkan kata sandi Anda untuk melanjutkan</p>

        <form method="POST" action="{{ route('password.confirm') }}">
            @csrf

            <div class="form-group">
                <label for="kata_sandi">Kata Sandi</label>
                <input type="password" name="kata_sandi" id="kata_sandi"
                    autocomplete="current-password" required
                    class="form-input @error('kata_sandi') error @enderror">
                @error('kata_sandi')
                    <p style="font-size:12px;color:#e74c3c;margin-top:4px;">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="btn">Konfirmasi</button>
        </form>
    </div>
</div>
@endsection
