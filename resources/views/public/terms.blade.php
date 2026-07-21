@extends('layouts.public')

@section('title', 'Syarat dan Ketentuan — NURISK')

@section('layout-class', 'full-width')

@push('styles')
<style>
    .terms-container {
        max-width: 800px;
        margin: 0 auto;
        padding: 40px 0;
    }
    .terms-container h1 {
        font-size: 2rem;
        font-weight: 800;
        color: var(--nu-green-dark);
        margin-bottom: 8px;
        line-height: 1.2;
    }
    .terms-container .subtitle {
        font-size: 0.95rem;
        color: #888;
        margin-bottom: 40px;
        border-bottom: 2px solid var(--nu-green-light);
        padding-bottom: 16px;
    }
    .terms-container h2 {
        font-size: 1.35rem;
        font-weight: 700;
        color: var(--nu-green);
        margin-top: 36px;
        margin-bottom: 12px;
    }
    .terms-container p {
        font-size: 1rem;
        line-height: 1.8;
        color: #4a5568;
        margin-bottom: 16px;
        text-align: justify;
    }
    .terms-container ul, .terms-container ol {
        margin: 12px 0 20px 24px;
        line-height: 1.8;
        color: #4a5568;
    }
    .terms-container li {
        margin-bottom: 8px;
    }
    .terms-container .contact-card {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 24px;
        margin-top: 32px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    }
    .terms-container .contact-card p {
        margin-bottom: 8px;
    }
    .terms-container .contact-card a {
        color: var(--nu-green);
        font-weight: 600;
        text-decoration: none;
    }
    .terms-container .contact-card a:hover {
        text-decoration: underline;
    }
    @media (max-width: 640px) {
        .terms-container { padding: 24px 0; }
        .terms-container h1 { font-size: 1.6rem; }
    }
</style>
@endpush

@section('content')
<div class="terms-container">

    <h1>Syarat dan Ketentuan NURisk</h1>
    <div class="subtitle">Terakhir diperbarui: <strong>21 Juli 2026</strong></div>

    <p>
        Dengan mengakses dan menggunakan aplikasi NURisk, Anda menyetujui syarat dan ketentuan berikut.
        Jika Anda tidak setuju dengan salah satu syarat ini, harap hentikan penggunaan aplikasi.
    </p>

    <h2>1. Penerimaan Ketentuan</h2>
    <p>
        Dengan mendaftar, mengakses, atau menggunakan layanan NURisk, Anda menyatakan telah membaca,
        memahami, dan menyetujui untuk terikat oleh Syarat dan Ketentuan ini.
    </p>

    <h2>2. Deskripsi Layanan</h2>
    <p>
        NURisk adalah sistem pelaporan dan manajemen penanggulangan bencana yang dikelola oleh
        LPBI NU Jawa Tengah. Layanan ini mencakup pelaporan kejadian bencana oleh warga, verifikasi
        oleh petugas, dashboard informasi bencana, dan koordinasi respons penanggulangan bencana.
    </p>

    <h2>3. Kewajiban Pengguna</h2>
    <ul>
        <li>Memberikan data yang benar, akurat, dan terkini saat pendaftaran.</li>
        <li>Tidak menyalahgunakan fitur pelaporan untuk tujuan palsu atau menyesatkan.</li>
        <li>Tidak mengunggah konten yang melanggar hukum, mengandung ujaran kebencian, atau bersifat eksplisit.</li>
        <li>Menjaga kerahasiaan kata sandi dan tidak membagikan akun kepada pihak lain.</li>
        <li>Bertanggung jawab penuh atas setiap aktivitas yang terjadi dalam akun Anda.</li>
    </ul>

    <h2>4. Konten Pengguna</h2>
    <p>
        Anda tetap memiliki hak atas foto, dokumen, dan informasi yang Anda unggah. Dengan mengunggahnya,
        Anda memberikan izin kepada LPBI NU Jawa Tengah untuk menggunakan konten tersebut untuk kepentingan
        verifikasi, koordinasi, dan respons penanggulangan bencana.
    </p>

    <h2>5. Keterbatasan Tanggung Jawab</h2>
    <p>
        NURisk dan LPBI NU Jawa Tengah tidak bertanggung jawab atas kerugian yang timbul akibat:
    </p>
    <ul>
        <li>Penggunaan layanan yang tidak sesuai dengan ketentuan.</li>
        <li>Ketidakakuratan informasi yang disampaikan oleh pengguna.</li>
        <li>Gangguan teknis di luar kendali pengelola.</li>
    </ul>

    <h2>6. Pembatasan Akses</h2>
    <p>
        Pengelola berhak menangguhkan atau menghentikan akses pengguna yang melanggar ketentuan ini
        tanpa pemberitahuan sebelumnya.
    </p>

    <h2>7. Perubahan Ketentuan</h2>
    <p>
        Syarat dan Ketentuan ini dapat diperbarui dari waktu ke waktu. Perubahan akan diberitahukan
        melalui aplikasi. Pengguna yang tetap menggunakan layanan setelah perubahan dianggap menyetujui
        ketentuan yang telah diperbarui.
    </p>

    <h2>8. Hukum yang Berlaku</h2>
    <p>
        Ketentuan ini tunduk pada hukum dan peraturan perundang-undangan yang berlaku di Negara
        Kesatuan Republik Indonesia.
    </p>

    <h2>9. Kontak Kami</h2>
    <div class="contact-card">
        <p><strong>LPBI NU Jawa Tengah</strong></p>
        <p>Email: <a href="mailto:privasi@nurisk.id">privasi@nurisk.id</a></p>
        <p>Website: <a href="https://nurisk.org" target="_blank">https://nurisk.org</a></p>
    </div>

</div>
@endsection
