<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sitrep: {{ $sitrep->nomor_sitrep ?? '—' }}</title>
    <style>
        body { font-family: 'DejaVu Serif', serif; font-size: 11pt; line-height: 1.5; }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; }
        .header h2 { margin: 0; font-size: 16pt; }
        .header h3 { margin: 5px 0; font-size: 13pt; }
        .header small { font-size: 9pt; color: #666; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { border: 1px solid #999; padding: 6px 8px; text-align: left; font-size: 10pt; }
        th { background: #eee; font-weight: bold; }
        .info-table td:first-child { width: 30%; font-weight: bold; }
        .section-title { font-size: 12pt; font-weight: bold; margin-top: 20px; padding-bottom: 3px; border-bottom: 1px solid #ccc; }
        .footer { margin-top: 30px; text-align: right; font-size: 9pt; color: #666; border-top: 1px solid #ccc; padding-top: 8px; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 3px; font-size: 9pt; }
        .bg-danger { background: #dc3545; color: #fff; }
        .bg-warning { background: #ffc107; color: #333; }
        .bg-secondary { background: #6c757d; color: #fff; }
        .bg-info { background: #0dcaf0; color: #333; }
        .bg-primary { background: #0d6efd; color: #fff; }
    </style>
</head>
<body>
    <div class="header">
        <h2>SITUATION REPORT (SITREP)</h2>
        <h3>NU Peduli — {{ $sitrep->insiden?->pcnu?->nama_pcnu ?? 'NU' }}</h3>
        <small>Dokumen ini dicetak pada {{ now()->format('d F Y H:i') }}</small>
    </div>

    <table class="info-table">
        <tr><td>Nomor Sitrep</td><td>{{ $sitrep->nomor_sitrep ?? '—' }}</td></tr>
        <tr><td>Kode Insiden</td><td>{{ $sitrep->insiden?->kode_kejadian ?? '—' }}</td></tr>
        <tr><td>Periode</td><td>{{ $sitrep->periode_sitrep ?? '—' }}</td></tr>
        <tr><td>Waktu Sitrep</td><td>{{ $sitrep->waktu_sitrep?->format('d F Y H:i') }}</td></tr>
        <tr><td>Dibuat Oleh</td><td>{{ $sitrep->pembuat?->profil?->nama_lengkap ?? '—' }}</td></tr>
        <tr><td>Jumlah Personel</td><td>{{ $sitrep->jumlah_personel ?? 0 }}</td></tr>
        <tr><td>Klaster Aktif</td><td>{{ $sitrep->jumlah_klaster_aktif ?? 0 }}</td></tr>
    </table>

    <div class="section-title">Catatan Situasi</div>
    <p>{{ $sitrep->catatan ?? 'Tidak ada catatan.' }}</p>

    @if($sitrep->dampak)
    <div class="section-title">Dampak Bencana</div>
    <table>
        <tr>
            <th>Meninggal</th>
            <th>Hilang</th>
            <th>Luka Berat</th>
            <th>Luka Ringan</th>
            <th>Mengungsi</th>
        </tr>
        <tr>
            <td>{{ $sitrep->dampak->meninggal ?? 0 }}</td>
            <td>{{ $sitrep->dampak->hilang ?? 0 }}</td>
            <td>{{ $sitrep->dampak->luka_berat ?? 0 }}</td>
            <td>{{ $sitrep->dampak->luka_ringan ?? 0 }}</td>
            <td>{{ $sitrep->dampak->mengungsi ?? 0 }}</td>
        </tr>
    </table>
    @endif

    @if($sitrep->kebutuhan && $sitrep->kebutuhan->count() > 0)
    <div class="section-title">Kebutuhan Mendesak</div>
    <table>
        <tr><th>Kebutuhan</th><th>Jumlah</th><th>Satuan</th></tr>
        @foreach($sitrep->kebutuhan as $k)
        <tr><td>{{ $k->nama_kebutuhan }}</td><td>{{ $k->jumlah }}</td><td>{{ $k->satuan }}</td></tr>
        @endforeach
    </table>
    @endif

    <div class="footer">
        Dicetak dari sistem NURISK — {{ config('app.url') }}
    </div>
</body>
</html>
