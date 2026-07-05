<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Benchmark - {{ $surat->nomor_surat_resmi }}</title>
    <style>
        body { font-family: 'DejaVu Serif', serif; font-size: 12pt; line-height: 1.6; color: #000; margin: 2cm; }
        h1 { text-align: center; font-size: 16pt; margin-bottom: 30pt; }
        .info { margin-bottom: 20pt; }
        .info td { padding: 4pt 10pt; }
        .content { text-align: justify; }
        .content p { text-indent: 1.5cm; margin: 8pt 0; }
        .benchmark { text-align: center; margin-top: 40pt; font-size: 10pt; color: #666; }
    </style>
</head>
<body>
    <h1>BENCHMARK PDF GENERATION</h1>
    <table class="info">
        <tr><td>Nomor Surat</td><td>: {{ $surat->nomor_surat_resmi }}</td></tr>
        <tr><td>Perihal</td><td>: {{ $surat->perihal }}</td></tr>
        <tr><td>Tanggal</td><td>: {{ $surat->tgl_terbit?->format('d F Y') ?? '-' }}</td></tr>
        <tr><td>Status</td><td>: {{ $surat->status_surat }}</td></tr>
    </table>
    <div class="content">
        <p>Dokumen ini dibuat secara otomatis untuk keperluan benchmark pengujian antrean (queue stress test) pada sistem.</p>
        <p>{{ $surat->isi_surat_snapshot ?? 'Isi surat tidak tersedia.' }}</p>
        @for ($i = 0; $i < 3; $i++)
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
        @endfor
    </div>
    <div class="benchmark">
        <p>Generated at: {{ now()->format('Y-m-d H:i:s') }} | Job ID: {{ $surat->id_surat }}</p>
    </div>
</body>
</html>
