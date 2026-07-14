<x-app-layout>
    <x-slot name="header">Edit Assessment</x-slot>

    <div class="mb-6 p-4 bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl flex flex-col md:flex-row justify-between items-center gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('insiden.assessment.show', [$insiden->id_insiden, $assessment->id_assessment_utama]) }}"
               class="p-2 bg-slate-100 text-slate-600 rounded-xl hover:bg-slate-200 transition-colors">
                <i class="bi bi-arrow-left text-xl"></i>
            </a>
            <div class="p-3 bg-amber-100 text-amber-600 rounded-xl">
                <i class="bi bi-pencil-square text-2xl"></i>
            </div>
            <div>
                <h2 class="text-xl font-bold text-slate-800">Edit Assessment</h2>
                <p class="text-sm text-slate-500">Insiden #{{ $insiden->kode_kejadian }}</p>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-600 rounded-xl">
            <div class="font-bold mb-2"><i class="bi bi-exclamation-triangle-fill"></i> Terdapat Kesalahan:</div>
            <ul class="list-disc pl-5 text-sm space-y-1">
                @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('insiden.assessment.update', [$insiden->id_insiden, $assessment->id_assessment_utama]) }}"
          id="assessment-edit-form" x-data="wizard" onsubmit="return validateEditForm()" novalidate>
        @csrf
        @method('PUT')
        <input type="hidden" name="uuid_insiden" value="{{ $insiden->uuid_insiden }}">

        {{-- Wizard Navigation --}}
        <div class="mb-6 overflow-x-auto pb-2">
            <div class="flex items-center min-w-max p-2 bg-white/50 backdrop-blur-md rounded-xl border border-white/40 shadow-sm gap-2">
                <template x-for="(tab, index) in tabs" :key="index">
                    <button type="button" @click="activeTab = index"
                        :class="activeTab === index ? 'bg-white shadow-md text-indigo-600 ring-1 ring-indigo-100' : 'text-slate-500 hover:bg-white/40'"
                        class="px-4 py-2.5 rounded-lg font-semibold text-sm transition-all duration-300 flex items-center gap-2 whitespace-nowrap">
                        <span class="flex items-center justify-center w-6 h-6 rounded-full text-xs"
                              :class="activeTab === index ? 'bg-indigo-100' : 'bg-slate-100'">
                            <span x-text="index + 1"></span>
                        </span>
                        <span x-text="tab.name"></span>
                    </button>
                </template>
            </div>
        </div>

        <div class="bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl p-6 lg:p-8 relative min-h-[500px]">

            {{-- Tab 1: Informasi Dasar --}}
            <div x-show="activeTab === 0" x-transition.opacity.duration.300ms class="space-y-6">
                <h3 class="text-lg font-bold text-slate-800 border-b border-slate-100 pb-3">Informasi Utama Assessment</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Jenis Laporan <span class="text-rose-500">*</span></label>
                        <select name="jenis_laporan" required class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring focus:ring-indigo-200 transition-all">
                            <option value="kaji_cepat" {{ old('jenis_laporan', $assessment->jenis_laporan) === 'kaji_cepat' ? 'selected' : '' }}>Kaji Cepat</option>
                            <option value="pendataan_lanjutan" {{ old('jenis_laporan', $assessment->jenis_laporan) === 'pendataan_lanjutan' ? 'selected' : '' }}>Pendataan Lanjutan</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Waktu Assessment <span class="text-rose-500">*</span></label>
                        <input type="datetime-local" name="waktu_assesment" required
                               value="{{ old('waktu_assesment', $assessment->waktu_assesment?->format('Y-m-d\TH:i')) }}"
                               class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all" max="{{ now()->format('Y-m-d\TH:i') }}">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Cakupan Wilayah <span class="text-rose-500">*</span></label>
                        <textarea name="cakupan_wilayah_deskripsi" rows="2" required minlength="10" maxlength="250"
                                  class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all">{{ old('cakupan_wilayah_deskripsi', $assessment->cakupan_wilayah_deskripsi) }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Latitude</label>
                        <input type="number" step="any" min="-11" max="6" name="latitude"
                               value="{{ old('latitude', $assessment->latitude) }}"
                               class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Longitude</label>
                        <input type="number" step="any" min="95" max="141" name="longitude"
                               value="{{ old('longitude', $assessment->longitude) }}"
                               class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all">
                    </div>
                </div>
            </div>

            {{-- Tab 2: Biodata Kejadian --}}
            <div x-show="activeTab === 1" x-transition.opacity.duration.300ms class="space-y-6" style="display: none;">
                <h3 class="text-lg font-bold text-slate-800 border-b border-slate-100 pb-3">Biodata Bencana</h3>
                @php $biodata = $assessment->biodataKejadian; @endphp
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Tanggal Mulai Kejadian <span class="text-rose-500">*</span></label>
                        <input type="date" name="biodata_kejadian[tanggal_mulai_kejadian]" required
                               value="{{ old('biodata_kejadian.tanggal_mulai_kejadian', $biodata?->tanggal_mulai_kejadian) }}"
                               class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Jam Mulai</label>
                        <input type="time" name="biodata_kejadian[jam_mulai_kejadian]"
                               value="{{ old('biodata_kejadian.jam_mulai_kejadian', $biodata?->jam_mulai_kejadian) }}"
                               class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Kronologi Singkat <span class="text-rose-500">*</span></label>
                        <textarea name="biodata_kejadian[kronologi_singkat]" rows="3" required maxlength="1000"
                                  class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all">{{ old('biodata_kejadian.kronologi_singkat', $biodata?->kronologi_singkat) }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Skala Kejadian</label>
                        <select name="biodata_kejadian[skala_kejadian]" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all">
                            @foreach(['lokal', 'kecamatan', 'kabupaten', 'provinsi', 'nasional'] as $skala)
                            <option value="{{ $skala }}" {{ old('biodata_kejadian.skala_kejadian', $biodata?->skala_kejadian) === $skala ? 'selected' : '' }}>{{ ucfirst($skala) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Penyebab Utama</label>
                        <input type="text" name="biodata_kejadian[penyebab_utama]"
                               value="{{ old('biodata_kejadian.penyebab_utama', $biodata?->penyebab_utama) }}"
                               class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all">
                    </div>
                </div>
            </div>

            {{-- Tab 3: Narasi --}}
            <div x-show="activeTab === 2" x-transition.opacity.duration.300ms class="space-y-6" style="display: none;">
                <h3 class="text-lg font-bold text-slate-800 border-b border-slate-100 pb-3">Narasi / Catatan Lapangan</h3>
                @php $narasi = $assessment->narasiKejadian->first(); @endphp
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Fase Narasi <span class="text-rose-500">*</span></label>
                        <select name="narasi_kejadian[fase]" required class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all">
                            @foreach(['saat_bencana', 'pra_bencana', 'pasca_bencana'] as $fase)
                            <option value="{{ $fase }}" {{ old('narasi_kejadian.fase', $narasi?->fase) === $fase ? 'selected' : '' }}>{{ str_replace('_', ' ', ucfirst($fase)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Judul Narasi <span class="text-rose-500">*</span></label>
                        <input type="text" name="narasi_kejadian[judul_narasi]" required
                               value="{{ old('narasi_kejadian.judul_narasi', $narasi?->judul_narasi) }}"
                               class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Isi Narasi <span class="text-rose-500">*</span></label>
                        <textarea name="narasi_kejadian[isi_narasi]" rows="6" required
                                  class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all">{{ old('narasi_kejadian.isi_narasi', $narasi?->isi_narasi) }}</textarea>
                    </div>

                    <div class="md:col-span-2 mt-6">
                        <h4 class="text-md font-bold text-slate-700 mb-3 border-t pt-4">Kebutuhan Mendesak</h4>
                        <div class="space-y-4">
                            <template x-for="(item, idx) in kebutuhanMendesakList" :key="idx">
                                <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 bg-slate-50 p-4 rounded-xl relative border border-slate-100">
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-600 mb-1">Nama Kebutuhan</label>
                                        <input type="text" :name="'kebutuhan_mendesak['+idx+'][nama_kebutuhan]'" x-model="item.nama_kebutuhan" required class="w-full text-sm rounded-xl border-slate-200 focus:border-indigo-500">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-600 mb-1">Jumlah</label>
                                        <input type="number" min="1" :name="'kebutuhan_mendesak['+idx+'][jumlah]'" x-model="item.jumlah" required class="w-full text-sm rounded-xl border-slate-200 focus:border-indigo-500">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-600 mb-1">Satuan</label>
                                        <input type="text" :name="'kebutuhan_mendesak['+idx+'][satuan]'" x-model="item.satuan" required class="w-full text-sm rounded-xl border-slate-200 focus:border-indigo-500" placeholder="Ex: kg, box, ltr">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-600 mb-1">Catatan</label>
                                        <div class="flex gap-2">
                                            <input type="text" :name="'kebutuhan_mendesak['+idx+'][catatan]'" x-model="item.catatan" class="w-full text-sm rounded-xl border-slate-200 focus:border-indigo-500">
                                            <button type="button" @click="kebutuhanMendesakList.splice(idx, 1)" class="p-2 text-rose-500 hover:bg-rose-50 rounded-xl transition-colors">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </template>
                            <button type="button" @click="kebutuhanMendesakList.push({ nama_kebutuhan: '', jumlah: 1, satuan: '', catatan: '' })" class="px-4 py-2 border border-dashed border-indigo-300 text-indigo-600 rounded-xl text-sm font-semibold hover:bg-indigo-50 transition-colors flex items-center gap-2">
                                <i class="bi bi-plus-lg"></i> Tambah Kebutuhan
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tab 4: Korban Jiwa --}}
            <div x-show="activeTab === 3" x-transition.opacity.duration.300ms class="space-y-6" style="display: none;">
                <h3 class="text-lg font-bold text-slate-800 border-b border-slate-100 pb-3">Dampak Korban Jiwa & Pengungsi</h3>
                @php $dm = $assessment->dampakManusiaV2; @endphp
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                    @foreach([
                        ['label' => 'Meninggal Dunia', 'name' => 'dampak_manusia[meninggal]', 'old' => $dm?->meninggal ?? 0],
                        ['label' => 'Hilang', 'name' => 'dampak_manusia[hilang]', 'old' => $dm?->hilang ?? 0],
                        ['label' => 'Luka Berat', 'name' => 'dampak_manusia[luka_berat]', 'old' => $dm?->luka_berat ?? 0],
                        ['label' => 'Luka Ringan', 'name' => 'dampak_manusia[luka_ringan]', 'old' => $dm?->luka_ringan ?? 0],
                        ['label' => 'Terdampak Jiwa', 'name' => 'dampak_manusia[menderita_mengungsi]', 'old' => $dm?->terdampak_jiwa ?? 0],
                        ['label' => 'Terdampak KK', 'name' => 'dampak_manusia[terdampak_kk]', 'old' => $dm?->terdampak_kk ?? 0],
                        ['label' => 'Pengungsi Jiwa', 'name' => 'dampak_manusia[pengungsi_jiwa]', 'old' => $dm?->pengungsi_jiwa ?? 0],
                        ['label' => 'Pengungsi KK', 'name' => 'dampak_manusia[pengungsi_kk]', 'old' => $dm?->pengungsi_kk ?? 0],
                        ['label' => 'Pengungsi Balita', 'name' => 'dampak_manusia[pengungsi_balita]', 'old' => $dm?->pengungsi_balita ?? 0],
                        ['label' => 'Pengungsi Lansia', 'name' => 'dampak_manusia[pengungsi_lansia]', 'old' => $dm?->pengungsi_lansia ?? 0],
                        ['label' => 'Pengungsi Disabilitas', 'name' => 'dampak_manusia[pengungsi_disabilitas]', 'old' => $dm?->pengungsi_disabilitas ?? 0],
                        ['label' => 'Pengungsi Ibu Hamil', 'name' => 'dampak_manusia[pengungsi_ibu_hamil]', 'old' => $dm?->pengungsi_ibu_hamil ?? 0],
                    ] as $f)
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">{{ $f['label'] }}</label>
                        <input type="number" min="0" name="{{ $f['name'] }}"
                               value="{{ old(str_replace(['[', ']'], ['.', ''], $f['name']), $f['old']) }}"
                               class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all">
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Tab 5: Infrastruktur --}}
            <div x-show="activeTab === 4" x-transition.opacity.duration.300ms class="space-y-6" style="display: none;">
                <h3 class="text-lg font-bold text-slate-800 border-b border-slate-100 pb-3">Dampak Infrastruktur</h3>
                @php $infra = $assessment->dampakInfrastruktur; @endphp
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                    @foreach([
                        ['label' => 'Rumah Rusak Berat', 'name' => 'dampak_infrastruktur[rumah_rusak_berat]', 'old' => $infra?->rumah_rusak_berat ?? 0],
                        ['label' => 'Rumah Rusak Sedang', 'name' => 'dampak_infrastruktur[rumah_rusak_sedang]', 'old' => $infra?->rumah_rusak_sedang ?? 0],
                        ['label' => 'Rumah Rusak Ringan', 'name' => 'dampak_infrastruktur[rumah_rusak_ringan]', 'old' => $infra?->rumah_rusak_ringan ?? 0],
                        ['label' => 'Rumah Terendam', 'name' => 'dampak_infrastruktur[rumah_terendam]', 'old' => $infra?->rumah_terendam ?? 0],
                        ['label' => 'Fasilitas Kesehatan Rusak', 'name' => 'dampak_infrastruktur[fasilitas_kesehatan_rusak]', 'old' => $infra?->fasilitas_kesehatan_rusak ?? 0],
                        ['label' => 'Fasilitas Pendidikan Rusak', 'name' => 'dampak_infrastruktur[fasilitas_pendidikan_rusak]', 'old' => $infra?->fasilitas_pendidikan_rusak ?? 0],
                        ['label' => 'Tempat Ibadah Rusak', 'name' => 'dampak_infrastruktur[tempat_ibadah_rusak]', 'old' => $infra?->tempat_ibadah_rusak ?? 0],
                        ['label' => 'Kantor Rusak', 'name' => 'dampak_infrastruktur[kantor_pemerintah_rusak]', 'old' => $infra?->kantor_pemerintah_rusak ?? 0],
                    ] as $f)
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">{{ $f['label'] }}</label>
                        <input type="number" min="0" name="{{ $f['name'] }}"
                               value="{{ old(str_replace(['[', ']'], ['.', ''], $f['name']), $f['old']) }}"
                               class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all">
                    </div>
                    @endforeach
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Jalan Rusak (Km)</label>
                        <input type="number" step="any" min="0" name="dampak_infrastruktur[jalan_rusak_km]"
                               value="{{ old('dampak_infrastruktur.jalan_rusak_km', $infra?->jalan_rusak_km ?? 0) }}"
                               class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Jembatan Putus</label>
                        <input type="number" min="0" name="dampak_infrastruktur[jembatan_putus]"
                               value="{{ old('dampak_infrastruktur.jembatan_putus', $infra?->jembatan_putus ?? 0) }}"
                               class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Jembatan Rusak</label>
                        <input type="number" min="0" name="dampak_infrastruktur[jembatan_rusak]"
                               value="{{ old('dampak_infrastruktur.jembatan_rusak', $infra?->jembatan_rusak ?? 0) }}"
                               class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Listrik Padam (KK)</label>
                        <input type="number" min="0" name="dampak_infrastruktur[jaringan_listrik_padam_kk]"
                               value="{{ old('dampak_infrastruktur.jaringan_listrik_padam_kk', $infra?->jaringan_listrik_padam_kk ?? 0) }}"
                               class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Air Bersih Rusak</label>
                        <select name="dampak_infrastruktur[sarana_air_bersih_rusak]" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all">
                            <option value="0" {{ old('dampak_infrastruktur.sarana_air_bersih_rusak', $infra?->sarana_air_bersih_rusak) == '0' ? 'selected' : '' }}>Tidak</option>
                            <option value="1" {{ old('dampak_infrastruktur.sarana_air_bersih_rusak', $infra?->sarana_air_bersih_rusak) == '1' ? 'selected' : '' }}>Ya</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Komunikasi Putus</label>
                        <select name="dampak_infrastruktur[jaringan_komunikasi_putus]" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all">
                            <option value="0" {{ old('dampak_infrastruktur.jaringan_komunikasi_putus', $infra?->jaringan_komunikasi_putus) == '0' ? 'selected' : '' }}>Tidak</option>
                            <option value="1" {{ old('dampak_infrastruktur.jaringan_komunikasi_putus', $infra?->jaringan_komunikasi_putus) == '1' ? 'selected' : '' }}>Ya</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Catatan Infrastruktur</label>
                        <input type="text" name="dampak_infrastruktur[catatan_infrastruktur]"
                               value="{{ old('dampak_infrastruktur.catatan_infrastruktur', $infra?->catatan_infrastruktur) }}"
                               class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all">
                    </div>
                </div>
            </div>

            {{-- Tab 6: Lingkungan --}}
            <div x-show="activeTab === 5" x-transition.opacity.duration.300ms class="space-y-6" style="display: none;">
                <h3 class="text-lg font-bold text-slate-800 border-b border-slate-100 pb-3">Dampak Lingkungan</h3>
                @php $ling = $assessment->dampakLingkungan; @endphp
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Lahan Pertanian Rusak (Ha)</label>
                        <input type="number" step="any" min="0" name="dampak_lingkungan[lahan_pertanian_rusak_ha]"
                               value="{{ old('dampak_lingkungan.lahan_pertanian_rusak_ha', $ling?->lahan_pertanian_rusak_ha ?? 0) }}"
                               class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Hutan Terdampak (Ha)</label>
                        <input type="number" step="any" min="0" name="dampak_lingkungan[hutan_terdampak_ha]"
                               value="{{ old('dampak_lingkungan.hutan_terdampak_ha', $ling?->hutan_terdampak_ha ?? 0) }}"
                               class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Lahan Tercemar (Ha)</label>
                        <input type="number" step="any" min="0" name="dampak_lingkungan[lahan_tercemar_ha]"
                               value="{{ old('dampak_lingkungan.lahan_tercemar_ha', $ling?->lahan_tercemar_ha ?? 0) }}"
                               class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Ternak Terdampak (Ekor)</label>
                        <input type="number" min="0" name="dampak_lingkungan[ternak_terdampak_ekor]"
                               value="{{ old('dampak_lingkungan.ternak_terdampak_ekor', $ling?->ternak_terdampak_ekor ?? 0) }}"
                               class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Sumber Air Tercemar</label>
                        <select name="dampak_lingkungan[sumber_air_tercemar]" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all">
                            <option value="0" {{ old('dampak_lingkungan.sumber_air_tercemar', $ling?->sumber_air_tercemar) == '0' ? 'selected' : '' }}>Tidak</option>
                            <option value="1" {{ old('dampak_lingkungan.sumber_air_tercemar', $ling?->sumber_air_tercemar) == '1' ? 'selected' : '' }}>Ya</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Pencemaran Tanah</label>
                        <select name="dampak_lingkungan[pencemaran_tanah]" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all">
                            <option value="0" {{ old('dampak_lingkungan.pencemaran_tanah', $ling?->pencemaran_tanah) == '0' ? 'selected' : '' }}>Tidak</option>
                            <option value="1" {{ old('dampak_lingkungan.pencemaran_tanah', $ling?->pencemaran_tanah) == '1' ? 'selected' : '' }}>Ya</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Erosi/Sedimentasi</label>
                        <select name="dampak_lingkungan[erosi_sedimentasi]" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all">
                            <option value="0" {{ old('dampak_lingkungan.erosi_sedimentasi', $ling?->erosi_sedimentasi) == '0' ? 'selected' : '' }}>Tidak</option>
                            <option value="1" {{ old('dampak_lingkungan.erosi_sedimentasi', $ling?->erosi_sedimentasi) == '1' ? 'selected' : '' }}>Ya</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Kerusakan DAS</label>
                        <select name="dampak_lingkungan[kerusakan_daerah_aliran_sungai]" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all">
                            <option value="0" {{ old('dampak_lingkungan.kerusakan_daerah_aliran_sungai', $ling?->kerusakan_daerah_aliran_sungai) == '0' ? 'selected' : '' }}>Tidak</option>
                            <option value="1" {{ old('dampak_lingkungan.kerusakan_daerah_aliran_sungai', $ling?->kerusakan_daerah_aliran_sungai) == '1' ? 'selected' : '' }}>Ya</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Kerusakan Pesisir</label>
                        <select name="dampak_lingkungan[kerusakan_ekosistem_pesisir]" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all">
                            <option value="0" {{ old('dampak_lingkungan.kerusakan_ekosistem_pesisir', $ling?->kerusakan_ekosistem_pesisir) == '0' ? 'selected' : '' }}>Tidak</option>
                            <option value="1" {{ old('dampak_lingkungan.kerusakan_ekosistem_pesisir', $ling?->kerusakan_ekosistem_pesisir) == '1' ? 'selected' : '' }}>Ya</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Rehabilitasi Lahan</label>
                        <select name="dampak_lingkungan[butuh_rehabilitasi_lahan]" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all">
                            <option value="0" {{ old('dampak_lingkungan.butuh_rehabilitasi_lahan', $ling?->butuh_rehabilitasi_lahan) == '0' ? 'selected' : '' }}>Tidak</option>
                            <option value="1" {{ old('dampak_lingkungan.butuh_rehabilitasi_lahan', $ling?->butuh_rehabilitasi_lahan) == '1' ? 'selected' : '' }}>Ya</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Tingkat Kerusakan</label>
                        <select name="dampak_lingkungan[tingkat_kerusakan_lingkungan]" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all">
                            <option value="tidak_ada" {{ old('dampak_lingkungan.tingkat_kerusakan_lingkungan', $ling?->tingkat_kerusakan_lingkungan) == 'tidak_ada' ? 'selected' : '' }}>Tidak Ada</option>
                            <option value="ringan" {{ old('dampak_lingkungan.tingkat_kerusakan_lingkungan', $ling?->tingkat_kerusakan_lingkungan) == 'ringan' ? 'selected' : '' }}>Ringan</option>
                            <option value="sedang" {{ old('dampak_lingkungan.tingkat_kerusakan_lingkungan', $ling?->tingkat_kerusakan_lingkungan) == 'sedang' ? 'selected' : '' }}>Sedang</option>
                            <option value="berat" {{ old('dampak_lingkungan.tingkat_kerusakan_lingkungan', $ling?->tingkat_kerusakan_lingkungan) == 'berat' ? 'selected' : '' }}>Berat</option>
                            <option value="sangat_berat" {{ old('dampak_lingkungan.tingkat_kerusakan_lingkungan', $ling?->tingkat_kerusakan_lingkungan) == 'sangat_berat' ? 'selected' : '' }}>Sangat Berat</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Catatan Lingkungan</label>
                        <input type="text" name="dampak_lingkungan[catatan_lingkungan]"
                               value="{{ old('dampak_lingkungan.catatan_lingkungan', $ling?->catatan_lingkungan) }}"
                               class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all">
                    </div>
                </div>
            </div>

            {{-- Tab 7: Ekonomi --}}
            <div x-show="activeTab === 6" x-transition.opacity.duration.300ms class="space-y-6" style="display: none;">
                <h3 class="text-lg font-bold text-slate-800 border-b border-slate-100 pb-3">Kerugian Ekonomi</h3>
                @php $eko = $assessment->dampakEkonomi; @endphp
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach([
                        ['label' => 'Kerugian Perumahan (Rp)', 'name' => 'dampak_ekonomi[kerugian_perumahan]', 'old' => $eko?->kerugian_perumahan ?? 0],
                        ['label' => 'Kerugian Pertanian (Rp)', 'name' => 'dampak_ekonomi[kerugian_pertanian]', 'old' => $eko?->kerugian_pertanian ?? 0],
                        ['label' => 'Kerugian Peternakan (Rp)', 'name' => 'dampak_ekonomi[kerugian_peternakan]', 'old' => $eko?->kerugian_peternakan ?? 0],
                        ['label' => 'Kerugian Perikanan (Rp)', 'name' => 'dampak_ekonomi[kerugian_perikanan]', 'old' => $eko?->kerugian_perikanan ?? 0],
                        ['label' => 'Kerugian UMKM (Rp)', 'name' => 'dampak_ekonomi[kerugian_umkm]', 'old' => $eko?->kerugian_umkm ?? 0],
                        ['label' => 'Kerugian Infrastruktur (Rp)', 'name' => 'dampak_ekonomi[kerugian_infrastruktur]', 'old' => $eko?->kerugian_infrastruktur ?? 0],
                        ['label' => 'Kerugian Lainnya (Rp)', 'name' => 'dampak_ekonomi[kerugian_lainnya]', 'old' => $eko?->kerugian_lainnya ?? 0],
                        ['label' => 'Estimasi Total (Rp)', 'name' => 'dampak_ekonomi[estimasi_kerugian_total]', 'old' => $eko?->estimasi_kerugian_total ?? 0],
                        ['label' => 'Mata Pencaharian Hilang', 'name' => 'dampak_ekonomi[mata_pencaharian_hilang]', 'old' => $eko?->mata_pencaharian_hilang ?? 0],
                        ['label' => 'Usaha Terdampak', 'name' => 'dampak_ekonomi[usaha_terdampak]', 'old' => $eko?->usaha_terdampak ?? 0],
                    ] as $f)
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">{{ $f['label'] }}</label>
                        <input type="number" min="0" name="{{ $f['name'] }}"
                               value="{{ old(str_replace(['[', ']'], ['.', ''], $f['name']), $f['old']) }}"
                               class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all">
                    </div>
                    @endforeach
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Metodologi Estimasi</label>
                        <input type="text" name="dampak_ekonomi[metodologi_estimasi]"
                               value="{{ old('dampak_ekonomi.metodologi_estimasi', $eko?->metodologi_estimasi) }}"
                               class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Catatan Ekonomi</label>
                        <input type="text" name="dampak_ekonomi[catatan_ekonomi]"
                               value="{{ old('dampak_ekonomi.catatan_ekonomi', $eko?->catatan_ekonomi) }}"
                               class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all">
                    </div>
                </div>
            </div>

            {{-- Tab 8: Simpan --}}
            <div x-show="activeTab === 7" x-transition.opacity.duration.300ms class="space-y-6" style="display: none;">
                <h3 class="text-lg font-bold text-slate-800 border-b border-slate-100 pb-3">Konfirmasi Perubahan</h3>
                <div class="p-6 bg-amber-50 border border-amber-200 rounded-xl text-center max-w-2xl mx-auto mt-8">
                    <i class="bi bi-pencil-square text-5xl text-amber-500 mb-4 inline-block"></i>
                    <h4 class="text-xl font-bold text-slate-800 mb-2">Simpan Perubahan?</h4>
                    <p class="text-slate-600 mb-6">Pastikan semua data sudah diverifikasi sebelum disimpan.</p>
                    <button type="submit"
                            class="px-8 py-3 rounded-xl font-bold text-white bg-amber-500 hover:bg-amber-600 shadow-lg shadow-amber-500/30 transition-all flex items-center justify-center gap-2 w-full mx-auto">
                        <i class="bi bi-check-circle-fill"></i> Simpan Perubahan
                    </button>
                </div>
            </div>

            {{-- Wizard Footer --}}
            <div class="mt-10 pt-6 border-t border-slate-100 flex justify-between items-center">
                <button type="button" @click="if(activeTab > 0) activeTab--"
                    :class="activeTab === 0 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-slate-100'"
                    class="px-6 py-2.5 rounded-xl font-semibold text-slate-600 transition-all border border-slate-200">
                    Sebelumnya
                </button>
                <button type="button" x-show="activeTab < tabs.length - 1" @click="activeTab++"
                    class="px-6 py-2.5 rounded-xl font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-md shadow-indigo-500/30 transition-all">
                    Selanjutnya
                </button>
            </div>
        </div>
    </form>

    @php
        $kebutuhanMendesakJson = json_encode(old('kebutuhan_mendesak', $assessment->kebutuhanMendesak->map(fn($item) => [
            'nama_kebutuhan' => $item->nama_kebutuhan,
            'jumlah' => $item->jumlah,
            'satuan' => $item->satuan,
            'catatan' => $item->catatan,
        ])->toArray()));
    @endphp
    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('wizard', () => ({
                activeTab: 0,
                kebutuhanMendesakList: {!! $kebutuhanMendesakJson !!},
                tabs: [
                    { name: 'Informasi' },
                    { name: 'Biodata' },
                    { name: 'Narasi' },
                    { name: 'Korban Jiwa' },
                    { name: 'Infrastruktur' },
                    { name: 'Lingkungan' },
                    { name: 'Ekonomi' },
                    { name: 'Simpan' },
                ],
            }));
        });

        function validateEditForm() {
            const form = document.getElementById('assessment-edit-form');
            if (!form.checkValidity()) {
                const firstInvalid = form.querySelector(':invalid');
                if (firstInvalid) {
                    const tabDiv = firstInvalid.closest('[x-show]');
                    if (tabDiv) {
                        const match = tabDiv.getAttribute('x-show').match(/activeTab\s*===\s*(\d+)/);
                        if (match && window.Alpine) {
                            const wizardEl = document.querySelector('[x-data="wizard"]');
                            if (wizardEl) {
                                Alpine.$data(wizardEl).activeTab = parseInt(match[1]);
                            }
                        }
                    }
                }
                form.reportValidity();
                return false;
            }

            const btn = form.querySelector('button[type="submit"]');
            btn.innerHTML = '<i class="bi bi-arrow-repeat animate-spin"></i> Menyimpan...';
            btn.classList.add('opacity-75', 'cursor-not-allowed');
            return true;
        }
    </script>
    @endpush
</x-app-layout>
