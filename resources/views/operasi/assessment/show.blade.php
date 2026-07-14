<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('insiden.show', $insiden->id_insiden) }}"
                   class="p-2 bg-slate-100 text-slate-600 rounded-xl hover:bg-slate-200 transition-colors">
                    <i class="bi bi-arrow-left text-lg"></i>
                </a>
                <div>
                    <h2 class="text-xl font-bold text-slate-800">Detail Assessment</h2>
                    <p class="text-sm text-slate-500">Insiden {{ $insiden->kode_kejadian }}</p>
                </div>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('insiden.assessment.cetak', [$insiden->id_insiden, $assessment->id_assessment_utama]) }}"
                   class="px-4 py-2 bg-slate-600 text-white rounded-xl text-sm font-semibold hover:bg-slate-700 transition-colors flex items-center gap-2">
                    <i class="bi bi-printer"></i> Cetak
                </a>
                <a href="{{ route('insiden.assessment.edit', [$insiden->id_insiden, $assessment->id_assessment_utama]) }}"
                   class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-semibold hover:bg-indigo-700 transition-colors flex items-center gap-2">
                    <i class="bi bi-pencil"></i> Edit Assessment
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">

        {{-- Header Info Card --}}
        <div class="bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Jenis Laporan</span>
                    <p class="mt-1 font-semibold text-slate-800">
                        {{ $assessment->jenis_laporan === 'kaji_cepat' ? '⚡ Kaji Cepat' : '📋 Pendataan Lanjutan' }}
                    </p>
                </div>
                <div>
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Waktu Assessment</span>
                    <p class="mt-1 font-semibold text-slate-800">
                        {{ $assessment->waktu_assesment->format('d M Y, H:i') }}
                    </p>
                </div>
                <div>
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Petugas</span>
                    <p class="mt-1 font-semibold text-slate-800">
                        {{ $assessment->petugas?->profil?->nama_lengkap ?? 'Tidak tersedia' }}
                    </p>
                </div>
                <div class="md:col-span-3">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Cakupan Wilayah</span>
                    <p class="mt-1 font-semibold text-slate-800">{{ $assessment->cakupan_wilayah_deskripsi }}</p>
                </div>
                @if($assessment->lokasiDetail)
                    <div>
                        <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Kecamatan</span>
                        <p class="mt-1 font-medium text-slate-700">
                            {{ $assessment->lokasiDetail->kecamatan?->nama_kec ?? '-' }}
                        </p>
                    </div>
                    <div>
                        <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Desa</span>
                        <p class="mt-1 font-medium text-slate-700">
                            {{ $assessment->lokasiDetail->desa?->nama_desa ?? '-' }}
                        </p>
                    </div>
                    <div>
                        <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Koordinat</span>
                        <p class="mt-1 font-mono text-sm text-slate-700">
                            {{ $assessment->latitude ?? '-' }}, {{ $assessment->longitude ?? '-' }}
                        </p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Ringkasan Skor --}}
        @if($assessment->ringkasanSkor)
        <div class="bg-gradient-to-r from-indigo-600 to-purple-700 text-white rounded-2xl p-6 shadow-xl">
            <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
                <i class="bi bi-speedometer2"></i> Ringkasan Skor Keparahan
            </h3>
            <div class="grid grid-cols-2 md:grid-cols-6 gap-4">
                <div class="text-center bg-white/20 rounded-xl p-3">
                    <div class="text-2xl font-bold">{{ $assessment->ringkasanSkor->skor_manusia }}</div>
                    <div class="text-xs opacity-80">Manusia</div>
                </div>
                <div class="text-center bg-white/20 rounded-xl p-3">
                    <div class="text-2xl font-bold">{{ $assessment->ringkasanSkor->skor_infrastruktur }}</div>
                    <div class="text-xs opacity-80">Infrastruktur</div>
                </div>
                <div class="text-center bg-white/20 rounded-xl p-3">
                    <div class="text-2xl font-bold">{{ $assessment->ringkasanSkor->skor_lingkungan }}</div>
                    <div class="text-xs opacity-80">Lingkungan</div>
                </div>
                <div class="text-center bg-white/20 rounded-xl p-3">
                    <div class="text-2xl font-bold">{{ $assessment->ringkasanSkor->skor_ekonomi }}</div>
                    <div class="text-xs opacity-80">Ekonomi</div>
                </div>
                <div class="text-center bg-white/20 rounded-xl p-3">
                    <div class="text-2xl font-bold">{{ $assessment->ringkasanSkor->skor_sosial }}</div>
                    <div class="text-xs opacity-80">Sosial</div>
                </div>
                <div class="text-center bg-white/30 rounded-xl p-3 ring-2 ring-white/50">
                    <div class="text-3xl font-extrabold">{{ $assessment->ringkasanSkor->skor_total }}</div>
                    <div class="text-xs opacity-80">TOTAL</div>
                    <div class="text-xs mt-1 uppercase font-bold tracking-wider">
                        {{ $assessment->ringkasanSkor->tingkat_keparahan }}
                    </div>
                </div>
            </div>
            <div class="mt-3 text-sm opacity-90">
                Rekomendasi: <strong class="uppercase">{{ str_replace('_', ' ', $assessment->ringkasanSkor->rekomendasi_respon) }}</strong>
            </div>
        </div>
        @endif

        {{-- KORBAN JIWA --}}
        @php
            $dm = $assessment->dampakManusiaV2 ?? $assessment->dampakManusia;
        @endphp
        @if($dm)
        <div class="bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl p-6">
            <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                <span class="p-2 bg-red-100 text-red-600 rounded-lg"><i class="bi bi-people-fill"></i></span>
                Dampak Korban Jiwa
            </h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @php
                    $statCards = [
                        ['label' => 'Meninggal', 'val' => $dm->meninggal ?? 0, 'color' => 'red'],
                        ['label' => 'Hilang', 'val' => $dm->hilang ?? 0, 'color' => 'orange'],
                        ['label' => 'Luka Berat', 'val' => $dm->luka_berat ?? 0, 'color' => 'amber'],
                        ['label' => 'Luka Ringan', 'val' => $dm->luka_ringan ?? 0, 'color' => 'yellow'],
                        ['label' => 'Terdampak Jiwa', 'val' => $dm->terdampak_jiwa ?? ($dm->menderita_mengungsi ?? 0), 'color' => 'blue'],
                        ['label' => 'Terdampak KK', 'val' => $dm->terdampak_kk ?? 0, 'color' => 'blue'],
                        ['label' => 'Pengungsi Jiwa', 'val' => $dm->pengungsi_jiwa ?? 0, 'color' => 'purple'],
                        ['label' => 'Pengungsi KK', 'val' => $dm->pengungsi_kk ?? 0, 'color' => 'purple'],
                    ];
                @endphp
                @foreach($statCards as $card)
                <div class="bg-{{ $card['color'] }}-50 border border-{{ $card['color'] }}-100 rounded-xl p-4 text-center">
                    <div class="text-2xl font-bold text-{{ $card['color'] }}-600">{{ number_format($card['val']) }}</div>
                    <div class="text-xs font-medium text-{{ $card['color'] }}-800 mt-1">{{ $card['label'] }}</div>
                </div>
                @endforeach
            </div>
            @if(isset($dm->pengungsi_balita) || isset($dm->pengungsi_lansia))
            <div class="mt-4 p-4 bg-slate-50 rounded-xl">
                <div class="text-sm font-semibold text-slate-700 mb-3">Kelompok Rentan di Pengungsian</div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                    <div class="flex justify-between"><span class="text-slate-500">Balita</span><strong>{{ $dm->pengungsi_balita ?? 0 }}</strong></div>
                    <div class="flex justify-between"><span class="text-slate-500">Lansia</span><strong>{{ $dm->pengungsi_lansia ?? 0 }}</strong></div>
                    <div class="flex justify-between"><span class="text-slate-500">Disabilitas</span><strong>{{ $dm->pengungsi_disabilitas ?? 0 }}</strong></div>
                    <div class="flex justify-between"><span class="text-slate-500">Ibu Hamil</span><strong>{{ $dm->pengungsi_ibu_hamil ?? 0 }}</strong></div>
                </div>
            </div>
            @endif
        </div>
        @endif

        {{-- KERUSAKAN RUMAH --}}
        @php $rumah = $assessment->dampakRumah ?? $assessment->dampakInfrastruktur; @endphp
        @if($rumah)
        <div class="bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl p-6">
            <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                <span class="p-2 bg-amber-100 text-amber-600 rounded-lg"><i class="bi bi-house-fill"></i></span>
                Kerusakan Rumah
            </h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @php
                    $rusak = [
                        ['label' => 'Rusak Berat', 'val' => $rumah->rusak_berat ?? $rumah->rumah_rusak_berat ?? 0, 'color' => 'red'],
                        ['label' => 'Rusak Sedang', 'val' => $rumah->rusak_sedang ?? $rumah->rumah_rusak_sedang ?? 0, 'color' => 'orange'],
                        ['label' => 'Rusak Ringan', 'val' => $rumah->rusak_ringan ?? $rumah->rumah_rusak_ringan ?? 0, 'color' => 'yellow'],
                        ['label' => 'Terendam', 'val' => $rumah->terendam ?? $rumah->rumah_terendam ?? 0, 'color' => 'blue'],
                    ];
                @endphp
                @foreach($rusak as $r)
                <div class="bg-{{ $r['color'] }}-50 border border-{{ $r['color'] }}-100 rounded-xl p-4 text-center">
                    <div class="text-2xl font-bold text-{{ $r['color'] }}-600">{{ number_format($r['val']) }}</div>
                    <div class="text-xs font-medium text-{{ $r['color'] }}-800 mt-1">{{ $r['label'] }}</div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- FASILITAS UMUM & VITAL --}}
        @if($assessment->dampakFasum || $assessment->dampakInfrastruktur)
        <div class="bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl p-6">
            <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                <span class="p-2 bg-sky-100 text-sky-600 rounded-lg"><i class="bi bi-building"></i></span>
                Kerusakan Fasilitas Umum & Infrastruktur Vital
            </h3>
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                @php
                    $fasum = $assessment->dampakFasum;
                    $infra = $assessment->dampakInfrastruktur;
                    $items = [
                        ['label' => 'Pendidikan', 'val' => $fasum?->pendidikan ?? $infra?->fasilitas_pendidikan_rusak ?? 0, 'icon' => 'bi-book'],
                        ['label' => 'Kesehatan', 'val' => $fasum?->kesehatan ?? $infra?->fasilitas_kesehatan_rusak ?? 0, 'icon' => 'bi-hospital'],
                        ['label' => 'Tempat Ibadah', 'val' => $fasum?->ibadah ?? $infra?->tempat_ibadah_rusak ?? 0, 'icon' => 'bi-moon'],
                        ['label' => 'Kantor Pem.', 'val' => $fasum?->kantor ?? $infra?->kantor_pemerintah_rusak ?? 0, 'icon' => 'bi-bank'],
                        ['label' => 'Jembatan', 'val' => ($fasum?->jembatan ?? 0) + ($infra?->jembatan_putus ?? 0), 'icon' => 'bi-sign-turn-right'],
                    ];
                @endphp
                @foreach($items as $item)
                <div class="bg-sky-50 rounded-xl p-4 text-center">
                    <i class="bi {{ $item['icon'] }} text-sky-500 text-xl mb-2 block"></i>
                    <div class="text-xl font-bold text-sky-700">{{ number_format($item['val']) }}</div>
                    <div class="text-xs text-sky-600 mt-1">{{ $item['label'] }}</div>
                </div>
                @endforeach
            </div>
            @if($assessment->dampakVital || $infra)
            <div class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                @php $vital = $assessment->dampakVital; @endphp
                <div class="flex justify-between p-3 bg-slate-50 rounded-lg">
                    <span class="text-slate-500">Jalan Rusak</span>
                    <strong>{{ number_format($vital?->jalan ?? $infra?->jalan_rusak_km ?? 0, 2) }} km</strong>
                </div>
                <div class="flex justify-between p-3 bg-slate-50 rounded-lg">
                    <span class="text-slate-500">Air Bersih</span>
                    <strong>{{ $vital?->air_bersih ?? 0 }}</strong>
                </div>
                <div class="flex justify-between p-3 bg-slate-50 rounded-lg">
                    <span class="text-slate-500">Listrik Padam</span>
                    <strong>{{ $vital?->listrik ?? $infra?->jaringan_listrik_padam_kk ?? 0 }}</strong>
                </div>
                <div class="flex justify-between p-3 bg-slate-50 rounded-lg">
                    <span class="text-slate-500">Telekomunikasi</span>
                    <strong>{{ $vital?->telekomunikasi ?? 0 }}</strong>
                </div>
            </div>
            @endif
        </div>
        @endif

        {{-- DAMPAK LINGKUNGAN & EKONOMI --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @if($assessment->dampakLingkungan)
            <div class="bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl p-6">
                <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                    <span class="p-2 bg-emerald-100 text-emerald-600 rounded-lg"><i class="bi bi-tree"></i></span>
                    Dampak Lingkungan
                </h3>
                <div class="space-y-3 text-sm">
                    @php $ling = $assessment->dampakLingkungan; @endphp
                    <div class="flex justify-between"><span class="text-slate-500">Lahan Pertanian Rusak</span><strong>{{ number_format($ling->lahan_pertanian_rusak_ha, 2) }} ha</strong></div>
                    <div class="flex justify-between"><span class="text-slate-500">Hutan Terdampak</span><strong>{{ number_format($ling->hutan_terdampak_ha, 2) }} ha</strong></div>
                    <div class="flex justify-between"><span class="text-slate-500">Lahan Tercemar</span><strong>{{ number_format($ling->lahan_tercemar_ha, 2) }} ha</strong></div>
                    <div class="flex justify-between"><span class="text-slate-500">Tingkat Kerusakan</span><strong class="capitalize">{{ str_replace('_', ' ', $ling->tingkat_kerusakan_lingkungan) }}</strong></div>
                    
                    <div class="grid grid-cols-2 gap-2 mt-2 pt-2 border-t text-xs">
                        <div class="flex justify-between"><span class="text-slate-500">Pencemaran Tanah:</span><strong>{{ $ling->pencemaran_tanah ? 'Ya' : 'Tidak' }}</strong></div>
                        <div class="flex justify-between"><span class="text-slate-500">Erosi/Sedimentasi:</span><strong>{{ $ling->erosi_sedimentasi ? 'Ya' : 'Tidak' }}</strong></div>
                        <div class="flex justify-between"><span class="text-slate-500">Kerusakan DAS:</span><strong>{{ $ling->kerusakan_daerah_aliran_sungai ? 'Ya' : 'Tidak' }}</strong></div>
                        <div class="flex justify-between"><span class="text-slate-500">Kerusakan Pesisir:</span><strong>{{ $ling->kerusakan_ekosistem_pesisir ? 'Ya' : 'Tidak' }}</strong></div>
                        <div class="flex justify-between"><span class="text-slate-500">Rehabilitasi Lahan:</span><strong>{{ $ling->butuh_rehabilitasi_lahan ? 'Ya' : 'Tidak' }}</strong></div>
                    </div>
                    @if($ling->sumber_air_tercemar)
                    <div class="p-2 bg-red-50 text-red-700 rounded-lg text-xs mt-2">⚠️ Sumber air tercemar</div>
                    @endif
                    @if($ling->catatan_lingkungan)
                    <div class="p-2 bg-slate-50 text-slate-700 rounded-lg text-xs mt-2"><strong>Catatan:</strong> {{ $ling->catatan_lingkungan }}</div>
                    @endif
                </div>
            </div>
            @endif

            @if($assessment->dampakEkonomi)
            <div class="bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl p-6">
                <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                    <span class="p-2 bg-violet-100 text-violet-600 rounded-lg"><i class="bi bi-currency-dollar"></i></span>
                    Kerugian Ekonomi
                </h3>
                <div class="space-y-3 text-sm">
                    @php $eko = $assessment->dampakEkonomi; @endphp
                    <div class="flex justify-between"><span class="text-slate-500">Perumahan</span><strong>Rp {{ number_format($eko->kerugian_perumahan) }}</strong></div>
                    <div class="flex justify-between"><span class="text-slate-500">Pertanian</span><strong>Rp {{ number_format($eko->kerugian_pertanian) }}</strong></div>
                    <div class="flex justify-between"><span class="text-slate-500">Peternakan</span><strong>Rp {{ number_format($eko->kerugian_peternakan ?? 0) }}</strong></div>
                    <div class="flex justify-between"><span class="text-slate-500">Perikanan</span><strong>Rp {{ number_format($eko->kerugian_perikanan ?? 0) }}</strong></div>
                    <div class="flex justify-between"><span class="text-slate-500">UMKM</span><strong>Rp {{ number_format($eko->kerugian_umkm ?? 0) }}</strong></div>
                    <div class="flex justify-between"><span class="text-slate-500">Infrastruktur</span><strong>Rp {{ number_format($eko->kerugian_infrastruktur) }}</strong></div>
                    <div class="flex justify-between"><span class="text-slate-500">Lainnya</span><strong>Rp {{ number_format($eko->kerugian_lainnya) }}</strong></div>
                    
                    <div class="grid grid-cols-2 gap-2 mt-2 pt-2 border-t text-xs">
                        <div class="flex justify-between"><span class="text-slate-500">Pencaharian Hilang:</span><strong>{{ number_format($eko->mata_pencaharian_hilang ?? 0) }} orang</strong></div>
                        <div class="flex justify-between"><span class="text-slate-500">Usaha Terdampak:</span><strong>{{ number_format($eko->usaha_terdampak ?? 0) }} unit</strong></div>
                    </div>
                    @if($eko->metodologi_estimasi)
                    <div class="text-xs text-slate-500 mt-2">Metodologi: {{ $eko->metodologi_estimasi }}</div>
                    @endif
                    <div class="border-t pt-3 flex justify-between font-bold">
                        <span>Total Estimasi</span>
                        <span class="text-violet-700">Rp {{ number_format($eko->estimasi_kerugian_total ?? 0) }}</span>
                    </div>
                    @if($eko->catatan_ekonomi)
                    <div class="p-2 bg-slate-50 text-slate-700 rounded-lg text-xs mt-2"><strong>Catatan:</strong> {{ $eko->catatan_ekonomi }}</div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        {{-- NARASI KEJADIAN --}}
        @if($assessment->biodataKejadian || $assessment->narasiKejadian->count())
        <div class="bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl p-6">
            <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                <span class="p-2 bg-slate-100 text-slate-600 rounded-lg"><i class="bi bi-journal-text"></i></span>
                Narasi & Kronologi
            </h3>
            @if($assessment->biodataKejadian)
            <div class="mb-4 p-4 bg-slate-50 rounded-xl">
                <div class="text-sm font-semibold text-slate-700 mb-2">Biodata Kejadian</div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                    <div><span class="text-slate-500">Tanggal Mulai:</span> <strong>{{ $assessment->biodataKejadian->tanggal_mulai_kejadian }}</strong></div>
                    <div><span class="text-slate-500">Skala:</span> <strong class="capitalize">{{ $assessment->biodataKejadian->skala_kejadian }}</strong></div>
                    <div class="md:col-span-2"><span class="text-slate-500">Penyebab:</span> <strong>{{ $assessment->biodataKejadian->penyebab_utama ?? '-' }}</strong></div>
                </div>
                <p class="mt-3 text-sm text-slate-700 leading-relaxed">{{ $assessment->biodataKejadian->kronologi_singkat }}</p>
            </div>
            @endif
            @foreach($assessment->narasiKejadian as $narasi)
            <div class="border-l-4 border-indigo-400 pl-4 py-2 mb-4">
                <div class="flex items-center gap-2 mb-1">
                    <span class="px-2 py-0.5 text-xs bg-indigo-100 text-indigo-700 rounded-full uppercase">{{ str_replace('_', ' ', $narasi->fase) }}</span>
                    <strong class="text-slate-800">{{ $narasi->judul_narasi }}</strong>
                </div>
                <p class="text-sm text-slate-600 leading-relaxed">{{ $narasi->isi_narasi }}</p>
            </div>
            @endforeach
        </div>
        @endif

        {{-- KEBUTUHAN --}}
        @if($assessment->kebutuhanLanjutan || $assessment->kebutuhanMendesak->count())
        <div class="bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl p-6">
            <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                <span class="p-2 bg-orange-100 text-orange-600 rounded-lg"><i class="bi bi-box-seam"></i></span>
                Kebutuhan Mendesak
            </h3>
            @if($assessment->kebutuhanLanjutan)
            @php $kb = $assessment->kebutuhanLanjutan; @endphp
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-4">
                @foreach(['dana' => 'Dana', 'relawan' => 'Relawan', 'logistik' => 'Logistik', 'peralatan' => 'Peralatan', 'medis' => 'Medis', 'pangan' => 'Pangan', 'lainnya' => 'Lainnya'] as $key => $label)
                    @if($kb->{"kebutuhan_$key"})
                    <div class="p-3 bg-orange-50 rounded-xl">
                        <div class="text-xs font-semibold text-orange-700 uppercase mb-1">{{ $label }}</div>
                        <p class="text-sm text-slate-700">{{ $kb->{"kebutuhan_$key"} }}</p>
                    </div>
                    @endif
                @endforeach
            </div>
            @endif

            @if($assessment->kebutuhanMendesak->count())
            <div class="mt-4">
                <div class="text-sm font-semibold text-slate-700 mb-2">Daftar Kebutuhan Urgent (Mendesak)</div>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                    @foreach($assessment->kebutuhanMendesak as $km)
                    <div class="p-3 bg-orange-50/50 border border-orange-100 rounded-xl text-sm">
                        <div class="font-bold text-slate-800">{{ $km->nama_kebutuhan }}</div>
                        <div class="text-xs text-orange-700 font-semibold mt-1">Jumlah: {{ number_format($km->jumlah) }} {{ $km->satuan }}</div>
                        @if($km->catatan)
                        <div class="text-xs text-slate-500 mt-1 italic">Note: {{ $km->catatan }}</div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            @if($assessment->kebutuhanNumerik->count())
            <div class="mt-4">
                <div class="text-sm font-semibold text-slate-700 mb-2">Detail Kebutuhan (Numerik)</div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    @foreach($assessment->kebutuhanNumerik as $kn)
                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg text-sm border
                        {{ $kn->prioritas === 'darurat' ? 'border-red-300 bg-red-50' : ($kn->prioritas === 'penting' ? 'border-amber-300 bg-amber-50' : 'border-slate-200') }}">
                        <div>
                            <div class="font-semibold text-slate-800">{{ $kn->item?->nama_item ?? $kn->id_item }}</div>
                            <div class="text-xs text-slate-500">{{ $kn->prioritas }}</div>
                        </div>
                        <div class="text-right">
                            <div class="font-bold text-lg">{{ number_format($kn->jumlah_dibutuhkan) }}</div>
                            <div class="text-xs text-slate-500">{{ $kn->satuan }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
        @endif

    </div>

    {{-- REVIEW WORKFLOW --}}
    @php $statusReview = $assessment->status_review ?? 'draft'; @endphp
    <div class="bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl p-6 mt-6">
        <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
            <span class="p-2 bg-indigo-100 text-indigo-600 rounded-lg"><i class="bi bi-check2-square"></i></span>
            Review Assessment
        </h3>

        <div class="flex items-center gap-3 mb-4">
            <span class="text-sm text-slate-500">Status:</span>
            <span class="px-3 py-1 rounded-full text-sm font-semibold {{
                match($statusReview) {
                    'draft' => 'bg-slate-100 text-slate-700',
                    'submitted' => 'bg-blue-100 text-blue-700',
                    'in_review' => 'bg-amber-100 text-amber-700',
                    'approved' => 'bg-green-100 text-green-700',
                    'rejected' => 'bg-red-100 text-red-700',
                    default => 'bg-slate-100 text-slate-700',
                }
            }}">
                {{ str_replace('_', ' ', ucfirst($statusReview)) }}
            </span>

            @if($assessment->reviewer)
                <span class="text-sm text-slate-500 ml-4">
                    Reviewer: <strong>{{ $assessment->reviewer?->profil?->nama_lengkap ?? 'Tidak tersedia' }}</strong>
                </span>
            @endif

            @if($assessment->waktu_review)
                <span class="text-sm text-slate-500">
                    Waktu: <strong>{{ $assessment->waktu_review->format('d M Y, H:i') }}</strong>
                </span>
            @endif
        </div>

        @if($assessment->catatan_review)
        <div class="p-4 bg-slate-50 rounded-xl mb-4">
            <span class="text-xs font-semibold text-slate-500 uppercase">Catatan Review</span>
            <p class="mt-1 text-sm text-slate-700">{{ $assessment->catatan_review }}</p>
        </div>
        @endif

        @can('update', $assessment)
            @if($statusReview === 'draft')
                <form action="{{ route('insiden.assessment.submit', [$insiden->id_insiden, $assessment->id_assessment_utama]) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-semibold hover:bg-blue-700 transition-colors">
                        <i class="bi bi-send"></i> Ajukan Review
                    </button>
                </form>
            @endif
        @endcan

        @can('review', $assessment)
            @if($statusReview === 'submitted')
            <div class="mt-4 p-4 bg-indigo-50 rounded-xl border border-indigo-200">
                <h4 class="text-sm font-semibold text-indigo-800 mb-3">Tindakan Review</h4>
                <form action="{{ route('insiden.assessment.review', [$insiden->id_insiden, $assessment->id_assessment_utama]) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <textarea name="catatan_review" rows="3" placeholder="Catatan review (wajib jika ditolak)..."
                                  class="w-full px-3 py-2 border border-indigo-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300"></textarea>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" name="action" value="approved"
                                class="px-4 py-2 bg-green-600 text-white rounded-xl text-sm font-semibold hover:bg-green-700 transition-colors">
                            <i class="bi bi-check-lg"></i> Setujui
                        </button>
                        <button type="submit" name="action" value="rejected"
                                class="px-4 py-2 bg-red-600 text-white rounded-xl text-sm font-semibold hover:bg-red-700 transition-colors">
                            <i class="bi bi-x-lg"></i> Tolak
                        </button>
                    </div>
                </form>
            </div>
            @endif
        @endcan

        @if($statusReview === 'in_review')
            <div class="mt-4 p-4 bg-teal-50 rounded-xl border border-teal-200">
                <h4 class="text-sm font-semibold text-teal-800 mb-2">Lanjut ke Pleno</h4>
                <p class="text-sm text-teal-700 mb-3">Assessment telah disetujui. Silakan buat Pleno untuk menentukan tindak lanjut.</p>
                <a href="{{ route('insiden.pleno.create', $insiden) }}"
                   class="inline-flex items-center px-4 py-2 bg-teal-600 text-white rounded-xl text-sm font-semibold hover:bg-teal-700 transition-colors">
                    <i class="bi bi-file-earmark-text"></i> Buat Pleno
                </a>
            </div>
        @endif
    </div>
</x-app-layout>
