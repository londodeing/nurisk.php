@extends('layouts.public')

@section('title', 'Kebijakan Privasi — NURISK')

@section('layout-class', 'full-width')

@push('styles')
<style>
    .privacy-container {
        max-width: 800px;
        margin: 0 auto;
        padding: 40px 0;
    }
    .privacy-container h1 {
        font-size: 2rem;
        font-weight: 800;
        color: var(--nu-green-dark);
        margin-bottom: 8px;
        line-height: 1.2;
    }
    .privacy-container .subtitle {
        font-size: 0.95rem;
        color: #888;
        margin-bottom: 40px;
        border-bottom: 2px solid var(--nu-green-light);
        padding-bottom: 16px;
    }
    .privacy-container h2 {
        font-size: 1.35rem;
        font-weight: 700;
        color: var(--nu-green);
        margin-top: 36px;
        margin-bottom: 12px;
    }
    .privacy-container h3 {
        font-size: 1.1rem;
        font-weight: 600;
        color: #2d3748;
        margin-top: 24px;
        margin-bottom: 8px;
    }
    .privacy-container p {
        font-size: 1rem;
        line-height: 1.8;
        color: #4a5568;
        margin-bottom: 16px;
        text-align: justify;
    }
    .privacy-container ul, .privacy-container ol {
        margin: 12px 0 20px 24px;
        line-height: 1.8;
        color: #4a5568;
    }
    .privacy-container li {
        margin-bottom: 8px;
    }
    .privacy-container .highlight-box {
        background: var(--nu-green-light);
        border-left: 4px solid var(--nu-green);
        padding: 16px 20px;
        border-radius: 0 12px 12px 0;
        margin: 20px 0;
    }
    .privacy-container .highlight-box p {
        margin-bottom: 0;
        font-size: 0.95rem;
    }
    .privacy-container .contact-card {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 24px;
        margin-top: 32px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    }
    .privacy-container .contact-card p {
        margin-bottom: 8px;
    }
    .privacy-container .contact-card a {
        color: var(--nu-green);
        font-weight: 600;
        text-decoration: none;
    }
    .privacy-container .contact-card a:hover {
        text-decoration: underline;
    }
    @media (max-width: 640px) {
        .privacy-container { padding: 24px 0; }
        .privacy-container h1 { font-size: 1.6rem; }
    }
</style>
@endpush

