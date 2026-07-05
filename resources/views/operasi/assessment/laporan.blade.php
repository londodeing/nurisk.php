<x-app-layout>
    <x-slot name="header">Laporan Assessment — {{ $insiden->kode_kejadian }}</x-slot>

    <div class="mb-6 flex justify-between items-center bg-white/80 backdrop-blur-xl border border-white/40 shadow-sm p-4 rounded-2xl no-print">
        <h2 class="text-lg font-bold text-slate-700"><i class="bi bi-file-earmark-pdf text-rose-500"></i> Pratinjau Laporan Assessment</h2>
        <div class="flex gap-2">
            <a href="{{ route('insiden.assessment.show', [$insiden->id_insiden, $assessment->id_assessment_utama]) }}" class="px-5 py-2.5 border border-slate-300 text-slate-700 font-bold rounded-xl hover:bg-slate-50 transition-all flex items-center gap-2">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
            <button onclick="window.print()" class="px-5 py-2.5 bg-slate-800 text-white font-bold rounded-xl shadow-md shadow-slate-500/20 hover:-translate-y-0.5 transition-all flex items-center gap-2">
                <i class="bi bi-printer"></i> Cetak PDF
            </button>
        </div>
    </div>

    <div class="bg-white border border-slate-200 shadow-xl rounded-none md:rounded-2xl mx-auto overflow-hidden p-8 md:p-12 print:shadow-none print:border-none print:p-0 max-w-4xl" id="print-area">

        {{-- Document Header --}}
        <div class="flex items-center justify-between border-b-2 border-slate-800 pb-6 mb-8">
            <div class="flex items-center gap-4">
                <div class="w-20 h-20 bg-emerald-100 text-emerald-700 flex items-center justify-center rounded-full border-2 border-emerald-600 print:flex">
                    <i class="bi bi-globe text-4xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-black text-slate-800 tracking-tight uppercase">Pusat Komando Tanggap Darurat</h1>
                    <p class="text-slate-600 font-bold">Lembaga Penanggulangan Bencana dan Perubahan Iklim</p>
                    <p class="text-slate-500 text-sm">Pengurus Wilayah Nahdlatul Ulama Jawa Timur</p>
                </div>
            </div>
            <div class="text-right">
                <p class="text-xs text-slate-400 mt-1">Dokumen ID: ASM-{{ $assessment->uuid_assessment }}</p>
            </div>
        </div>

        <h2 class="text-xl font-black text-center text-slate-800 uppercase underline mb-8">Laporan Assessment Situasi Darurat</h2>

        {{-- Basic Info Box --}}
        <div class="bg-slate-50 border border-slate-200 rounded-xl p-6 mb-8 break-inside-avoid">
            <div class="grid grid-cols-2 gap-y-4 text-sm">
                <div>
                    <p class="text-slate-500 font-bold uppercase text-xs">Lokasi Kejadian</p>
                    <p class="text-slate-800 font-semibold text-lg">{{ $assessment->cakupan_wilayah_deskripsi }}</p>
                    @if($assessment->lokasiDetail)
                        <p class="text-slate-600 text-sm">
                            {{ $assessment->lokasiDetail->kecamatan?->nama_kec ?? '-' }},
                            {{ $assessment->lokasiDetail->desa?->nama_desa ?? '-' }}
                        </p>
                    @endif
                </div>
                <div>
                    <p class="text-slate-500 font-bold uppercase text-xs">Jenis Bencana</p>
                    <p class="text-slate-800 font-semibold text-lg">{{ $insiden->jenisBencana?->nama_bencana ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-slate-500 font-bold uppercase text-xs">Waktu Assessment</p>
                    <p class="text-slate-800 font-medium">{{ $assessment->waktu_assesment ? Carbon\Carbon::parse($assessment->waktu_assesment)->locale('id')->isoFormat('D MMMM YYYY, HH:mm') . ' WIB' : '-' }}</p>
                </div>
                <div>
                    <p class="text-slate-500 font-bold uppercase text-xs">Asesor</p>
                    <p class="text-slate-800 font-medium">{{ $assessment->petugas?->profil?->nama_lengkap ?? ($insiden->penerimaSpk?->profil?->nama_lengkap ?? 'Tidak diketahui') }}</p>
                </div>
                <div>
                    <p class="text-slate-500 font-bold uppercase text-xs">Kode Insiden</p>
                    <p class="text-slate-800 font-medium">{{ $insiden->kode_kejadian }}</p>
                </div>
                <div>
                    <p class="text-slate-500 font-bold uppercase text-xs">PCNU</p>
                    <p class="text-slate-800 font-medium">{{ $insiden->pcnu?->nama_pcnu ?? '-' }}</p>
                </div>
                <div class="col-span-2">
                    <p class="text-slate-500 font-bold uppercase text-xs">Jenis Laporan</p>
                    <p class="text-slate-800 font-medium">{{ $assessment->jenis_laporan === 'kaji_cepat' ? 'Kaji Cepat' : 'Pendataan Lanjutan' }}</p>
                </div>
            </div>
        </div>

        {{-- Executive Summary --}}
        @php $biodata = $assessment->biodataKejadian; @endphp
        @if($biodata)
        <div class="mb-8 break-inside-avoid">
            <h3 class="text-lg font-bold text-slate-800 border-l-4 border-indigo-600 pl-3 mb-3">Ringkasan Situasi</h3>
            <p class="text-slate-700 text-sm leading-relaxed text-justify">{{ $biodata->kronologi_singkat ?? 'Tidak ada data.' }}</p>
            @if($biodata->penyebab_utama)
            <p class="text-slate-600 text-sm mt-2"><strong>Penyebab:</strong> {{ $biodata->penyebab_utama }}</p>
            @endif
            @if($biodata->sumber_informasi_awal)
            <p class="text-slate-600 text-sm"><strong>Sumber Informasi:</strong> {{ $biodata->sumber_informasi_awal }}</p>
            @endif
        </div>
        @endif

        {{-- Severity & Score --}}
        @if($assessment->ringkasanSkor)
        @php $rs = $assessment->ringkasanSkor; @endphp
        <div class="flex gap-6 mb-8 break-inside-avoid">
            <div class="w-1/3 bg-rose-50 border border-rose-200 rounded-xl p-5 text-center">
                <p class="text-rose-600 font-bold uppercase text-xs tracking-wider mb-2">Tingkat Keparahan</p>
                <p class="text-3xl font-black text-rose-700 uppercase">{{ str_replace('_', ' ', $rs->tingkat_keparahan ?? '-') }}</p>
            </div>
            <div class="w-1/3 bg-indigo-50 border border-indigo-200 rounded-xl p-5 text-center">
                <p class="text-indigo-600 font-bold uppercase text-xs tracking-wider mb-2">Skor Dampak Total</p>
                <p class="text-3xl font-black text-indigo-700">{{ number_format($rs->skor_total ?? 0, 1) }}<span class="text-lg font-bold text-indigo-400">/100</span></p>
            </div>
            <div class="w-1/3 bg-amber-50 border border-amber-200 rounded-xl p-5 text-center">
                <p class="text-amber-600 font-bold uppercase text-xs tracking-wider mb-2">Rekomendasi</p>
                <p class="text-xl font-black text-amber-700 leading-tight uppercase">{{ str_replace('_', ' ', $rs->rekomendasi_respon ?? '-') }}</p>
            </div>
        </div>
        @endif

        {{-- Detail Table --}}
        <div class="mb-12 break-inside-avoid">
            <h3 class="text-lg font-bold text-slate-800 border-l-4 border-slate-600 pl-3 mb-4">Rincian Dampak Multisektor</h3>
            <table class="w-full text-sm text-left border-collapse border border-slate-300">
                <thead class="bg-slate-100 text-slate-800 font-bold">
                    <tr>
                        <th class="border border-slate-300 px-4 py-3 w-1/4">Sektor</th>
                        <th class="border border-slate-300 px-4 py-3 w-1/6 text-center">Skor (0-100)</th>
                        <th class="border border-slate-300 px-4 py-3">Catatan Utama</th>
                    </tr>
                </thead>
                <tbody class="text-slate-700">
                    @php $rs = $assessment->ringkasanSkor; @endphp
                    <tr>
                        <td class="border border-slate-300 px-4 py-3 font-semibold">Manusia</td>
                        <td class="border border-slate-300 px-4 py-3 text-center font-bold">{{ $rs ? number_format($rs->skor_manusia, 1) : '-' }}</td>
                        <td class="border border-slate-300 px-4 py-3">
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
                        <td class="border border-slate-300 px-4 py-3 font-semibold">Infrastruktur</td>
                        <td class="border border-slate-300 px-4 py-3 text-center font-bold">{{ $rs ? number_format($rs->skor_infrastruktur, 1) : '-' }}</td>
                        <td class="border border-slate-300 px-4 py-3">
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
                        <td class="border border-slate-300 px-4 py-3 font-semibold">Lingkungan</td>
                        <td class="border border-slate-300 px-4 py-3 text-center font-bold">{{ $rs ? number_format($rs->skor_lingkungan, 1) : '-' }}</td>
                        <td class="border border-slate-300 px-4 py-3">
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
                        <td class="border border-slate-300 px-4 py-3 font-semibold">Ekonomi</td>
                        <td class="border border-slate-300 px-4 py-3 text-center font-bold">{{ $rs ? number_format($rs->skor_ekonomi, 1) : '-' }}</td>
                        <td class="border border-slate-300 px-4 py-3">
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

        {{-- Kebutuhan Mendesak --}}
        @if($assessment->kebutuhanMendesak->count())
        <div class="mb-8 break-inside-avoid">
            <h3 class="text-lg font-bold text-slate-800 border-l-4 border-amber-600 pl-3 mb-4">Kebutuhan Mendesak</h3>
            <table class="w-full text-sm text-left border-collapse border border-slate-300">
                <thead class="bg-slate-100 text-slate-800 font-bold">
                    <tr>
                        <th class="border border-slate-300 px-4 py-3">Kebutuhan</th>
                        <th class="border border-slate-300 px-4 py-3 text-center">Jumlah</th>
                        <th class="border border-slate-300 px-4 py-3 text-center">Satuan</th>
                    </tr>
                </thead>
                <tbody class="text-slate-700">
                    @foreach($assessment->kebutuhanMendesak as $k)
                    <tr>
                        <td class="border border-slate-300 px-4 py-3">{{ $k->nama_kebutuhan }}</td>
                        <td class="border border-slate-300 px-4 py-3 text-center">{{ number_format($k->jumlah) }}</td>
                        <td class="border border-slate-300 px-4 py-3 text-center">{{ $k->satuan }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        {{-- Kebutuhan Lanjutan --}}
        @if($assessment->kebutuhanLanjutan)
        @php $kl = $assessment->kebutuhanLanjutan; @endphp
        <div class="mb-8 break-inside-avoid">
            <h3 class="text-lg font-bold text-slate-800 border-l-4 border-emerald-600 pl-3 mb-4">Kebutuhan Lanjutan</h3>
            <div class="grid grid-cols-2 gap-4 text-sm">
                @if($kl->kebutuhan_dana)<div><span class="text-slate-500 font-semibold">Dana:</span> {{ $kl->kebutuhan_dana }}</div>@endif
                @if($kl->kebutuhan_relawan)<div><span class="text-slate-500 font-semibold">Relawan:</span> {{ $kl->kebutuhan_relawan }}</div>@endif
                @if($kl->kebutuhan_logistik)<div><span class="text-slate-500 font-semibold">Logistik:</span> {{ $kl->kebutuhan_logistik }}</div>@endif
                @if($kl->kebutuhan_peralatan)<div><span class="text-slate-500 font-semibold">Peralatan:</span> {{ $kl->kebutuhan_peralatan }}</div>@endif
                @if($kl->kebutuhan_medis)<div><span class="text-slate-500 font-semibold">Medis:</span> {{ $kl->kebutuhan_medis }}</div>@endif
                @if($kl->kebutuhan_pangan)<div><span class="text-slate-500 font-semibold">Pangan:</span> {{ $kl->kebutuhan_pangan }}</div>@endif
                @if($kl->kebutuhan_lainnya)<div><span class="text-slate-500 font-semibold">Lainnya:</span> {{ $kl->kebutuhan_lainnya }}</div>@endif
            </div>
        </div>
        @endif

        {{-- Signature --}}
        <div class="flex justify-end mt-16 break-inside-avoid">
            <div class="text-center w-64">
                <p class="text-sm text-slate-600 mb-16">Divalidasi Oleh,<br>Komandan Tanggap Darurat</p>
                <p class="text-sm font-bold text-slate-800 underline uppercase">{{ $insiden->pemberiSpk?->profil?->nama_lengkap ?? '____________________' }}</p>
                <p class="text-xs text-slate-500">{{ $insiden->no_spk_assesment ? 'SPK: ' . $insiden->no_spk_assesment : '' }}</p>
            </div>
        </div>

    </div>

    <style>
        @media print {
            body { background: white; }
            .no-print { display: none !important; }
            #print-area { width: 100%; max-width: none; margin: 0; padding: 0; }
        }
    </style>
</x-app-layout>
