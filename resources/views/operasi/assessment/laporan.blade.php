<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Assessment - {{ $insiden->kode_kejadian }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Tipografi Baku Dokumen Resmi */
        body {
            font-family: Arial, Helvetica, "Inter", sans-serif;
            font-size: 10pt;
            color: #000;
            background-color: #f8fafc;
            line-height: 1.5;
        }

        /* Aturan Kertas & Margin PDF */
        @page {
            size: A4 portrait;
            margin: 2cm;
        }

        /* Pengaturan Area Cetak */
        .print-container {
            width: 210mm;
            min-height: 297mm;
            margin: 2rem auto;
            background: #fff;
            padding: 2cm;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        }

        @media print {
            body { background: transparent; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .no-print { display: none !important; }
            .print-container {
                margin: 0;
                padding: 0;
                box-shadow: none;
                width: 100%;
            }
            .break-inside-avoid { break-inside: avoid; page-break-inside: avoid; }
            .page-break-before { page-break-before: always; }
        }

        /* Tipografi Hirarki */
        h1, .header-title { font-size: 16pt; font-weight: bold; }
        h2, .header-subtitle { font-size: 14pt; font-weight: bold; }
        h3, .section-title { font-size: 12pt; font-weight: bold; margin-bottom: 0.5rem; margin-top: 1.5rem; text-transform: uppercase; border-bottom: 2px solid #000; padding-bottom: 2px;}
        .body-text { font-size: 10pt; }
        .table-text, table td, table th { font-size: 9pt; padding: 4px 6px; }

        /* Tabel Standar Baku */
        table.doc-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
        }
        table.doc-table th, table.doc-table td {
            border: 1px solid #000;
            vertical-align: top;
        }
        table.doc-table th {
            background-color: #e2e8f0;
            font-weight: bold;
            text-align: center;
        }
        
        /* Utility */
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }
        .mt-4 { margin-top: 1rem; }
        .mb-2 { margin-bottom: 0.5rem; }
        .mb-6 { margin-bottom: 1.5rem; }
    </style>
</head>
<body>

    {{-- Tombol Aksi (Sembunyi saat cetak) --}}
    <div class="fixed top-4 right-4 flex gap-2 no-print z-50">
        <a href="{{ route('insiden.assessment.show', [$insiden->kode_kejadian, $assessment->id_assessment_utama]) }}" class="px-4 py-2 bg-white border border-slate-300 text-slate-700 font-bold rounded shadow-sm hover:bg-slate-50 flex items-center gap-2 text-sm">
            &larr; Kembali
        </a>
        <button onclick="window.print()" class="px-4 py-2 bg-slate-800 text-white font-bold rounded shadow-sm hover:bg-slate-700 flex items-center gap-2 text-sm">
            Cetak Laporan Resmi
        </button>
    </div>

    <div class="print-container">

        {{-- KOP SURAT OFFISIAL --}}
        <div style="border-bottom: 3px double #000; padding-bottom: 15px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: flex-start;">
            <div style="display: flex; gap: 15px; align-items: center;">
                <div style="width: 70px; height: 70px; border: 2px solid #000; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 30px;">
                    NU
                </div>
                <div>
                    <div class="header-title uppercase">Pusat Komando Tanggap Darurat Bencana</div>
                    <div class="header-subtitle uppercase">Lembaga Penanggulangan Bencana dan Perubahan Iklim</div>
                    <div class="body-text">Pengurus Wilayah Nahdlatul Ulama</div>
                </div>
            </div>
            <div style="text-align: right; font-family: monospace; font-size: 9pt;">
                ID: ASM-{{ substr($assessment->uuid_assessment, 0, 8) }}<br>
                Tanggal Cetak: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}
            </div>
        </div>

        <div class="text-center mb-6">
            <h2 class="uppercase" style="text-decoration: underline;">Laporan Assessment Situasi Darurat</h2>
            <div class="font-bold body-text">Nomor Kejadian: {{ $insiden->kode_kejadian }}</div>
        </div>

        {{-- 1. INFORMASI UTAMA & LOKASI --}}
        <div class="break-inside-avoid">
            <h3 class="section-title">1. Informasi Utama & Lokasi Kejadian</h3>
            <table class="doc-table">
                <tbody>
                    <tr>
                        <td style="width: 30%; font-weight: bold;">Jenis Laporan</td>
                        <td style="width: 70%;">{{ $assessment->jenis_laporan === 'kaji_cepat' ? 'Kaji Cepat' : 'Pendataan Lanjutan' }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold;">Waktu Assessment</td>
                        <td>{{ $assessment->waktu_assesment ? \Carbon\Carbon::parse($assessment->waktu_assesment)->locale('id')->isoFormat('D MMMM YYYY, HH:mm') . ' WIB' : '-' }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold;">Asesor Lapangan</td>
                        <td>{{ $assessment->petugas?->profil?->nama_lengkap ?? ($insiden->penerimaSpk?->profil?->nama_lengkap ?? 'Tidak diketahui') }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold;">Lokasi / Cakupan Wilayah</td>
                        <td>{{ $assessment->cakupan_wilayah_deskripsi ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold;">Region Terdampak</td>
                        <td>
                            {{ $assessment->lokasiDetail?->desa?->nama_desa ? 'Desa/Kel. ' . $assessment->lokasiDetail->desa->nama_desa : '-' }},
                            {{ $assessment->lokasiDetail?->kecamatan?->nama_kec ? 'Kec. ' . $assessment->lokasiDetail->kecamatan->nama_kec : '-' }}.
                            {{ $assessment->lokasiDetail?->region_terdampak ? '('.$assessment->lokasiDetail->region_terdampak.')' : '' }}
                        </td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold;">Koordinat</td>
                        <td style="font-family: monospace;">{{ $assessment->latitude ?? '-' }}, {{ $assessment->longitude ?? '-' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- 2. BIODATA KEJADIAN --}}
        @php $biodata = $assessment->biodataKejadian; @endphp
        <div class="break-inside-avoid">
            <h3 class="section-title">2. Biodata & Kronologi Bencana</h3>
            <table class="doc-table">
                <tbody>
                    <tr>
                        <td style="width: 30%; font-weight: bold;">Jenis Bencana</td>
                        <td style="width: 70%; font-weight: bold; text-transform: uppercase;">{{ $insiden->jenisBencana?->nama_bencana ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold;">Waktu Kejadian</td>
                        <td>
                            {{ $biodata?->tanggal_mulai_kejadian ? \Carbon\Carbon::parse($biodata->tanggal_mulai_kejadian)->format('d/m/Y') : '-' }} 
                            {{ $biodata?->jam_mulai_kejadian ? 'Pukul: '.$biodata->jam_mulai_kejadian : '' }}
                        </td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold;">Skala Kejadian</td>
                        <td style="text-transform: capitalize;">{{ $biodata?->skala_kejadian ? str_replace('_', ' ', $biodata->skala_kejadian) : '-' }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold;">Penyebab Utama</td>
                        <td>{{ $biodata?->penyebab_utama ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td colspan="2" style="font-weight: bold;">Kronologi Singkat:</td>
                    </tr>
                    <tr>
                        <td colspan="2" style="text-align: justify; padding: 10px;">{{ $biodata?->kronologi_singkat ?: 'Tidak ada kronologi yang dicatat.' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- 3. NARASI LAPANGAN --}}
        @php 
            $narasiK = ($assessment->narasiKejadian ?? collect())->first();
            $narasiD = $assessment->narasiDetail;
        @endphp
        <div class="break-inside-avoid">
            <h3 class="section-title">3. Catatan Observasi Lapangan</h3>
            <table class="doc-table">
                <tbody>
                    <tr>
                        <td style="width: 30%; font-weight: bold;">Fase & Judul Narasi</td>
                        <td style="width: 70%;">
                            <strong>{{ $narasiK?->fase ? str_replace('_', ' ', strtoupper($narasiK->fase)) : '-' }}</strong> — {{ $narasiK?->judul_narasi ?: '-' }}
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" style="font-weight: bold;">Isi Narasi Utama:</td>
                    </tr>
                    <tr>
                        <td colspan="2" style="text-align: justify; padding: 10px;">{{ $narasiK?->isi_narasi ?: '-' }}</td>
                    </tr>
                    <tr><td style="font-weight: bold;">Sebaran Dampak</td><td>{{ $narasiD?->sebaran_dampak ?: '-' }}</td></tr>
                    <tr><td style="font-weight: bold;">Upaya Penanganan</td><td>{{ $narasiD?->upaya_penanganan ?: '-' }}</td></tr>
                    <tr><td style="font-weight: bold;">Kendala Lapangan</td><td>{{ $narasiD?->kendala_lapangan ?: '-' }}</td></tr>
                    <tr><td style="font-weight: bold;">Kendala Tambahan</td><td>{{ $narasiD?->kendala_tambahan ?: '-' }}</td></tr>
                    <tr><td style="font-weight: bold;">Rekomendasi Aksi</td><td>{{ $narasiD?->rekomendasi_aksi ?: '-' }}</td></tr>
                </tbody>
            </table>
        </div>

        {{-- 4. KORBAN JIWA --}}
        @php $dm = $assessment->dampakManusiaV2 ?? $assessment->dampakManusia; @endphp
        <div class="break-inside-avoid">
            <h3 class="section-title">4. Dampak Manusia & Korban Jiwa</h3>
            
            <div style="font-weight: bold; margin-bottom: 5px;" class="table-text">A. Korban Jiwa & Luka:</div>
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Meninggal</th>
                        <th>Hilang</th>
                        <th>Luka Berat</th>
                        <th>Luka Ringan</th>
                    </tr>
                </thead>
                <tbody class="text-center font-bold">
                    <tr>
                        <td>{{ number_format($dm?->meninggal ?? 0) }} Jiwa</td>
                        <td>{{ number_format($dm?->hilang ?? 0) }} Jiwa</td>
                        <td>{{ number_format($dm?->luka_berat ?? 0) }} Jiwa</td>
                        <td>{{ number_format($dm?->luka_ringan ?? 0) }} Jiwa</td>
                    </tr>
                </tbody>
            </table>

            <div style="font-weight: bold; margin-bottom: 5px;" class="table-text">B. Penduduk Terdampak & Mengungsi:</div>
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Terdampak (Jiwa)</th>
                        <th>Terdampak (KK)</th>
                        <th>Pengungsi (Jiwa)</th>
                        <th>Pengungsi (KK)</th>
                    </tr>
                </thead>
                <tbody class="text-center font-bold">
                    <tr>
                        <td>{{ number_format($dm?->terdampak_jiwa ?? ($dm?->menderita_mengungsi ?? 0)) }} Jiwa</td>
                        <td>{{ number_format($dm?->terdampak_kk ?? 0) }} KK</td>
                        <td>{{ number_format($dm?->pengungsi_jiwa ?? 0) }} Jiwa</td>
                        <td>{{ number_format($dm?->pengungsi_kk ?? 0) }} KK</td>
                    </tr>
                    <tr>
                        <td colspan="4" style="text-align: left; font-weight: normal; padding-left: 10px;">
                            <strong>Titik Pengungsian:</strong> {{ $dm?->titik_pengungsian ?: '-' }}
                        </td>
                    </tr>
                </tbody>
            </table>
            
            <div style="font-weight: bold; margin-bottom: 5px;" class="table-text">C. Rincian Kelompok Rentan:</div>
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Balita</th>
                        <th>Ibu Hamil</th>
                        <th>Lansia</th>
                        <th>Disabilitas</th>
                    </tr>
                </thead>
                <tbody class="text-center">
                    <tr>
                        <td>{{ number_format($dm?->pengungsi_balita ?? 0) }} Jiwa</td>
                        <td>{{ number_format($dm?->pengungsi_ibu_hamil ?? 0) }} Jiwa</td>
                        <td>{{ number_format($dm?->pengungsi_lansia ?? 0) }} Jiwa</td>
                        <td>{{ number_format($dm?->pengungsi_disabilitas ?? 0) }} Jiwa</td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- 5. INFRASTRUKTUR --}}
        @php 
            $rumah = $assessment->dampakRumah;
            $fasum = $assessment->dampakFasum;
            $vital = $assessment->dampakVital;
            $infra = $assessment->dampakInfrastruktur;
        @endphp
        <div class="break-inside-avoid">
            <h3 class="section-title">5. Kerusakan Infrastruktur & Bangunan</h3>
            
            <div style="font-weight: bold; margin-bottom: 5px;" class="table-text">A. Sektor Pemukiman (Rumah Tinggal)</div>
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Rusak Berat</th>
                        <th>Rusak Sedang</th>
                        <th>Rusak Ringan</th>
                        <th>Terendam</th>
                        <th>Terancam</th>
                    </tr>
                </thead>
                <tbody class="text-center">
                    <tr>
                        <td>{{ number_format($rumah?->rusak_berat ?? ($infra?->rumah_rusak_berat ?? 0)) }} Unit</td>
                        <td>{{ number_format($rumah?->rusak_sedang ?? ($infra?->rumah_rusak_sedang ?? 0)) }} Unit</td>
                        <td>{{ number_format($rumah?->rusak_ringan ?? ($infra?->rumah_rusak_ringan ?? 0)) }} Unit</td>
                        <td>{{ number_format($rumah?->terendam ?? ($infra?->rumah_terendam ?? 0)) }} Unit</td>
                        <td>{{ number_format($rumah?->terancam ?? ($infra?->rumah_terancam ?? 0)) }} Unit</td>
                    </tr>
                </tbody>
            </table>

            <div style="font-weight: bold; margin-bottom: 5px;" class="table-text">B. Fasilitas Umum & Sosial</div>
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Kesehatan</th>
                        <th>Pendidikan</th>
                        <th>Peribadatan</th>
                        <th>Perkantoran</th>
                        <th>Pasar</th>
                    </tr>
                </thead>
                <tbody class="text-center">
                    <tr>
                        <td>{{ number_format($fasum?->kesehatan ?? ($infra?->fasilitas_kesehatan_rusak ?? 0)) }} Unit</td>
                        <td>{{ number_format($fasum?->pendidikan ?? ($infra?->fasilitas_pendidikan_rusak ?? 0)) }} Unit</td>
                        <td>{{ number_format($fasum?->ibadah ?? ($infra?->tempat_ibadah_rusak ?? 0)) }} Unit</td>
                        <td>{{ number_format($fasum?->kantor ?? ($infra?->kantor_pemerintah_rusak ?? 0)) }} Unit</td>
                        <td>{{ number_format($fasum?->pasar ?? ($infra?->pasar ?? 0)) }} Unit</td>
                    </tr>
                </tbody>
            </table>

            <div style="font-weight: bold; margin-bottom: 5px;" class="table-text">C. Infrastruktur Vital & Tambahan</div>
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Jalan Rusak</th>
                        <th>Jembatan Putus/Rusak</th>
                        <th>Listrik Padam</th>
                        <th>Air Bersih Rusak</th>
                        <th>Komunikasi Putus</th>
                        <th>Sanitasi Rusak</th>
                        <th>SPBU Rusak</th>
                    </tr>
                </thead>
                <tbody class="text-center">
                    <tr>
                        <td>{{ number_format($vital?->jalan ?? ($infra?->jalan_rusak_km ?? 0), 2) }} Km</td>
                        <td>{{ number_format($fasum?->jembatan ?? ($infra?->jembatan_putus ?? 0)) }} Unit</td>
                        <td>{{ number_format($vital?->listrik ?? ($infra?->jaringan_listrik_padam_kk ?? 0)) }} KK</td>
                        <td>{{ ($vital?->air_bersih ?? ($infra?->sarana_air_bersih_rusak ?? false)) ? 'Ya' : 'Tidak' }}</td>
                        <td>{{ ($vital?->telekomunikasi ?? ($infra?->jaringan_komunikasi_putus ?? false)) ? 'Ya' : 'Tidak' }}</td>
                        <td>{{ number_format($fasum?->sanitasi ?? ($infra?->sanitasi ?? 0)) }} Unit</td>
                        <td>{{ number_format($vital?->spbu ?? ($infra?->spbu ?? 0)) }} Unit</td>
                    </tr>
                </tbody>
            </table>
            @if($infra?->catatan_infrastruktur || $fasum?->catatan_fasum || $vital?->catatan_vital)
            <div style="margin-top: 10px; padding: 10px; border: 1px dashed #ccc; font-style: italic;" class="table-text">
                <strong>Catatan Kerusakan:</strong> {{ $infra?->catatan_infrastruktur ?? ($fasum?->catatan_fasum ?? $vital?->catatan_vital) }}
            </div>
            @endif
        </div>

        {{-- 6. LINGKUNGAN --}}
        @php $ling = $assessment->dampakLingkungan; @endphp
        <div class="break-inside-avoid">
            <h3 class="section-title">6. Kerusakan Lingkungan & Pertanian</h3>
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Lahan Pertanian Rusak</th>
                        <th>Hutan Terdampak</th>
                        <th>Lahan Tercemar</th>
                        <th>Ternak Unggas</th>
                        <th>Ternak Kaki Empat</th>
                        <th>Perikanan Kolam</th>
                    </tr>
                </thead>
                <tbody class="text-center">
                    <tr>
                        <td>{{ number_format($ling?->lahan_pertanian_rusak_ha ?? 0, 2) }} Ha</td>
                        <td>{{ number_format($ling?->hutan_terdampak_ha ?? 0, 2) }} Ha</td>
                        <td>{{ number_format($ling?->lahan_tercemar_ha ?? 0, 2) }} Ha</td>
                        <td>{{ number_format($ling?->ternak_unggas_ekor ?? 0) }} Ekor</td>
                        <td>{{ number_format($ling?->ternak_kaki_empat_ekor ?? 0) }} Ekor</td>
                        <td>{{ number_format($ling?->perikanan_kolam_ha ?? 0, 2) }} Ha</td>
                    </tr>
                </tbody>
            </table>
            @if($ling?->catatan_lingkungan)
            <div style="margin-top: 10px; padding: 10px; border: 1px dashed #ccc; font-style: italic;" class="table-text">
                <strong>Catatan Lingkungan:</strong> {{ $ling->catatan_lingkungan }}
            </div>
            @endif
        </div>

        {{-- 7. EKONOMI --}}
        @php 
            $eko = $assessment->dampakEkonomi; 
            $statusEko = '-';
            if ($eko?->persentase_ekonomi_terdampak === '< 25%') $statusEko = 'Rendah';
            elseif ($eko?->persentase_ekonomi_terdampak === '25% - 50%') $statusEko = 'Sedang';
            elseif ($eko?->persentase_ekonomi_terdampak === '51% - 75%') $statusEko = 'Tinggi';
            elseif ($eko?->persentase_ekonomi_terdampak === '> 75%') $statusEko = 'Lumpuh Total';
        @endphp
        <div class="break-inside-avoid">
            <h3 class="section-title">7. Dampak Ekonomi Masyarakat</h3>
            <table class="doc-table">
                <tbody>
                    <tr>
                        <td style="width: 30%; font-weight: bold;">Status Ekonomi Terdampak</td>
                        <td style="width: 70%; text-transform: capitalize;">{{ $statusEko }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold;">Persentase Kelumpuhan Ekonomi</td>
                        <td style="font-weight: bold;">{{ $eko?->persentase_ekonomi_terdampak ?: '0%' }}</td>
                    </tr>
                </tbody>
            </table>
            
            <div style="font-weight: bold; margin-bottom: 5px;" class="table-text">Sektor Pencaharian Utama:</div>
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Sektor Pencaharian</th>
                        <th>Estimasi Kontribusi Wilayah</th>
                        <th>Status Kelumpuhan</th>
                    </tr>
                </thead>
                <tbody class="text-center">
                    <tr>
                        <td>{{ $eko?->sektor_pencaharian_1 ?: '-' }}</td>
                        <td>{{ $eko?->kontribusi_1 ?: '0' }}%</td>
                        <td style="text-transform: capitalize;">{{ $eko?->status_terdampak_1 ? str_replace('_', ' ', $eko->status_terdampak_1) : '-' }}</td>
                    </tr>
                    @if($eko?->sektor_pencaharian_2)
                    <tr>
                        <td>{{ $eko?->sektor_pencaharian_2 ?: '-' }}</td>
                        <td>{{ $eko?->kontribusi_2 ?: '0' }}%</td>
                        <td style="text-transform: capitalize;">{{ $eko?->status_terdampak_2 ? str_replace('_', ' ', $eko->status_terdampak_2) : '-' }}</td>
                    </tr>
                    @endif
                    @if($eko?->sektor_pencaharian_3)
                    <tr>
                        <td>{{ $eko?->sektor_pencaharian_3 ?: '-' }}</td>
                        <td>{{ $eko?->kontribusi_3 ?: '0' }}%</td>
                        <td style="text-transform: capitalize;">{{ $eko?->status_terdampak_3 ? str_replace('_', ' ', $eko->status_terdampak_3) : '-' }}</td>
                    </tr>
                    @endif
                </tbody>
            </table>

            <div style="font-weight: bold; margin-bottom: 5px; margin-top: 10px;" class="table-text">Dampak Lainnya:</div>
            <table class="doc-table">
                <tbody>
                    <tr>
                        <td style="width: 40%; font-weight: bold;">Gangguan Distribusi/Rantai Pasok</td>
                        <td style="width: 60%; text-transform: capitalize;">{{ $eko?->distribusi_hasil_panen ? str_replace('_', ' ', $eko->distribusi_hasil_panen) : '-' }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold;">Kerusakan Fasilitas Produksi Kolektif</td>
                        <td style="text-transform: capitalize;">{{ $eko?->fasilitas_pengolahan_kolektif ? str_replace('_', ' ', $eko->fasilitas_pengolahan_kolektif) : '-' }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold;">Catatan Ekonomi Khusus</td>
                        <td>{{ $eko?->catatan_ekonomi ?: '-' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- 8. KEBUTUHAN --}}
        @php 
            $kebNum = $assessment->kebutuhanNumerik;
            $kebLan = $assessment->kebutuhanLanjutan;
            $rs = $assessment->ringkasanSkor;
        @endphp
        
        <div class="page-break-before"></div>

        <div class="break-inside-avoid">
            <h3 class="section-title">8. Rincian Kebutuhan Bantuan Mendesak</h3>
            
            <div style="font-weight: bold; margin-bottom: 5px;" class="table-text">A. Kebutuhan Bantuan Terstruktur (Logistik / Barang)</div>
            <table class="doc-table">
                <thead>
                    <tr>
                        <th style="width: 5%;">No</th>
                        <th style="width: 30%;">Jenis Bantuan / Item</th>
                        <th style="width: 15%;">Dibutuhkan</th>
                        <th style="width: 15%;">Tersedia</th>
                        <th style="width: 15%;">Satuan</th>
                        <th style="width: 20%;">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kebNum as $index => $kn)
                    <tr class="text-center">
                        <td>{{ $index + 1 }}</td>
                        <td style="text-align: left;">{{ $kn->item?->nama_item ?? '-' }}</td>
                        <td style="font-weight: bold;">{{ number_format($kn->jumlah_dibutuhkan, 2) }}</td>
                        <td>{{ number_format($kn->jumlah_tersedia, 2) }}</td>
                        <td>{{ $kn->satuan }}</td>
                        <td style="text-align: left;">{{ $kn->keterangan ?: '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align: center; font-style: italic;">Tidak ada rincian kebutuhan numerik.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            <div style="font-weight: bold; margin-bottom: 5px;" class="table-text">B. Narasi Kebutuhan Tambahan</div>
            <table class="doc-table">
                <tbody>
                    <tr>
                        <td style="width: 30%; font-weight: bold;">Kebutuhan Relawan</td>
                        <td style="width: 70%;">{{ $kebLan?->kebutuhan_relawan ?: '-' }}</td>
                    </tr>
                    <tr><td style="font-weight: bold;">Logistik Dasar</td><td>{{ $kebLan?->kebutuhan_logistik ?: '-' }}</td></tr>
                    <tr><td style="font-weight: bold;">Peralatan Khusus</td><td>{{ $kebLan?->kebutuhan_peralatan ?: '-' }}</td></tr>
                    <tr><td style="font-weight: bold;">Bantuan Medis</td><td>{{ $kebLan?->kebutuhan_medis ?: '-' }}</td></tr>
                    <tr><td style="font-weight: bold;">Kebutuhan Pangan</td><td>{{ $kebLan?->kebutuhan_pangan ?: '-' }}</td></tr>
                    <tr><td style="font-weight: bold;">Lainnya</td><td>{{ $kebLan?->kebutuhan_lainnya ?: '-' }}</td></tr>
                </tbody>
            </table>
        </div>

        {{-- 9. PENGESAHAN --}}
        <div class="break-inside-avoid" style="margin-top: 40px;">
            <table style="width: 100%; border: none;">
                <tr>
                    <td style="width: 50%; text-align: center; border: none; vertical-align: bottom;">
                        <div class="body-text">Dibuat & Disurvei Oleh,<br>Asesor Lapangan</div>
                        <br><br><br><br>
                        <div style="font-weight: bold; text-decoration: underline;" class="uppercase body-text">{{ $assessment->petugas?->profil?->nama_lengkap ?? '_______________________' }}</div>
                        <div class="table-text">Tim Assessment / TRC</div>
                    </td>
                    <td style="width: 50%; text-align: center; border: none; vertical-align: bottom;">
                        <div class="body-text">Diketahui & Divalidasi Oleh,<br>Komandan Tanggap Darurat</div>
                        <br><br><br><br>
                        <div style="font-weight: bold; text-decoration: underline;" class="uppercase body-text">{{ $insiden->pemberiSpk?->profil?->nama_lengkap ?? '_______________________' }}</div>
                        <div class="table-text">Pengurus / PIC PCNU</div>
                    </td>
                </tr>
            </table>
        </div>

    </div>

</body>
</html>
