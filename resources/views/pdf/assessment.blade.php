<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Assessment - {{ $assessment->id_assessment_utama }}</title>
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
        <div class="sub-org">PCNU {{ strtoupper($assessment->insiden?->pcnu?->nama_pcnu ?? 'KABUPATEN/KOTA') }} — NU PEDULI</div>
        <div class="alamat">Alamat: Sekretariat PCNU, {{ $assessment->insiden?->pcnu?->nama_pcnu ?? 'Kabupaten/Kota' }}</div>
        <div class="garis"></div>
        <div class="garis2"></div>
    </div>

    <!-- JUDUL DOKUMEN -->
    <div class="judul-dokumen">
        <h2>LAPORAN ASSESSMENT SITUASI DARURAT</h2>
        <p>Kode Laporan: ASM-{{ $assessment->id_assessment_utama }}</p>
    </div>

    <!-- INFORMASI UMUM -->
    <div class="section-title">I. INFORMASI ASSESSMENT</div>
    <table>
        <tr>
            <td style="width: 150px;">Jenis Laporan</td>
            <td style="width: 10px;">:</td>
            <td>{{ $assessment->jenis_laporan === 'kaji_cepat' ? 'Kaji Cepat' : 'Lanjutan' }}</td>
        </tr>
        <tr>
            <td>Waktu Assessment</td>
            <td>:</td>
            <td>{{ \Carbon\Carbon::parse($assessment->waktu_assesment)->isoFormat('dddd, D MMMM Y HH:i') }} WIB</td>
        </tr>
        <tr>
            <td>Petugas Asesor</td>
            <td>:</td>
            <td><strong>{{ $assessment->petugas?->profil?->nama_lengkap ?? $assessment->nama_petugas ?? '-' }}</strong></td>
        </tr>
        <tr>
            <td>Wilayah Terdampak</td>
            <td>:</td>
            <td>
                {{ $assessment->lokasiDetail?->desa?->nama_desa ?? '-' }}, 
                Kec. {{ $assessment->lokasiDetail?->kecamatan?->nama_kec ?? '-' }}, 
                {{ $assessment->insiden?->pcnu?->nama_pcnu ?? '-' }}
            </td>
        </tr>
        <tr>
            <td>Kode Insiden Acuan</td>
            <td>:</td>
            <td>{{ $assessment->insiden?->kode_kejadian }}</td>
        </tr>
    </table>

    <!-- RINGKASAN SITUASI -->
    @if($assessment->biodataKejadian)
    <div class="section-title">II. RINGKASAN SITUASI</div>
    <table>
        <tr>
            <td style="width: 150px;">Kronologi Singkat</td>
            <td style="width: 10px;">:</td>
            <td style="text-align: justify;">{{ $assessment->biodataKejadian->kronologi_singkat ?? '-' }}</td>
        </tr>
        <tr>
            <td>Penyebab Utama</td>
            <td>:</td>
            <td>{{ $assessment->biodataKejadian->penyebab ?? '-' }}</td>
        </tr>
        <tr>
            <td>Sumber Informasi Awal</td>
            <td>:</td>
            <td>{{ $assessment->biodataKejadian->sumber_informasi_awal ?? '-' }}</td>
        </tr>
    </table>
    @endif

    <!-- SKOR KEPARAHAN -->
    @if($assessment->ringkasanSkor)
    <div class="section-title">III. RINGKASAN SKOR KEPARAHAN & REKOMENDASI</div>
    <table class="table-data">
        <thead>
            <tr>
                <th style="text-align: center;">Tingkat Keparahan</th>
                <th style="text-align: center;">Skor Dampak Total</th>
                <th style="text-align: center;">Rekomendasi Respon</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="text-align: center; font-size: 12pt; font-weight: bold; text-transform: uppercase;">
                    {{ str_replace('_', ' ', $assessment->ringkasanSkor->tingkat_keparahan ?? '-') }}
                </td>
                <td style="text-align: center; font-size: 14pt; font-weight: bold; color: red;">
                    {{ number_format($assessment->ringkasanSkor->skor_total ?? 0, 1) }} / 100
                </td>
                <td style="text-align: center; font-weight: bold;">
                    {{ str_replace('_', ' ', $assessment->ringkasanSkor->rekomendasi_respon ?? '-') }}
                </td>
            </tr>
        </tbody>
    </table>
    @endif

    <div class="page-break"></div>

    <!-- RINCIAN SEKTORAL -->
    <div class="section-title">IV. RINCIAN DAMPAK SEKTORAL</div>
    <table class="table-data">
        <thead>
            <tr>
                <th style="width: 25%;">Sektor</th>
                <th style="width: 15%; text-align: center;">Skor Sektor</th>
                <th>Catatan Dampak</th>
            </tr>
        </thead>
        <tbody>
            @php $rs = $assessment->ringkasanSkor; @endphp
            <tr>
                <td><strong>Manusia</strong></td>
                <td style="text-align: center;">{{ $rs ? number_format($rs->skor_manusia, 1) : '-' }}</td>
                <td>
                    @php $dm = $assessment->dampakManusiaV2 ?? $assessment->dampakManusia; @endphp
                    @if($dm)
                        Meninggal: {{ number_format($dm->meninggal ?? 0) }} jiwa, 
                        Menderita: {{ number_format($dm->terdampak_jiwa ?? ($dm->menderita_mengungsi ?? 0)) }} jiwa, 
                        Mengungsi: {{ number_format($dm->pengungsi_jiwa ?? 0) }} jiwa ({{ number_format($dm->pengungsi_kk ?? 0) }} KK).
                    @else
                        -
                    @endif
                </td>
            </tr>
            <tr>
                <td><strong>Infrastruktur</strong></td>
                <td style="text-align: center;">{{ $rs ? number_format($rs->skor_infrastruktur, 1) : '-' }}</td>
                <td>
                    @php $infra = $assessment->dampakInfrastruktur; @endphp
                    @if($infra)
                        Rumah Rusak Berat: {{ number_format($infra->rumah_rb ?? 0) }} unit, 
                        Rusak Sedang: {{ number_format($infra->rumah_rs ?? 0) }} unit, 
                        Rusak Ringan: {{ number_format($infra->rumah_rr ?? 0) }} unit.<br>
                        Fasilitas: Pendidikan ({{ $infra->fasilitas_pendidikan ?? 0 }}), Ibadah ({{ $infra->fasilitas_ibadah ?? 0 }}), Kesehatan ({{ $infra->fasilitas_kesehatan ?? 0 }}).
                    @else
                        -
                    @endif
                </td>
            </tr>
            <tr>
                <td><strong>Lingkungan</strong></td>
                <td style="text-align: center;">{{ $rs ? number_format($rs->skor_lingkungan, 1) : '-' }}</td>
                <td>
                    @php $ling = $assessment->dampakLingkungan; @endphp
                    @if($ling)
                        Lahan Pertanian Rusak: {{ number_format($ling->lahan_pertanian_rusak_ha ?? 0) }} Ha, 
                        Lahan Perkebunan Rusak: {{ number_format($ling->lahan_perkebunan_rusak_ha ?? 0) }} Ha.<br>
                        Hewan Ternak Mati/Hilang: {{ number_format($ling->ternak_terdampak_ekor ?? 0) }} ekor.
                    @else
                        -
                    @endif
                </td>
            </tr>
            <tr>
                <td><strong>Ekonomi</strong></td>
                <td style="text-align: center;">{{ $rs ? number_format($rs->skor_ekonomi, 1) : '-' }}</td>
                <td>
                    @php $eko = $assessment->dampakEkonomi; @endphp
                    @if($eko)
                        Estimasi Kerugian: Rp {{ number_format($eko->estimasi_kerugian_total ?? 0, 0, ',', '.') }}, 
                        Usaha Terdampak: {{ number_format($eko->usaha_terdampak ?? 0) }} usaha.
                    @else
                        -
                    @endif
                </td>
            </tr>
        </tbody>
    </table>

    <!-- KEBUTUHAN MENDESAK -->
    @if($assessment->kebutuhanMendesak->count())
    <div class="section-title">V. KEBUTUHAN MENDESAK KORBAN</div>
    <table class="table-data">
        <thead>
            <tr>
                <th>Nama Kebutuhan</th>
                <th style="text-align: center; width: 20%;">Jumlah</th>
                <th style="text-align: center; width: 20%;">Satuan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($assessment->kebutuhanMendesak as $k)
            <tr>
                <td>{{ $k->nama_kebutuhan }}</td>
                <td style="text-align: center;">{{ number_format($k->jumlah) }}</td>
                <td style="text-align: center;">{{ $k->satuan }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <!-- TANDA TANGAN -->
    <div class="signature-container">
        <div style="width: 50%; float: right; text-align: center;">
            <p>Petugas Asesor Lapangan,</p>
            <div style="margin-bottom: 50pt;"></div>
            <p style="font-weight: bold; text-decoration: underline;">
                {{ $assessment->petugas?->profil?->nama_lengkap ?? $assessment->nama_petugas ?? '_______________________' }}
            </p>
            <p>NU PEDULI PCNU {{ $assessment->insiden?->pcnu?->nama_pcnu ?? '' }}</p>
        </div>
    </div>
</body>
</html>
