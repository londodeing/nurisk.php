@extends('layouts.guest')

@section('title', 'Standar Keselamatan Anak & Kebijakan Anti-CSAM/CSAE')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-4xl">
    <div class="bg-white rounded-lg shadow p-6 md:p-8">
        <h1 class="text-3xl font-bold mb-6 text-gray-800">Standar Keselamatan Anak (Child Safety Standards)</h1>
        
        <div class="prose prose-green max-w-none">
            <p class="text-gray-600 mb-4">Terakhir diperbarui: {{ date('d F Y') }}</p>

            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
                <p class="font-semibold text-red-800">Toleransi Nol Terhadap Eksploitasi & Pelecehan Seksual Anak (CSAM/CSAE)</p>
                <p class="text-sm text-red-700 mt-1">LPBI NU Jawa Tengah dan platform NURisk menerapkan kebijakan toleransi nol (zero tolerance) terhadap segala bentuk Materi Pelecehan Seksual Anak (CSAM) dan Eksploitasi dan Pelecehan Seksual Terhadap Anak (CSAE).</p>
            </div>

            <h2 class="text-xl font-semibold mt-6 mb-3">1. Komitmen Keselamatan Anak</h2>
            <p>NURisk adalah aplikasi sistem informasi risiko dan penanggulangan bencana publik. Kami berkomitmen menyediakan lingkungan yang aman bagi seluruh pengguna, termasuk anak-anak dan keluarga yang terdampak bencana.</p>

            <h2 class="text-xl font-semibold mt-6 mb-3">2. Larangan Konten & Perilaku (CSAM/CSAE)</h2>
            <p>Dilarang keras mengunggah, membagikan, menyimpan, atau mentransmisikan:</p>
            <ul class="list-disc pl-6 space-y-1 my-2">
                <li>Materi visual atau teks yang memperlihatkan eksploitasi atau pelecehan seksual terhadap anak.</li>
                <li>Konten yang membahayakan keselamatan fisik, emosional, atau psikologis anak.</li>
                <li>Pesan atau interaksi yang mengarah pada tindakan grooming atau kontak tidak pantas dengan anak.</li>
            </ul>

            <h2 class="text-xl font-semibold mt-6 mb-3">3. Mekanisme Pencegahan & Moderasi</h2>
            <p>Kami menerapkan mekanisme pengawasan dan moderasi aktif:</p>
            <ul class="list-disc pl-6 space-y-1 my-2">
                <li><strong>Moderasi Konten Laporan:</strong> Setiap foto dan teks laporan publik yang diunggah dikaji dan divalidasi oleh petugas operasional sebelum dipublikasikan.</li>
                <li><strong>Penghapusan Seketika:</strong> Konten yang terindikasi melanggar standar keselamatan anak akan segera dihapus dan diblokir secara permanen.</li>
            </ul>

            <h2 class="text-xl font-semibold mt-6 mb-3">4. Pelaporan Pelanggaran & Kontak Person of Contact (POC)</h2>
            <p>Pengguna dapat melaporkan temuan konten atau perilaku yang mencurigakan melalui:</p>
            <ul class="list-disc pl-6 space-y-1 my-2">
                <li><strong>Point of Contact (POC) Keselamatan Anak:</strong> <a href="mailto:yudi.asmui@gmail.com" class="text-green-600 font-semibold hover:underline">yudi.asmui@gmail.com</a></li>
                <li><strong>Email Privasi & Moderasi:</strong> <a href="mailto:privasi@nurisk.id" class="text-green-600 hover:underline">privasi@nurisk.id</a></li>
            </ul>

            <h2 class="text-xl font-semibold mt-6 mb-3">5. Pelaporan kepada Otoritas Hukum</h2>
            <p>Setiap pelanggaran yang melibatkan CSAM/CSAE akan langsung dilaporkan kepada pihak berwajib (Kepolisian Negara Republik Indonesia - POLRI / Satgas Siber) serta lembaga perlindungan anak nasional yang relevan, sesuai dengan ketentuan hukum yang berlaku di Republik Indonesia.</p>
        </div>
        
        <div class="mt-8 pt-6 border-t border-gray-200">
            <a href="/" class="text-green-600 hover:text-green-800 font-medium">&larr; Kembali ke Beranda</a>
        </div>
    </div>
</div>
@endsection
