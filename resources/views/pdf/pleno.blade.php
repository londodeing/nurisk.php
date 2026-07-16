<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Hasil Pleno - {{ $pleno->nomor_pleno }}</title>
    <style>
        body { font-family: 'DejaVu Serif', serif; font-size: 11pt; line-height: 1.5; color: #000; margin: 1.5cm 2cm; }
        .header { text-align: center; margin-bottom: 20pt; }
        .header .org { font-size: 16pt; font-weight: bold; text-transform: uppercase; }
        .header .sub-org { font-size: 12pt; font-weight: bold; }
        .header .alamat { font-size: 9pt; }
        .header .garis { border-top: 3px solid #000; margin-top: 6pt; }
        .header .garis2 { border-top: 1px solid #000; margin-top: 2pt; }
        .judul-dokumen { text-align: center; margin-bottom: 20pt; }
        .judul-dokumen h2 { margin: 0; font-size: 13pt; text-decoration: underline; text-transform: uppercase; }
        .judul-dokumen p { margin: 2pt 0; }
        
        .section-title { font-weight: bold; font-size: 11pt; border-bottom: 1px solid #000; padding-bottom: 2pt; margin-top: 15pt; margin-bottom: 8pt; text-transform: uppercase; }
        
        table { border-collapse: collapse; width: 100%; margin-bottom: 10pt; }
        table td { vertical-align: top; padding: 3px 5px; }
        
        .table-data { border: 1px solid #000; }
        .table-data th { border: 1px solid #000; background-color: #f2f2f2; padding: 5px; text-align: left; font-size: 10pt; }
        .table-data td { border: 1px solid #000; padding: 5px; font-size: 10pt; }
        
        .page-break { page-break-before: always; }
        
        .signature-container { margin-top: 30pt; width: 100%; }
        .signature-box { width: 50%; float: left; text-align: center; }
        .signature-box p { margin: 2pt 0; }
        .signature-box .space { margin-bottom: 50pt; }
        .signature-box .nama { font-weight: bold; text-decoration: underline; }
    </style>
</head>
<body>
    <!-- KOP SURAT -->
    <div class="header">
        <div class="org">NAHDLATUL ULAMA</div>
        <div class="sub-org">PENGURUS CABANG {{ strtoupper($pleno->insiden?->pcnu?->nama_pcnu ?? 'KABUPATEN/KOTA') }}</div>
        <div class="alamat">Alamat: Sekretariat PCNU, {{ $pleno->insiden?->pcnu?->nama_pcnu ?? 'Kabupaten/Kota' }}</div>
        <div class="garis"></div>
        <div class="garis2"></div>
    </div>

    <!-- JUDUL DOKUMEN -->
    <div class="judul-dokumen">
        <h2>SURAT KEPUTUSAN HASIL RAPAT PLENO</h2>
        <p>Nomor: {{ $pleno->nomor_pleno ?? '[Belum Ada Nomor]' }}</p>
    </div>

    <!-- INFORMASI UMUM -->
    <div class="section-title">I. INFORMASI UMUM RAPAT PLENO</div>
    <table>
        <tr>
            <td style="width: 150px;">Hari / Tanggal</td>
            <td style="width: 10px;">:</td>
            <td>{{ \Carbon\Carbon::parse($pleno->waktu_pleno)->isoFormat('dddd, D MMMM Y') }}</td>
        </tr>
        <tr>
            <td>Waktu Pelaksanaan</td>
            <td>:</td>
            <td>{{ \Carbon\Carbon::parse($pleno->waktu_pleno)->format('H:i') }} WIB s.d Selesai</td>
        </tr>
        <tr>
            <td>Tempat / Lokasi</td>
            <td>:</td>
            <td>{{ $pleno->lokasi_pleno }}</td>
        </tr>
        <tr>
            <td>Jenis Pleno</td>
            <td>:</td>
            <td>{{ ucwords(str_replace('_', ' ', $pleno->jenis_pleno)) }}</td>
        </tr>
        <tr>
            <td>Pimpinan Pleno</td>
            <td>:</td>
            <td><strong>{{ $pleno->pimpinan?->profil?->nama_lengkap ?? $pleno->pimpinan?->no_hp ?? '-' }}</strong></td>
        </tr>
        <tr>
            <td>Notulis Pleno</td>
            <td>:</td>
            <td><strong>{{ $pleno->notulis?->profil?->nama_lengkap ?? $pleno->notulis?->no_hp ?? '-' }}</strong></td>
        </tr>
    </table>

    <!-- REFERENSI KEJADIAN & ASSESSMENT -->
    <div class="section-title">II. ACUAN SITUASI KEJADIAN & ASSESSMENT</div>
    <p style="margin-top: 0;">Rapat pleno diselenggarakan berdasarkan data laporan kejadian awal dan hasil assessment lapangan berikut:</p>
    
    <table class="table-data">
        <thead>
            <tr>
                <th style="width: 30%;">Parameter Situasi</th>
                <th>Detail Informasi</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Kode Kejadian</strong></td>
                <td>{{ $pleno->insiden?->kode_kejadian }}</td>
            </tr>
            <tr>
                <td><strong>Jenis Bencana</strong></td>
                <td>{{ $pleno->insiden?->jenisBencana?->nama_bencana ?? $pleno->insiden?->laporanAsal?->jenisBencana?->nama_bencana ?? '-' }}</td>
            </tr>
            <tr>
                <td><strong>Waktu Kejadian</strong></td>
                <td>{{ $pleno->insiden?->laporanAsal?->waktu_kejadian ? \Carbon\Carbon::parse($pleno->insiden->laporanAsal->waktu_kejadian)->isoFormat('D MMMM Y HH:i') : '-' }} WIB</td>
            </tr>
            <tr>
                <td><strong>Lokasi Terdampak</strong></td>
                <td>
                    {{ $pleno->insiden?->laporanAsal?->desa?->nama_desa ?? '-' }}, 
                    Kec. {{ $pleno->insiden?->laporanAsal?->kecamatan?->nama_kec ?? '-' }}, 
                    {{ $pleno->insiden?->laporanAsal?->kabupaten?->nama_kab ?? '-' }}
                </td>
            </tr>
            @if($assessment = $pleno->insiden?->assessments()->where('is_latest', true)->first())
                <tr>
                    <td><strong>Penyebab Kejadian</strong></td>
                    <td>{{ $assessment->biodataKejadian?->penyebab ?? '-' }}</td>
                </tr>
                <tr>
                    <td><strong>Dampak Korban Jiwa</strong></td>
                    <td>
                        Meninggal: {{ $assessment->dampakManusiaV2?->meninggal ?? $assessment->dampakManusia?->meninggal ?? 0 }} Jiwa, 
                        Luka-luka: {{ $assessment->dampakManusiaV2?->luka_ringan + $assessment->dampakManusiaV2?->luka_sedang + $assessment->dampakManusiaV2?->luka_berat ?? 0 }} Jiwa, 
                        Mengungsi: {{ $assessment->dampakManusiaV2?->mengungsi_jiwa ?? 0 }} Jiwa ({{ $assessment->dampakManusiaV2?->mengungsi_kk ?? 0 }} KK)
                    </td>
                </tr>
                <tr>
                    <td><strong>Dampak Infrastruktur</strong></td>
                    <td>
                        Rumah RB: {{ $assessment->dampakInfrastruktur?->rumah_rb ?? 0 }}, 
                        Rumah RS: {{ $assessment->dampakInfrastruktur?->rumah_rs ?? 0 }}, 
                        Rumah RR: {{ $assessment->dampakInfrastruktur?->rumah_rr ?? 0 }} unit.
                        Fasilitas Umum: {{ $assessment->dampakInfrastruktur?->fasilitas_pendidikan ? 'Sekolah' : '' }} {{ $assessment->dampakInfrastruktur?->fasilitas_ibadah ? 'Tempat Ibadah' : '' }} {{ $assessment->dampakInfrastruktur?->fasilitas_kesehatan ? 'Fasilitas Kesehatan' : '' }}
                    </td>
                </tr>
            @else
                <tr>
                    <td colspan="2" style="text-align: center; font-style: italic; color: #555;">Data hasil assessment lapangan belum terlampir.</td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="page-break"></div>

    <!-- DAFTAR KEPUTUSAN RAPAT -->
    <div class="section-title">III. KEPUTUSAN & RENCANA TINDAK LANJUT</div>
    <p style="margin-top: 0;">Berdasarkan hasil musyawarah pleno, diputuskan beberapa langkah operasional penanganan darurat berikut:</p>

    <table class="table-data">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 25%;">Kategori Objek</th>
                <th style="width: 50%;">Deskripsi Keputusan</th>
                <th style="width: 20%;">Status Pelaksanaan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pleno->keputusan as $index => $kep)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ ucwords(str_replace('_', ' ', $kep->kategori_objek)) }}</strong><br>
                        <span style="font-size: 8pt; color: #555;">{{ ucwords(str_replace('_', ' ', $kep->jenis_keputusan)) }}</span>
                    </td>
                    <td>{!! nl2br(e($kep->deskripsi_keputusan)) !!}</td>
                    <td>{{ ucwords(str_replace('_', ' ', $kep->status_pelaksanaan)) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align: center; font-style: italic; color: #555;">Belum ada hasil keputusan rapat yang didaftarkan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- TANDA TANGAN -->
    <div class="signature-container">
        <div class="signature-box">
            <p>Pimpinan Pleno,</p>
            <div class="space"></div>
            <p class="nama">{{ $pleno->pimpinan?->profil?->nama_lengkap ?? $pleno->pimpinan?->no_hp ?? '_______________________' }}</p>
            <p>NIP/ID: {{ $pleno->pimpinan_pleno }}</p>
        </div>
        <div class="signature-box">
            <p>Notulis Pleno,</p>
            <div class="space"></div>
            <p class="nama">{{ $pleno->notulis?->profil?->nama_lengkap ?? $pleno->notulis?->no_hp ?? '_______________________' }}</p>
            <p>NIP/ID: {{ $pleno->notulis_pleno }}</p>
        </div>
    </div>
</body>
</html>
