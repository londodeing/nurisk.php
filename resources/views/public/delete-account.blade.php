@extends('layouts.public')

@section('title', 'Penghapusan Akun — NURISK')

@section('layout-class', 'full-width')

@push('styles')
<style>
    .delete-account-container {
        max-width: 640px;
        margin: 0 auto;
        padding: 40px 0;
        text-align: center;
    }
    .delete-account-container h1 {
        font-size: 2rem;
        font-weight: 800;
        color: var(--nu-green-dark);
        margin-bottom: 8px;
        line-height: 1.2;
    }
    .delete-account-container .subtitle {
        font-size: 0.95rem;
        color: #888;
        margin-bottom: 40px;
    }
    .delete-account-container .method-card {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 28px;
        margin-bottom: 20px;
        text-align: left;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        transition: box-shadow 0.2s;
    }
    .delete-account-container .method-card:hover {
        box-shadow: 0 4px 16px rgba(0,0,0,0.08);
    }
    .delete-account-container .method-card .method-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 16px;
        font-size: 24px;
    }
    .delete-account-container .method-card h3 {
        font-size: 1.1rem;
        font-weight: 700;
        color: #1a1a2e;
        margin-bottom: 8px;
    }
    .delete-account-container .method-card p, .delete-account-container .method-card ol {
        font-size: 0.95rem;
        line-height: 1.7;
        color: #4a5568;
    }
    .delete-account-container .method-card ol {
        padding-left: 20px;
    }
    .delete-account-container .method-card ol li {
        margin-bottom: 6px;
    }
    .delete-account-container .method-card a {
        color: var(--nu-green);
        font-weight: 600;
        text-decoration: none;
    }
    .delete-account-container .method-card a:hover {
        text-decoration: underline;
    }
    .delete-account-container .note-box {
        background: #FFF8E6;
        border: 1px solid #F0D78E;
        border-radius: 12px;
        padding: 16px 20px;
        margin-top: 24px;
        text-align: left;
    }
    .delete-account-container .note-box p {
        font-size: 0.9rem;
        color: #7A6200;
        margin-bottom: 0;
    }
    .delete-account-container .back-link {
        display: inline-block;
        margin-top: 32px;
        color: var(--nu-green);
        font-weight: 600;
        text-decoration: none;
    }
    .delete-account-container .back-link:hover {
        text-decoration: underline;
    }
    @media (max-width: 640px) {
        .delete-account-container { padding: 24px 0; }
        .delete-account-container h1 { font-size: 1.6rem; }
    }
</style>
@endpush

@section('content')
<div class="delete-account-container">

    <h1>Penghapusan Akun</h1>
    <p class="subtitle">Ajukan penghapusan akun dan data pribadi Anda dari NURisk</p>

    <div class="method-card">
        <div class="method-icon" style="background: #E6F3EC;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#0F6B3C" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                <polyline points="7 10 12 15 17 10"/>
                <line x1="12" y1="15" x2="12" y2="3"/>
            </svg>
        </div>
        <h3>Opsi 1: Melalui Aplikasi</h3>
        <p>Pengguna aplikasi Android NURisk dapat menghapus akun langsung dari dalam aplikasi:</p>
        <ol>
            <li>Buka tab <strong>Profil</strong></li>
            <li>Gulir ke bawah ke bagian <strong>Hapus Akun</strong></li>
            <li>Tekan tombol <strong>Hapus Akun</strong></li>
            <li>Konfirmasi penghapusan pada dialog yang muncul</li>
        </ol>
        <p style="margin-top: 12px;">Proses akan berjalan otomatis dan akun Anda akan dinonaktifkan beserta seluruh data pribadi yang dianonimkan.</p>
    </div>

    <div class="method-card">
        <div class="method-icon" style="background: #E8F0FE;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#0F6B3C" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                <polyline points="22,6 12,13 2,6"/>
            </svg>
        </div>
        <h3>Opsi 2: Melalui Email</h3>
        <p>Kirim permohonan penghapusan akun ke alamat email resmi kami:</p>
        <p style="margin-top: 12px; font-size: 1.1rem;">
            <a href="mailto:privasi@nurisk.id?subject=Permohonan%20Penghapusan%20Akun&body=Saya%20yang%20bertanda%20tangan%20di%20bawah%20ini%20mengajukan%20permohonan%20penghapusan%20akun%20NURisk%20saya.%0A%0ANomor%20HP%20terdaftar%3A%20%5Bisi%20nomor%20HP%5D%0ANama%20lengkap%3A%20%5Bisi%20nama%20lengkap%5D">privasi@nurisk.id</a>
        </p>
        <p>Gunakan subjek email: <strong>"Permohonan Penghapusan Akun"</strong> dan sertakan nomor HP serta nama lengkap yang terdaftar untuk verifikasi.</p>
    </div>

    <div class="note-box">
        <p><strong>Catatan:</strong> Permohonan penghapusan akan diproses dalam waktu 14 hari kerja setelah identitas Anda terverifikasi. Beberapa data tertentu mungkin tetap disimpan jika diwajibkan oleh ketentuan hukum yang berlaku (misalnya untuk keperluan audit). Setelah akun dihapus, Anda tidak dapat login kembali dan data pribadi Anda akan dianonimkan secara permanen.</p>
    </div>

    <a href="/privacy" class="back-link">&larr; Kebijakan Privasi</a>

</div>
@endsection
