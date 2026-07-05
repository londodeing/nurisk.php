<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $surat->nomor_surat_resmi }}</title>
    <style>
        body { font-family: 'DejaVu Serif', serif; font-size: 12pt; line-height: 1.6; color: #000; margin: 2cm 2.5cm; }
        .header { text-align: center; margin-bottom: 24pt; }
        .header .org { font-size: 16pt; font-weight: bold; text-transform: uppercase; }
        .header .sub-org { font-size: 12pt; font-weight: bold; }
        .header .alamat { font-size: 9pt; }
        .header .garis { border-top: 3px solid #000; margin-top: 6pt; }
        .header .garis2 { border-top: 1px solid #000; margin-top: 2pt; }
        .nomor { text-align: center; margin-bottom: 20pt; }
        .nomor p { margin: 2pt 0; }
        .content { text-align: justify; margin-bottom: 20pt; }
        .content p { margin: 6pt 0; }
        .content .indent { text-indent: 1.5cm; }
        .ttd { margin-top: 40pt; text-align: right; float: right; width: 60%; }
        .ttd p { margin: 2pt 0; text-align: center; }
        .ttd .kota-tgl { margin-bottom: 4pt; }
        .ttd .jabatan { margin-bottom: 60pt; }
        .ttd .nama { font-weight: bold; text-decoration: underline; }
        .tembusan { margin-top: 30pt; font-size: 10pt; clear: both; }
        .tembusan ol { padding-left: 20pt; margin-top: 2pt; }
        .page-break { page-break-before: always; }
        table { border-collapse: collapse; width: 100%; }
        table td { vertical-align: top; padding: 2px 4px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="org">NAHDLATUL ULAMA</div>
        <div class="sub-org">PENGURUS CABANG {{ strtoupper($surat->insiden?->pcnu?->nama_pcnu ?? 'KABUPATEN/KOTA') }}</div>
        <div class="alamat">Alamat: Sekretariat PCNU, {{ $surat->insiden?->pcnu?->nama_pcnu ?? 'Kabupaten/Kota' }}</div>
        <div class="garis"></div>
        <div class="garis2"></div>
    </div>

    @if($surat->jenisSurat?->nama_jenis === 'Surat Tugas' || $surat->jenisSurat?->kode_jenis === 'ST')
        <!-- KOP SURAT TUGAS -->
        <div class="nomor" style="margin-top: 30pt; margin-bottom: 30pt;">
            <p style="font-size: 14pt; font-weight: bold; text-decoration: underline; text-transform: uppercase;">SURAT TUGAS</p>
            <p>Nomor: {{ $surat->nomor_surat_resmi }}</p>
        </div>

        <div class="content">
            <p>Yang bertanda tangan di bawah ini:</p>
            <table style="margin-left: 1cm; margin-bottom: 15pt; width: 90%;">
                <tr><td style="width: 120px;">Nama</td><td>: <strong>{{ $surat->penandatangan?->profil?->nama_lengkap ?? $surat->penandatangan?->no_hp ?? 'Pejabat Berwenang' }}</strong></td></tr>
                <tr><td>Jabatan</td><td>: {{ $surat->jabatanTtd?->nama_jabatan ?? 'Pengurus PCNU' }}</td></tr>
            </table>

            <p>{!! nl2br(e($surat->isi_surat_snapshot)) !!}</p>

            <p>Demikian surat tugas ini dibuat untuk dilaksanakan dengan penuh tanggung jawab dan melaporkan hasilnya kepada atasan secara berkala.</p>
        </div>
    @else
        <!-- KOP SURAT BIASA -->
        <div class="nomor" style="text-align: left; margin-bottom: 20pt;">
            <table>
                <tr><td style="width: 80px;">Nomor</td><td>: {{ $surat->nomor_surat_resmi }}</td></tr>
                <tr><td>Sifat</td><td>: Penting</td></tr>
                <tr><td>Lampiran</td><td>: -</td></tr>
                <tr><td>Perihal</td><td>: <strong>{{ $surat->perihal }}</strong></td></tr>
            </table>
        </div>

        <div class="content">
            <p>Yang terhormat,</p>
            @if ($surat->tembusan->count())
                @foreach ($surat->tembusan as $tembusan)
                    <p><strong>{{ $tembusan->nama_pihak }}</strong></p>
                @endforeach
            @else
                <p><strong>[Penerima]</strong></p>
            @endif
            <p>di - Tempat</p>

            <p class="indent"><em>Assalamu&rsquo;alaikum Wr. Wb.</em></p>

            <p class="indent">{!! nl2br(e($surat->isi_surat_snapshot ?? 'Isi surat tidak tersedia.')) !!}</p>

            <p class="indent"><em>Wassalamu&rsquo;alaikum Wr. Wb.</em></p>
        </div>
    @endif

    <div class="ttd">
        <p class="kota-tgl">{{ $surat->insiden?->pcnu?->nama_pcnu ?? 'Kota' }}, {{ $surat->tgl_terbit?->format('d F Y') ?? now()->format('d F Y') }}</p>
        <p class="jabatan">{{ $surat->jabatanTtd?->nama_jabatan ?? 'Pengurus PCNU' }}</p>
        <br><br><br>
        <p class="nama">{{ $surat->penandatangan?->profil?->nama_lengkap ?? $surat->penandatangan?->no_hp ?? '_______________________' }}</p>
    </div>

    @if ($surat->tembusan->count())
        <div class="tembusan">
            <p><strong>Tembusan:</strong></p>
            <ol>
                @foreach ($surat->tembusan as $tembusan)
                    <li>{{ $tembusan->nama_pihak }}</li>
                @endforeach
            </ol>
        </div>
    @endif

    @if($surat->insiden?->laporanAsal && ($surat->jenisSurat?->nama_jenis === 'Surat Tugas' || $surat->jenisSurat?->kode_jenis === 'ST'))
        <div class="page-break"></div>
        <div class="header" style="margin-bottom: 10pt;">
            <div class="org">LAMPIRAN SURAT TUGAS</div>
            <div class="sub-org">DATA LAPORAN KEJADIAN AWAL</div>
            <div class="garis"></div>
        </div>

        <div class="content" style="font-size: 11pt;">
            <table style="width: 100%; border-collapse: collapse; margin-top: 10pt;">
                <tr>
                    <td style="width: 30%; padding: 4px; font-weight: bold;">Kode Kejadian</td>
                    <td style="width: 5%; padding: 4px;">:</td>
                    <td style="padding: 4px;">{{ $surat->insiden->laporanAsal->kode_kejadian }}</td>
                </tr>
                <tr>
                    <td style="padding: 4px; font-weight: bold;">Nama Pelapor</td>
                    <td style="padding: 4px;">:</td>
                    <td style="padding: 4px;">{{ $surat->insiden->laporanAsal->nama_pelapor ?? '-' }}</td>
                </tr>
                <tr>
                    <td style="padding: 4px; font-weight: bold;">Kontak (No. HP)</td>
                    <td style="padding: 4px;">:</td>
                    <td style="padding: 4px;">{{ $surat->insiden->laporanAsal->hp_pelapor ?? '-' }}</td>
                </tr>
                <tr>
                    <td style="padding: 4px; font-weight: bold;">Waktu Kejadian</td>
                    <td style="padding: 4px;">:</td>
                    <td style="padding: 4px;">{{ $surat->insiden->laporanAsal->waktu_kejadian ? $surat->insiden->laporanAsal->waktu_kejadian->format('d/m/Y H:i') : '-' }}</td>
                </tr>
                <tr>
                    <td style="padding: 4px; font-weight: bold;">Jenis Bencana</td>
                    <td style="padding: 4px;">:</td>
                    <td style="padding: 4px;">{{ $surat->insiden->laporanAsal->jenisBencana->nama_bencana ?? '-' }}</td>
                </tr>
                <tr>
                    <td style="padding: 4px; font-weight: bold;">Keterangan Situasi</td>
                    <td style="padding: 4px;">:</td>
                    <td style="padding: 4px;">{{ $surat->insiden->laporanAsal->keterangan_situasi ?? '-' }}</td>
                </tr>
                <tr>
                    <td style="padding: 4px; font-weight: bold;">Wilayah</td>
                    <td style="padding: 4px;">:</td>
                    <td style="padding: 4px;">
                        {{ $surat->insiden->laporanAsal->desa->nama_desa ?? '-' }},
                        {{ $surat->insiden->laporanAsal->kecamatan->nama_kec ?? '-' }},
                        {{ $surat->insiden->laporanAsal->kabupaten->nama_kab ?? '-' }}
                    </td>
                </tr>
                <tr>
                    <td style="padding: 4px; font-weight: bold;">Alamat Lengkap</td>
                    <td style="padding: 4px;">:</td>
                    <td style="padding: 4px;">{{ $surat->insiden->laporanAsal->alamat_lengkap ?? '-' }}</td>
                </tr>
                <tr>
                    <td style="padding: 4px; font-weight: bold;">Titik Kenal</td>
                    <td style="padding: 4px;">:</td>
                    <td style="padding: 4px;">{{ $surat->insiden->laporanAsal->titik_kenal ?? '-' }}</td>
                </tr>
                <tr>
                    <td style="padding: 4px; font-weight: bold;">Koordinat Lokasi</td>
                    <td style="padding: 4px;">:</td>
                    <td style="padding: 4px;">
                        @if($surat->insiden->laporanAsal->latitude && $surat->insiden->laporanAsal->longitude)
                            {{ $surat->insiden->laporanAsal->latitude }}, {{ $surat->insiden->laporanAsal->longitude }}
                        @else
                            -
                        @endif
                    </td>
                </tr>
            </table>
            
            <p style="margin-top: 20pt; font-style: italic; color: #555; text-align: center;">Data di atas merupakan laporan awal yang diterima sistem. Mohon berkoordinasi dengan pelapor dan melakukan assessment lanjutan di lapangan.</p>
        </div>
    @endif
</body>
</html>
