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
        table.bordered { border: 1px solid #000; }
        table.bordered th, table.bordered td { border: 1px solid #000; padding: 6px 8px; text-align: left; }
        table.bordered th { background: #f0f0f0; font-weight: bold; }
        .lampiran-title { text-align: center; font-size: 14pt; font-weight: bold; text-decoration: underline; margin-bottom: 20pt; }
        .lampiran-section { margin-bottom: 12pt; }
        .lampiran-section h3 { font-size: 13pt; margin-bottom: 6pt; }
        .info-box { border: 1px solid #000; padding: 12pt; margin-bottom: 16pt; }
        .info-row { display: flex; margin-bottom: 4pt; }
        .info-label { width: 180px; font-weight: bold; }
        .info-value { flex: 1; }
        .sektor-box { border: 1px solid #000; padding: 8pt 12pt; margin-bottom: 8pt; }
        .sektor-title { font-weight: bold; font-size: 11pt; margin-bottom: 4pt; }
        .sektor-score { font-size: 14pt; font-weight: bold; }
        .kebutuhan-table { width: 100%; border-collapse: collapse; margin-bottom: 12pt; }
        .kebutuhan-table th, .kebutuhan-table td { border: 1px solid #000; padding: 4px 8px; text-align: left; }
        .kebutuhan-table th { background: #f0f0f0; font-weight: bold; text-align: center; }
        .signature-area { margin-top: 40pt; text-align: right; }
        .signature-area p { margin: 2pt 0; text-align: center; }
    </style>
</head>
<body>

    {{-- ========== BAGIAN SURAT TUGAS ========== --}}
    <div class="header">
        <div class="org">NAHDLATUL ULAMA</div>
        <div class="sub-org">PENGURUS CABANG {{ strtoupper($surat->insiden?->pcnu?->nama_pcnu ?? 'KABUPATEN/KOTA') }}</div>
        <div class="alamat">Alamat: Sekretariat PCNU, {{ $surat->insiden?->pcnu?->nama_pcnu ?? 'Kabupaten/Kota' }}</div>
        <div class="garis"></div>
        <div class="garis2"></div>
    </div>

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

    {{-- ========== BAGIAN LAMPIRAN: LAPORAN ASSESSMENT ========== --}}
    <div class="page-break"></div>

    <div class="lampiran-title">LAMPIRAN: LAPORAN ASSESSMENT SITUASI DARURAT</div>

    @if($assessment)
    <div class="lampiran-section">
        <div class="info-box">
            <table style="width: 100%;">
                <tr>
                    <td style="width: 120px; font-weight: bold; padding: 2px 4px;">Lokasi</td>
                    <td style="padding: 2px 4px;">: {{ $assessment->cakupan_wilayah_deskripsi }}</td>
                </tr>
                @if($assessment->lokasiDetail)
                <tr>
                    <td style="width: 120px; font-weight: bold; padding: 2px 4px;">Kecamatan/Desa</td>
                    <td style="padding: 2px 4px;">: {{ $assessment->lokasiDetail->kecamatan?->nama_kec ?? '-' }} / {{ $assessment->lokasiDetail->desa?->nama_desa ?? '-' }}</td>
                </tr>
                @endif
                <tr>
                    <td style="font-weight: bold; padding: 2px 4px;">Jenis Bencana</td>
                    <td style="padding: 2px 4px;">: {{ $surat->insiden?->jenisBencana?->nama_bencana ?? '-' }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold; padding: 2px 4px;">Waktu Assessment</td>
                    <td style="padding: 2px 4px;">: {{ $assessment->waktu_assesment ? \Carbon\Carbon::parse($assessment->waktu_assesment)->locale('id')->isoFormat('D MMMM YYYY, HH:mm') . ' WIB' : '-' }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold; padding: 2px 4px;">Asesor</td>
                    <td style="padding: 2px 4px;">: {{ $assessment->petugas?->profil?->nama_lengkap ?? '-' }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold; padding: 2px 4px;">Kode Insiden</td>
                    <td style="padding: 2px 4px;">: {{ $surat->insiden?->kode_kejadian ?? '-' }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold; padding: 2px 4px;">PCNU</td>
                    <td style="padding: 2px 4px;">: {{ $surat->insiden?->pcnu?->nama_pcnu ?? '-' }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold; padding: 2px 4px;">Jenis Laporan</td>
                    <td style="padding: 2px 4px;">: {{ $assessment->jenis_laporan === 'kaji_cepat' ? 'Kaji Cepat' : 'Pendataan Lanjutan' }}</td>
                </tr>
            </table>
        </div>
    </div>

    @php $biodata = $assessment->biodataKejadian; @endphp
    @if($biodata)
    <div class="lampiran-section">
        <h3>Ringkasan Situasi</h3>
        <p style="text-align: justify;">{{ $biodata->kronologi_singkat ?? 'Tidak ada data.' }}</p>
        @if($biodata->penyebab_utama)
        <p><strong>Penyebab:</strong> {{ $biodata->penyebab_utama }}</p>
        @endif
        @if($biodata->sumber_informasi_awal)
        <p><strong>Sumber Informasi:</strong> {{ $biodata->sumber_informasi_awal }}</p>
        @endif
    </div>
    @endif

    @if($assessment->ringkasanSkor)
    @php $rs = $assessment->ringkasanSkor; @endphp
    <div class="lampiran-section">
        <h3>Skor Dampak</h3>
        <table style="width: 100%; border: 1px solid #000; margin-bottom: 10pt;">
            <tr>
                <td style="border: 1px solid #000; padding: 4pt; text-align: center;">
                    <strong>Tingkat Keparahan</strong><br>
                    <span style="font-size: 14pt;">{{ str_replace('_', ' ', $rs->tingkat_keparahan ?? '-') }}</span>
                </td>
                <td style="border: 1px solid #000; padding: 4pt; text-align: center;">
                    <strong>Skor Total</strong><br>
                    <span style="font-size: 14pt;">{{ number_format($rs->skor_total ?? 0, 1) }}/100</span>
                </td>
                <td style="border: 1px solid #000; padding: 4pt; text-align: center;">
                    <strong>Rekomendasi</strong><br>
                    <span style="font-size: 11pt;">{{ str_replace('_', ' ', $rs->rekomendasi_respon ?? '-') }}</span>
                </td>
            </tr>
        </table>
    </div>
    @endif

    <div class="lampiran-section">
        <h3>Rincian Dampak Multisektor</h3>
        <table class="bordered">
            <thead>
                <tr>
                    <th style="width: 25%;">Sektor</th>
                    <th style="width: 15%; text-align: center;">Skor</th>
                    <th>Catatan</th>
                </tr>
            </thead>
            <tbody>
                @php $rs = $assessment->ringkasanSkor; @endphp
                <tr>
                    <td style="font-weight: bold;">Manusia</td>
                    <td style="text-align: center;">{{ $rs ? number_format($rs->skor_manusia, 1) : '-' }}</td>
                    <td>
                        @php $dm = $assessment->dampakManusiaV2 ?? $assessment->dampakManusia; @endphp
                        @if($dm)
                            {{ number_format($dm->meninggal ?? 0) }} meninggal,
                            {{ number_format($dm->terdampak_jiwa ?? ($dm->menderita_mengungsi ?? 0)) }} jiwa terdampak,
                            {{ number_format($dm->pengungsi_jiwa ?? 0) }} mengungsi.
                        @else
                            -
                        @endif
                    </td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">Infrastruktur</td>
                    <td style="text-align: center;">{{ $rs ? number_format($rs->skor_infrastruktur, 1) : '-' }}</td>
                    <td>
                        @php $infra = $assessment->dampakInfrastruktur; @endphp
                        @if($infra)
                            {{ number_format($infra->jalan_rusak_km ?? 0) }} km jalan rusak,
                            {{ number_format($infra->jembatan_putus ?? 0) }} jembatan putus.
                        @else
                            -
                        @endif
                    </td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">Lingkungan</td>
                    <td style="text-align: center;">{{ $rs ? number_format($rs->skor_lingkungan, 1) : '-' }}</td>
                    <td>
                        @php $lingkung = $assessment->dampakLingkungan; @endphp
                        @if($lingkung)
                            {{ number_format($lingkung->lahan_pertanian_rusak_ha ?? 0) }} Ha lahan rusak,
                            {{ number_format($lingkung->ternak_terdampak_ekor ?? 0) }} ekor ternak terdampak.
                        @else
                            -
                        @endif
                    </td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">Ekonomi</td>
                    <td style="text-align: center;">{{ $rs ? number_format($rs->skor_ekonomi, 1) : '-' }}</td>
                    <td>
                        @php $eko = $assessment->dampakEkonomi; @endphp
                        @if($eko)
                            Estimasi kerugian: Rp {{ number_format($eko->estimasi_kerugian_total ?? 0, 0, ',', '.') }},
                            {{ number_format($eko->usaha_terdampak ?? 0) }} usaha terdampak.
                        @else
                            -
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    @if($assessment->kebutuhanMendesak->count())
    <div class="lampiran-section">
        <h3>Kebutuhan Mendesak</h3>
        <table class="kebutuhan-table">
            <thead>
                <tr>
                    <th>Kebutuhan</th>
                    <th style="text-align: center;">Jumlah</th>
                    <th style="text-align: center;">Satuan</th>
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
    </div>
    @endif

    @if($assessment->kebutuhanLanjutan)
    @php $kl = $assessment->kebutuhanLanjutan; @endphp
    <div class="lampiran-section">
        <h3>Kebutuhan Lanjutan</h3>
        <div style="font-size: 11pt;">
            @if($kl->kebutuhan_dana)<p><strong>Dana:</strong> {{ $kl->kebutuhan_dana }}</p>@endif
            @if($kl->kebutuhan_relawan)<p><strong>Relawan:</strong> {{ $kl->kebutuhan_relawan }}</p>@endif
            @if($kl->kebutuhan_logistik)<p><strong>Logistik:</strong> {{ $kl->kebutuhan_logistik }}</p>@endif
            @if($kl->kebutuhan_peralatan)<p><strong>Peralatan:</strong> {{ $kl->kebutuhan_peralatan }}</p>@endif
            @if($kl->kebutuhan_medis)<p><strong>Medis:</strong> {{ $kl->kebutuhan_medis }}</p>@endif
            @if($kl->kebutuhan_pangan)<p><strong>Pangan:</strong> {{ $kl->kebutuhan_pangan }}</p>@endif
            @if($kl->kebutuhan_lainnya)<p><strong>Lainnya:</strong> {{ $kl->kebutuhan_lainnya }}</p>@endif
        </div>
    </div>
    @endif

    <div class="signature-area">
        <p style="margin-bottom: 60pt;">Mengetahui,<br>Komandan Tanggap Darurat</p>
        <p style="font-weight: bold; text-decoration: underline;">{{ $surat->insiden?->pemberiSpk?->profil?->nama_lengkap ?? '____________________' }}</p>
        <p style="font-size: 10pt;">{{ $surat->insiden?->no_spk_assesment ? 'SPK: ' . $surat->insiden?->no_spk_assesment : '' }}</p>
    </div>
    @else
    <div class="lampiran-section">
        <p><em>Laporan assessment belum tersedia.</em></p>
    </div>
    @endif

</body>
</html>