@section('content')
<div class="privacy-container">

    <h1>Kebijakan Privasi NURisk</h1>
    <div class="subtitle">Terakhir diperbarui: <strong>[Tanggal Berlaku]</strong></div>

    <p>
        NURisk adalah aplikasi sistem pelaporan dan manajemen penanggulangan bencana yang dikelola oleh
        <strong>LPBI NU Jawa Tengah (Lembaga Penanggulangan Bencana dan Perubahan Iklim Nahdlatul Ulama Jawa Tengah)</strong>.
        Kebijakan Privasi ini menjelaskan bagaimana kami mengumpulkan, menggunakan, menyimpan, dan melindungi data pribadi Anda
        saat menggunakan layanan NURisk, baik melalui situs web maupun aplikasi Android.
    </p>
    <p>
        Dengan mendaftar dan menggunakan aplikasi NURisk, Anda menyetujui praktik yang dijelaskan dalam kebijakan ini.
        Kami berkomitmen untuk melindungi privasi Anda dan memastikan data pribadi Anda diproses secara transparan,
        sah, dan sesuai dengan peraturan perundang-undangan yang berlaku di Indonesia.
    </p>

    <!-- 2. Data yang Kami Kumpulkan -->
    <h2>1. Data yang Kami Kumpulkan</h2>
    <p>Untuk memberikan layanan pelaporan dan manajemen bencana, kami mengumpulkan data sebagai berikut:</p>
    <ul>
        <li><strong>Data Akun:</strong> nama lengkap, alamat email, nomor telepon (opsional), dan foto profil (opsional) yang Anda berikan saat registrasi.</li>
        <li><strong>Data Pelaporan:</strong> lokasi GPS, foto kejadian, dan deskripsi bencana yang Anda unggah saat membuat laporan.</li>
        <li><strong>Data Perangkat:</strong> alamat IP, jenis perangkat, sistem operasi, dan log aktivitas yang dikumpulkan secara otomatis untuk keperluan keamanan dan analisis sistem.</li>
    </ul>

    <div class="highlight-box">
        <p><strong>Mengenai Lokasi GPS:</strong> Lokasi GPS hanya diambil saat Anda memberikan izin secara eksplisit melalui perangkat Anda. Data lokasi digunakan untuk membantu petugas memverifikasi kejadian dan mempercepat respons penanggulangan bencana. Anda dapat menonaktifkan akses lokasi kapan saja melalui pengaturan perangkat.</p>
    </div>

    <div class="highlight-box">
        <p><strong>Mengenai Foto/Dokumen:</strong> Foto dan dokumen yang Anda unggah dalam laporan akan digunakan sebagai bahan verifikasi kejadian bencana oleh petugas/TRC. Informasi ini penting untuk memastikan keakuratan data dan ketepatan respons di lapangan.</p>
    </div>

    <!-- 3. Cara Kami Menggunakan Data -->
    <h2>2. Cara Kami Menggunakan Data</h2>
    <p>Data yang kami kumpulkan digunakan untuk tujuan berikut:</p>
    <ul>
        <li>Memproses registrasi dan autentikasi pengguna.</li>
        <li>Menerima, memproses, dan memverifikasi laporan kejadian bencana yang Anda sampaikan.</li>
        <li>Menghubungi Anda jika diperlukan untuk klarifikasi atau tindak lanjut laporan.</li>
        <li>Menampilkan informasi status laporan secara real-time.</li>
        <li>Menyusun dashboard informasi bencana untuk kepentingan koordinasi penanggulangan bencana.</li>
        <li>Mengirimkan notifikasi terkait perubahan status laporan.</li>
        <li>Meningkatkan kualitas layanan, keamanan sistem, dan pengalaman pengguna.</li>
    </ul>

    <!-- 4. Dasar Pemrosesan dan Persetujuan Pengguna -->
    <h2>3. Dasar Pemrosesan dan Persetujuan Pengguna</h2>
    <p>
        Kami memproses data pribadi Anda berdasarkan:
    </p>
    <ul>
        <li><strong>Persetujuan (consent):</strong> Anda memberikan persetujuan saat mendaftar dan menyetujui Kebijakan Privasi ini.</li>
        <li><strong>Kepentingan publik dan tugas kemanusiaan:</strong> Pemrosesan data dilakukan untuk kepentingan penanggulangan bencana dan penyelamatan jiwa, yang merupakan misi kemanusiaan LPBI NU Jawa Tengah.</li>
        <li><strong>Kewajiban hukum:</strong> Jika diperlukan untuk memenuhi ketentuan peraturan perundang-undangan yang berlaku.</li>
    </ul>
    <p>
        Anda berhak menarik persetujuan kapan saja, namun penarikan tersebut tidak memengaruhi keabsahan pemrosesan data
        yang telah dilakukan sebelumnya.
    </p>

    <!-- 5. Penyimpanan dan Keamanan Data -->
    <h2>4. Penyimpanan dan Keamanan Data</h2>
    <p>
        Seluruh data pribadi Anda disimpan di server yang dikelola dan diamankan oleh pengelola aplikasi. Kami menerapkan
        langkah-langkah keamanan teknis dan organisasi yang memadai untuk melindungi data Anda dari akses tidak sah,
        perubahan, pengungkapan, atau perusakan, termasuk:
    </p>
    <ul>
        <li>Enkripsi data dalam penyimpanan dan saat transmisi.</li>
        <li>Penggunaan protokol HTTPS untuk seluruh komunikasi data.</li>
        <li>Sistem kontrol akses berbasis peran (role-based access control).</li>
        <li>Pemantauan keamanan secara berkala.</li>
    </ul>
    <p>
        Data Anda akan disimpan selama akun Anda masih aktif atau selama diperlukan untuk memenuhi tujuan
        penanggulangan bencana. Data dapat disimpan lebih lama jika diwajibkan oleh ketentuan hukum yang berlaku.
    </p>

    <!-- 6. Pembagian Data kepada Pihak Ketiga -->
    <h2>5. Pembagian Data kepada Pihak Ketiga</h2>
    <p>
        Kami <strong>tidak pernah memperjualbelikan</strong> data pribadi Anda kepada pihak mana pun. Data hanya dapat
        diakses oleh petugas yang berwenang sesuai dengan kebutuhan penanganan bencana.
    </p>
    <p>
        Kami dapat membagikan data Anda kepada pihak ketiga dalam situasi berikut:
    </p>
    <ul>
        <li>Kepada lembaga pemerintah atau badan penanggulangan bencana resmi untuk keperluan koordinasi respons bencana.</li>
        <li>Penyedia layanan teknologi yang bekerja atas nama kami (misalnya penyedia hosting) yang terikat kewajiban kerahasiaan.</li>
        <li>Jika diwajibkan oleh hukum atau perintah pengadilan yang sah.</li>
    </ul>

    <!-- 7. Hak Pengguna -->
    <h2>6. Hak Pengguna</h2>
    <p>Anda memiliki hak-hak berikut atas data pribadi Anda:</p>
    <ul>
        <li><strong>Hak untuk mengetahui:</strong> Mengetahui data apa saja yang kami kumpulkan dan bagaimana data tersebut digunakan (sebagaimana dijelaskan dalam kebijakan ini).</li>
        <li><strong>Hak untuk mengakses:</strong> Melihat data akun Anda melalui halaman profil di dalam aplikasi.</li>
        <li><strong>Hak untuk memperbaiki:</strong> Memperbaiki data akun yang tidak akurat atau tidak lengkap melalui menu pengaturan profil.</li>
        <li><strong>Hak untuk menghapus:</strong> Mengajukan penghapusan akun dan data pribadi Anda.</li>
        <li><strong>Hak untuk menarik persetujuan:</strong> Menarik persetujuan pemrosesan data kapan saja.</li>
        <li><strong>Hak untuk mengajukan keluhan:</strong> Mengajukan keluhan terkait pemrosesan data pribadi kepada kami melalui kontak yang tersedia.</li>
    </ul>

    <!-- 8. Penghapusan Akun dan Data -->
    <h2>7. Penghapusan Akun dan Data</h2>
    <p>
        Anda dapat mengajukan penghapusan akun dan data pribadi melalui dua cara:
    </p>
    <ol>
        <li><strong>Melalui menu aplikasi:</strong> Buka halaman Profil &gt; Pengaturan Akun &gt; Hapus Akun, lalu ikuti petunjuk yang diberikan.</li>
        <li><strong>Menghubungi tim privasi:</strong> Kirim permohonan penghapusan melalui email ke <a href="mailto:privasi@nurisk.id">privasi@nurisk.id</a> dengan subjek "Permohonan Penghapusan Akun".</li>
    </ol>
    <p>
        Permohonan penghapusan akan kami proses dalam waktu <strong>14 hari kerja</strong> setelah verifikasi identitas Anda.
        Beberapa data tertentu mungkin tetap kami simpan jika diwajibkan oleh ketentuan hukum yang berlaku,
        misalnya untuk keperluan audit atau kepatuhan terhadap peraturan.
    </p>

    <!-- 9. Cookie dan Teknologi Serupa -->
    <h2>8. Cookie dan Teknologi Serupa</h2>
    <p>
        Saat Anda mengakses situs web NURisk, kami menggunakan cookie dan teknologi serupa untuk:
    </p>
    <ul>
        <li>Menjaga sesi login Anda tetap aman.</li>
        <li>Mengingat preferensi pengaturan Anda.</li>
        <li>Menganalisis pola penggunaan untuk meningkatkan layanan.</li>
    </ul>
    <p>
        Anda dapat mengatur preferensi cookie melalui pengaturan peramban Anda. Jika Anda menonaktifkan cookie,
        beberapa fitur situs web mungkin tidak berfungsi secara optimal. Cookie yang kami gunakan tidak digunakan
        untuk melacak aktivitas Anda di situs lain.
    </p>

    <!-- 10. Privasi & Standar Keselamatan Anak (CSAM/CSAE Policy) -->
    <h2 id="child-safety">9. Privasi & Standar Keselamatan Anak (Child Safety Standards)</h2>
    <p>
        Layanan NURisk tidak ditujukan untuk anak-anak di bawah usia 13 tahun. Kami menerapkan kebijakan 
        <strong>toleransi nol (zero tolerance)</strong> terhadap segala bentuk Materi Pelecehan Seksual Anak (CSAM) 
        dan Eksploitasi Terhadap Anak (CSAE).
    </p>
    <ul>
        <li><strong>Moderasi Konten:</strong> Seluruh laporan dan foto publik divalidasi oleh petugas operasional sebelum dipublikasikan.</li>
        <li><strong>Pelaporan CSAM/CSAE:</strong> Pengguna atau pihak manapun dapat melaporkan temuan pelanggaran keselamatan anak ke Point of Contact (POC) kami di <a href="mailto:yudi.asmui@gmail.com">yudi.asmui@gmail.com</a> atau <a href="mailto:privasi@nurisk.id">privasi@nurisk.id</a>.</li>
        <li><strong>Penindakan & Pelaporan Otoritas:</strong> Setiap indikasi pelanggaran CSAM/CSAE akan langsung dihapus dan dilaporkan kepada Kepolisian Negara Republik Indonesia (POLRI / Satgas Siber) serta lembaga penegak hukum terkait.</li>
    </ul>

    <!-- 11. Perubahan Kebijakan Privasi -->
    <h2>10. Perubahan Kebijakan Privasi</h2>
    <p>
        Kebijakan Privasi ini dapat diperbarui dari waktu ke waktu untuk mencerminkan perubahan pada praktik pemrosesan
        data kami atau perubahan ketentuan hukum yang berlaku. Setiap perubahan akan diberitahukan melalui:
    </p>
    <ul>
        <li>Pemberitahuan di dalam aplikasi NURisk.</li>
        <li>Pembaruan tanggal "Terakhir diperbarui" di bagian atas halaman ini.</li>
    </ul>
    <p>
        Kami menganjurkan Anda untuk meninjau halaman ini secara berkala. Dengan terus menggunakan layanan NURisk
        setelah perubahan diberlakukan, Anda dianggap menyetujui perubahan tersebut.
    </p>

    <!-- 12. Kontak Kami -->
    <h2>11. Kontak Kami</h2>
    <p>
        Jika Anda memiliki pertanyaan, keluhan, atau ingin menggunakan hak-hak Anda terkait data pribadi,
        jangan ragu untuk menghubungi kami:
    </p>

    <div class="contact-card">
        <p><strong>LPBI NU Jawa Tengah</strong></p>
        <p>Email: <a href="mailto:privasi@nurisk.id">privasi@nurisk.id</a></p>
        <p>Website: <a href="https://nurisk.org" target="_blank">https://nurisk.org</a></p>
        <p style="margin-top: 12px; font-size: 0.9rem; color: #888;">
            Kami akan merespons pertanyaan atau keluhan Anda dalam waktu 7&ndash;14 hari kerja.
        </p>
    </div>

</div>
@endsection
