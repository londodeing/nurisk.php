        <!-- Wizard Navigation -->
        <div class="mb-6 overflow-x-auto pb-2 scrollbar-thin scrollbar-thumb-slate-200">
            <div class="flex items-center min-w-max p-2 bg-white/50 backdrop-blur-md rounded-xl border border-white/40 shadow-sm gap-2" id="wizard-nav">
                <template x-for="(tab, index) in tabs" :key="index">
                    <button type="button"
                        @click="activeTab = index"
                        :class="activeTab === index ? 'bg-white shadow-md text-indigo-600 ring-1 ring-indigo-100' : 'text-slate-500 hover:bg-white/40'"
                        class="px-4 py-2.5 rounded-lg font-semibold text-sm transition-all duration-300 flex items-center gap-2 whitespace-nowrap">
                        <span class="flex items-center justify-center w-6 h-6 rounded-full text-xs"
                              :class="activeTab === index ? 'bg-indigo-100' : 'bg-slate-100'">
                            <span x-text="index + 1"></span>
                        </span>
                        <span x-text="tab.name"></span>
                        <span x-show="tabHasErrors(index)" class="text-rose-500"><i class="bi bi-exclamation-circle-fill"></i></span>
                    </button>
                </template>
            </div>
        </div>

        <!-- Wizard Content -->
        <div class="bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl p-6 lg:p-8 relative min-h-[500px]">

            <!-- Step 1: Informasi & Lokasi -->
            <div x-show="activeTab === 0" x-transition.opacity.duration.300ms class="space-y-6">
                <h3 class="text-lg font-bold text-slate-800 border-b border-slate-100 pb-3">Informasi & Lokasi</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Jenis Laporan <span class="text-rose-500">*</span></label>
                        <select name="jenis_laporan" required class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring focus:ring-indigo-200 transition-all">
                            <option value="kaji_cepat" {{ old('jenis_laporan') == 'kaji_cepat' ? 'selected' : '' }}>Kaji Cepat</option>
                            <option value="pendataan_lanjutan" {{ old('jenis_laporan') == 'pendataan_lanjutan' ? 'selected' : '' }}>Pendataan Lanjutan</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Waktu Assessment <span class="text-rose-500">*</span></label>
                        <input type="datetime-local" name="waktu_assesment" required value="{{ old('waktu_assesment', now()->format('Y-m-d\TH:i')) }}" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all" max="{{ now()->format('Y-m-d\TH:i') }}">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Cakupan Wilayah <span class="text-rose-500">*</span></label>
                        <textarea name="cakupan_wilayah_deskripsi" rows="2" required minlength="10" maxlength="250" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all" placeholder="Contoh: Desa Pronojiwo dan Supiturang, Kec. Pronojiwo">{{ old('cakupan_wilayah_deskripsi') }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Tanggal Mulai Kejadian <span class="text-rose-500">*</span></label>
                        <input type="date" name="biodata_kejadian[tanggal_mulai_kejadian]" required value="{{ old('biodata_kejadian.tanggal_mulai_kejadian') }}" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Jam Mulai Kejadian</label>
                        <input type="time" name="biodata_kejadian[jam_mulai_kejadian]" value="{{ old('biodata_kejadian.jam_mulai_kejadian') }}" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Skala Kejadian</label>
                        <select name="biodata_kejadian[skala_kejadian]" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all">
                            <option value="lokal" {{ old('biodata_kejadian.skala_kejadian') == 'lokal' ? 'selected' : '' }}>Lokal</option>
                            <option value="kecamatan" {{ old('biodata_kejadian.skala_kejadian') == 'kecamatan' ? 'selected' : '' }}>Kecamatan</option>
                            <option value="kabupaten" {{ old('biodata_kejadian.skala_kejadian') == 'kabupaten' ? 'selected' : '' }}>Kabupaten</option>
                            <option value="provinsi" {{ old('biodata_kejadian.skala_kejadian') == 'provinsi' ? 'selected' : '' }}>Provinsi</option>
                            <option value="nasional" {{ old('biodata_kejadian.skala_kejadian') == 'nasional' ? 'selected' : '' }}>Nasional</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Penyebab Utama</label>
                        <input type="text" name="biodata_kejadian[penyebab_utama]" value="{{ old('biodata_kejadian.penyebab_utama') }}" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Latitude</label>
                        <input type="number" step="any" min="-11" max="6" name="latitude" value="{{ old('latitude') }}" placeholder="-7.250445" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Longitude</label>
                        <input type="number" step="any" min="95" max="141" name="longitude" value="{{ old('longitude') }}" placeholder="112.768845" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Kronologi Singkat <span class="text-rose-500">*</span></label>
                        <textarea name="biodata_kejadian[kronologi_singkat]" rows="3" required maxlength="1000" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all">{{ old('biodata_kejadian.kronologi_singkat') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Step 2: Dampak Kerusakan -->
            <div x-show="activeTab === 1" x-transition.opacity.duration.300ms class="space-y-6" style="display: none;">
                <h3 class="text-lg font-bold text-slate-800 border-b border-slate-100 pb-3">Dampak Manusia & Kerusakan Infrastruktur</h3>

                <div class="p-4 bg-slate-50 rounded-xl border border-slate-100">
                    <h4 class="font-semibold text-slate-700 mb-3">Korban Jiwa & Pengungsi</h4>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        @foreach([
                            ['label' => 'Meninggal', 'name' => 'dampak_manusia[meninggal]', 'icon' => 'bi-person-x'],
                            ['label' => 'Hilang', 'name' => 'dampak_manusia[hilang]', 'icon' => 'bi-question-circle'],
                            ['label' => 'Luka Berat', 'name' => 'dampak_manusia[luka_berat]', 'icon' => 'bi-bandaid'],
                            ['label' => 'Luka Ringan', 'name' => 'dampak_manusia[luka_ringan]', 'icon' => 'bi-heart-pulse'],
                            ['label' => 'Terdampak Jiwa', 'name' => 'dampak_manusia[menderita_mengungsi]', 'icon' => 'bi-people'],
                            ['label' => 'Terdampak KK', 'name' => 'dampak_manusia[terdampak_kk]', 'icon' => 'bi-house'],
                            ['label' => 'Pengungsi Jiwa', 'name' => 'dampak_manusia[pengungsi_jiwa]', 'icon' => 'bi-people-fill'],
                            ['label' => 'Pengungsi KK', 'name' => 'dampak_manusia[pengungsi_kk]', 'icon' => 'bi-house-fill'],
                            ['label' => 'Pengungsi Balita', 'name' => 'dampak_manusia[pengungsi_balita]', 'icon' => 'bi-baby'],
                            ['label' => 'Pengungsi Lansia', 'name' => 'dampak_manusia[pengungsi_lansia]', 'icon' => 'bi-person-heart'],
                            ['label' => 'Pengungsi Disabilitas', 'name' => 'dampak_manusia[pengungsi_disabilitas]', 'icon' => 'bi-person-wheelchair'],
                            ['label' => 'Pengungsi Ibu Hamil', 'name' => 'dampak_manusia[pengungsi_ibu_hamil]', 'icon' => 'bi-person-standing'],
                        ] as $field)
                        <div>
                            <label class="block text-sm font-semibold text-slate-600 mb-1">{{ $field['label'] }}</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="bi {{ $field['icon'] }} text-slate-400"></i>
                                </div>
                                <input type="number" min="0" name="{{ $field['name'] }}"
                                       value="{{ old(str_replace(['[', ']'], ['.', ''], $field['name']), 0) }}"
                                       class="pl-10 w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all">
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="p-4 bg-slate-50 rounded-xl border border-slate-100">
                    <h4 class="font-semibold text-slate-700 mb-3">Kerusakan Rumah & Infrastruktur</h4>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div><label class="block text-sm font-semibold text-slate-600 mb-1">Rumah Rusak Berat</label><input type="number" min="0" name="dampak_infrastruktur[rumah_rusak_berat]" value="{{ old('dampak_infrastruktur.rumah_rusak_berat', 0) }}" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all"></div>
                        <div><label class="block text-sm font-semibold text-slate-600 mb-1">Rumah Rusak Sedang</label><input type="number" min="0" name="dampak_infrastruktur[rumah_rusak_sedang]" value="{{ old('dampak_infrastruktur.rumah_rusak_sedang', 0) }}" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all"></div>
                        <div><label class="block text-sm font-semibold text-slate-600 mb-1">Rumah Rusak Ringan</label><input type="number" min="0" name="dampak_infrastruktur[rumah_rusak_ringan]" value="{{ old('dampak_infrastruktur.rumah_rusak_ringan', 0) }}" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all"></div>
                        <div><label class="block text-sm font-semibold text-slate-600 mb-1">Rumah Terendam</label><input type="number" min="0" name="dampak_infrastruktur[rumah_terendam]" value="{{ old('dampak_infrastruktur.rumah_terendam', 0) }}" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all"></div>
                        <div><label class="block text-sm font-semibold text-slate-600 mb-1">FasKes Rusak</label><input type="number" min="0" name="dampak_infrastruktur[fasilitas_kesehatan_rusak]" value="{{ old('dampak_infrastruktur.fasilitas_kesehatan_rusak', 0) }}" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all"></div>
                        <div><label class="block text-sm font-semibold text-slate-600 mb-1">Pendidikan Rusak</label><input type="number" min="0" name="dampak_infrastruktur[fasilitas_pendidikan_rusak]" value="{{ old('dampak_infrastruktur.fasilitas_pendidikan_rusak', 0) }}" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all"></div>
                        <div><label class="block text-sm font-semibold text-slate-600 mb-1">Ibadah Rusak</label><input type="number" min="0" name="dampak_infrastruktur[tempat_ibadah_rusak]" value="{{ old('dampak_infrastruktur.tempat_ibadah_rusak', 0) }}" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all"></div>
                        <div><label class="block text-sm font-semibold text-slate-600 mb-1">Kantor Rusak</label><input type="number" min="0" name="dampak_infrastruktur[kantor_pemerintah_rusak]" value="{{ old('dampak_infrastruktur.kantor_pemerintah_rusak', 0) }}" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all"></div>
                        <div><label class="block text-sm font-semibold text-slate-600 mb-1">Jalan Rusak (Km)</label><input type="number" step="any" min="0" name="dampak_infrastruktur[jalan_rusak_km]" value="{{ old('dampak_infrastruktur.jalan_rusak_km', 0) }}" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all"></div>
                        <div><label class="block text-sm font-semibold text-slate-600 mb-1">Jembatan Putus</label><input type="number" min="0" name="dampak_infrastruktur[jembatan_putus]" value="{{ old('dampak_infrastruktur.jembatan_putus', 0) }}" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all"></div>
                        <div><label class="block text-sm font-semibold text-slate-600 mb-1">Jembatan Rusak</label><input type="number" min="0" name="dampak_infrastruktur[jembatan_rusak]" value="{{ old('dampak_infrastruktur.jembatan_rusak', 0) }}" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all"></div>
                        <div><label class="block text-sm font-semibold text-slate-600 mb-1">Listrik Padam (KK)</label><input type="number" min="0" name="dampak_infrastruktur[jaringan_listrik_padam_kk]" value="{{ old('dampak_infrastruktur.jaringan_listrik_padam_kk', 0) }}" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all"></div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-600 mb-1">Air Bersih Rusak</label>
                            <select name="dampak_infrastruktur[sarana_air_bersih_rusak]" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all">
                                <option value="0" {{ old('dampak_infrastruktur.sarana_air_bersih_rusak') == '0' ? 'selected' : '' }}>Tidak</option>
                                <option value="1" {{ old('dampak_infrastruktur.sarana_air_bersih_rusak') == '1' ? 'selected' : '' }}>Ya</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-600 mb-1">Komunikasi Putus</label>
                            <select name="dampak_infrastruktur[jaringan_komunikasi_putus]" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all">
                                <option value="0" {{ old('dampak_infrastruktur.jaringan_komunikasi_putus') == '0' ? 'selected' : '' }}>Tidak</option>
                                <option value="1" {{ old('dampak_infrastruktur.jaringan_komunikasi_putus') == '1' ? 'selected' : '' }}>Ya</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-slate-600 mb-1">Catatan Infrastruktur</label>
                            <input type="text" name="dampak_infrastruktur[catatan_infrastruktur]" value="{{ old('dampak_infrastruktur.catatan_infrastruktur') }}" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all">
                        </div>
                    </div>
                </div>

                <div class="p-4 bg-slate-50 rounded-xl border border-slate-100">
                    <h4 class="font-semibold text-slate-700 mb-3">Dampak Lingkungan</h4>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div><label class="block text-sm font-semibold text-slate-600 mb-1">Lahan Pertanian (Ha)</label><input type="number" step="any" min="0" name="dampak_lingkungan[lahan_pertanian_rusak_ha]" value="{{ old('dampak_lingkungan.lahan_pertanian_rusak_ha', 0) }}" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all"></div>
                        <div><label class="block text-sm font-semibold text-slate-600 mb-1">Hutan Terdampak (Ha)</label><input type="number" step="any" min="0" name="dampak_lingkungan[hutan_terdampak_ha]" value="{{ old('dampak_lingkungan.hutan_terdampak_ha', 0) }}" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all"></div>
                        <div><label class="block text-sm font-semibold text-slate-600 mb-1">Lahan Tercemar (Ha)</label><input type="number" step="any" min="0" name="dampak_lingkungan[lahan_tercemar_ha]" value="{{ old('dampak_lingkungan.lahan_tercemar_ha', 0) }}" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all"></div>
                        <div><label class="block text-sm font-semibold text-slate-600 mb-1">Ternak Terdampak (Ekor)</label><input type="number" min="0" name="dampak_lingkungan[ternak_terdampak_ekor]" value="{{ old('dampak_lingkungan.ternak_terdampak_ekor', 0) }}" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all"></div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-600 mb-1">Sumber Air Tercemar</label>
                            <select name="dampak_lingkungan[sumber_air_tercemar]" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all">
                                <option value="0" {{ old('dampak_lingkungan.sumber_air_tercemar') == '0' ? 'selected' : '' }}>Tidak</option>
                                <option value="1" {{ old('dampak_lingkungan.sumber_air_tercemar') == '1' ? 'selected' : '' }}>Ya</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-600 mb-1">Pencemaran Tanah</label>
                            <select name="dampak_lingkungan[pencemaran_tanah]" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all">
                                <option value="0" {{ old('dampak_lingkungan.pencemaran_tanah') == '0' ? 'selected' : '' }}>Tidak</option>
                                <option value="1" {{ old('dampak_lingkungan.pencemaran_tanah') == '1' ? 'selected' : '' }}>Ya</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-600 mb-1">Erosi/Sedimentasi</label>
                            <select name="dampak_lingkungan[erosi_sedimentasi]" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all">
                                <option value="0" {{ old('dampak_lingkungan.erosi_sedimentasi') == '0' ? 'selected' : '' }}>Tidak</option>
                                <option value="1" {{ old('dampak_lingkungan.erosi_sedimentasi') == '1' ? 'selected' : '' }}>Ya</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-600 mb-1">Kerusakan DAS</label>
                            <select name="dampak_lingkungan[kerusakan_daerah_aliran_sungai]" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all">
                                <option value="0" {{ old('dampak_lingkungan.kerusakan_daerah_aliran_sungai') == '0' ? 'selected' : '' }}>Tidak</option>
                                <option value="1" {{ old('dampak_lingkungan.kerusakan_daerah_aliran_sungai') == '1' ? 'selected' : '' }}>Ya</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-600 mb-1">Kerusakan Pesisir</label>
                            <select name="dampak_lingkungan[kerusakan_ekosistem_pesisir]" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all">
                                <option value="0" {{ old('dampak_lingkungan.kerusakan_ekosistem_pesisir') == '0' ? 'selected' : '' }}>Tidak</option>
                                <option value="1" {{ old('dampak_lingkungan.kerusakan_ekosistem_pesisir') == '1' ? 'selected' : '' }}>Ya</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-600 mb-1">Rehabilitasi Lahan</label>
                            <select name="dampak_lingkungan[butuh_rehabilitasi_lahan]" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all">
                                <option value="0" {{ old('dampak_lingkungan.butuh_rehabilitasi_lahan') == '0' ? 'selected' : '' }}>Tidak</option>
                                <option value="1" {{ old('dampak_lingkungan.butuh_rehabilitasi_lahan') == '1' ? 'selected' : '' }}>Ya</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-600 mb-1">Tingkat Kerusakan</label>
                            <select name="dampak_lingkungan[tingkat_kerusakan_lingkungan]" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all">
                                <option value="tidak_ada" {{ old('dampak_lingkungan.tingkat_kerusakan_lingkungan') == 'tidak_ada' ? 'selected' : '' }}>Tidak Ada</option>
                                <option value="ringan" {{ old('dampak_lingkungan.tingkat_kerusakan_lingkungan') == 'ringan' ? 'selected' : '' }}>Ringan</option>
                                <option value="sedang" {{ old('dampak_lingkungan.tingkat_kerusakan_lingkungan') == 'sedang' ? 'selected' : '' }}>Sedang</option>
                                <option value="berat" {{ old('dampak_lingkungan.tingkat_kerusakan_lingkungan') == 'berat' ? 'selected' : '' }}>Berat</option>
                                <option value="sangat_berat" {{ old('dampak_lingkungan.tingkat_kerusakan_lingkungan') == 'sangat_berat' ? 'selected' : '' }}>Sangat Berat</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-slate-600 mb-1">Catatan Lingkungan</label>
                            <input type="text" name="dampak_lingkungan[catatan_lingkungan]" value="{{ old('dampak_lingkungan.catatan_lingkungan') }}" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all">
                        </div>
                    </div>
                </div>

                <div class="p-4 bg-slate-50 rounded-xl border border-slate-100">
                    <h4 class="font-semibold text-slate-700 mb-3">Dampak Ekonomi</h4>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div><label class="block text-sm font-semibold text-slate-600 mb-1">Perumahan (Rp)</label><input type="number" min="0" name="dampak_ekonomi[kerugian_perumahan]" value="{{ old('dampak_ekonomi.kerugian_perumahan', 0) }}" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all"></div>
                        <div><label class="block text-sm font-semibold text-slate-600 mb-1">Pertanian (Rp)</label><input type="number" min="0" name="dampak_ekonomi[kerugian_pertanian]" value="{{ old('dampak_ekonomi.kerugian_pertanian', 0) }}" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all"></div>
                        <div><label class="block text-sm font-semibold text-slate-600 mb-1">Peternakan (Rp)</label><input type="number" min="0" name="dampak_ekonomi[kerugian_peternakan]" value="{{ old('dampak_ekonomi.kerugian_peternakan', 0) }}" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all"></div>
                        <div><label class="block text-sm font-semibold text-slate-600 mb-1">Perikanan (Rp)</label><input type="number" min="0" name="dampak_ekonomi[kerugian_perikanan]" value="{{ old('dampak_ekonomi.kerugian_perikanan', 0) }}" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all"></div>
                        <div><label class="block text-sm font-semibold text-slate-600 mb-1">UMKM (Rp)</label><input type="number" min="0" name="dampak_ekonomi[kerugian_umkm]" value="{{ old('dampak_ekonomi.kerugian_umkm', 0) }}" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all"></div>
                        <div><label class="block text-sm font-semibold text-slate-600 mb-1">Infrastruktur (Rp)</label><input type="number" min="0" name="dampak_ekonomi[kerugian_infrastruktur]" value="{{ old('dampak_ekonomi.kerugian_infrastruktur', 0) }}" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all"></div>
                        <div><label class="block text-sm font-semibold text-slate-600 mb-1">Lainnya (Rp)</label><input type="number" min="0" name="dampak_ekonomi[kerugian_lainnya]" value="{{ old('dampak_ekonomi.kerugian_lainnya', 0) }}" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all"></div>
                        <div><label class="block text-sm font-semibold text-slate-600 mb-1">Estimasi Total (Rp)</label><input type="number" min="0" name="dampak_ekonomi[estimasi_kerugian_total]" value="{{ old('dampak_ekonomi.estimasi_kerugian_total', 0) }}" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all"></div>
                        <div><label class="block text-sm font-semibold text-slate-600 mb-1">Mata Pencaharian Hilang</label><input type="number" min="0" name="dampak_ekonomi[mata_pencaharian_hilang]" value="{{ old('dampak_ekonomi.mata_pencaharian_hilang', 0) }}" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all"></div>
                        <div><label class="block text-sm font-semibold text-slate-600 mb-1">Usaha Terdampak</label><input type="number" min="0" name="dampak_ekonomi[usaha_terdampak]" value="{{ old('dampak_ekonomi.usaha_terdampak', 0) }}" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all"></div>
                        <div><label class="block text-sm font-semibold text-slate-600 mb-1">Metodologi Estimasi</label><input type="text" name="dampak_ekonomi[metodologi_estimasi]" value="{{ old('dampak_ekonomi.metodologi_estimasi') }}" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all"></div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-slate-600 mb-1">Catatan Ekonomi</label>
                            <input type="text" name="dampak_ekonomi[catatan_ekonomi]" value="{{ old('dampak_ekonomi.catatan_ekonomi') }}" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 3: Narasi & Kebutuhan -->
            <div x-show="activeTab === 2" x-transition.opacity.duration.300ms class="space-y-6" style="display: none;">
                <h3 class="text-lg font-bold text-slate-800 border-b border-slate-100 pb-3">Narasi & Kebutuhan Lapangan</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Fase Narasi <span class="text-rose-500">*</span></label>
                        <select name="narasi_kejadian[fase]" required class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all">
                            <option value="saat_bencana" {{ old('narasi_kejadian.fase') == 'saat_bencana' ? 'selected' : '' }}>Saat Bencana</option>
                            <option value="pra_bencana" {{ old('narasi_kejadian.fase') == 'pra_bencana' ? 'selected' : '' }}>Pra Bencana</option>
                            <option value="pasca_bencana" {{ old('narasi_kejadian.fase') == 'pasca_bencana' ? 'selected' : '' }}>Pasca Bencana</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Judul Laporan <span class="text-rose-500">*</span></label>
                        <input type="text" name="narasi_kejadian[judul_narasi]" required value="{{ old('narasi_kejadian.judul_narasi') }}" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all" placeholder="Kondisi Jalur Evakuasi Terputus">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Isi Narasi <span class="text-rose-500">*</span></label>
                        <textarea name="narasi_kejadian[isi_narasi]" rows="4" required class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all" placeholder="Tuliskan laporan situasi selengkapnya...">{{ old('narasi_kejadian.isi_narasi') }}</textarea>
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

            <!-- Step 4: Konfirmasi & Simpan -->
            <div x-show="activeTab === 3" x-transition.opacity.duration.300ms class="space-y-6" style="display: none;">
                <h3 class="text-lg font-bold text-slate-800 border-b border-slate-100 pb-3">Konfirmasi & Simpan</h3>

                <div class="p-6 bg-slate-50 border border-slate-200 rounded-xl text-center max-w-2xl mx-auto mt-8">
                    <i class="bi bi-shield-check text-5xl text-emerald-500 mb-4 inline-block"></i>
                    <h4 class="text-xl font-bold text-slate-800 mb-2">Pastikan Data Telah Akurat</h4>
                    <p class="text-slate-600 mb-6">Dengan menyimpan, data akan diproses oleh mesin skoring otomatis.</p>

                    <button id="submitBtn" type="submit" class="px-8 py-3 rounded-xl font-bold text-white bg-emerald-500 hover:bg-emerald-600 shadow-lg shadow-emerald-500/30 transition-all flex items-center justify-center gap-2 w-full mx-auto">
                        <i class="bi bi-cloud-arrow-up-fill"></i> Simpan Laporan Assessment
                    </button>
                </div>
            </div>

            <!-- Wizard Footer -->
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

    @push('scripts')
    <script>
        const hasErrors = {{ $errors->any() ? 'true' : 'false' }};
        const errorFields = @json($errors->keys());

        document.addEventListener('alpine:init', () => {
            Alpine.data('wizard', () => ({
                activeTab: 0,
                kebutuhanMendesakList: @json(old('kebutuhan_mendesak', [])),
                tabs: [
                    { name: 'Informasi & Lokasi', fields: ['jenis_laporan', 'waktu_assesment', 'cakupan_wilayah_deskripsi', 'biodata_kejadian.tanggal_mulai_kejadian', 'biodata_kejadian.kronologi_singkat'] },
                    { name: 'Dampak Kerusakan', fields: ['dampak_manusia.meninggal', 'dampak_manusia.hilang', 'dampak_infrastruktur.rumah_rusak_berat', 'dampak_infrastruktur.rumah_rusak_sedang', 'dampak_lingkungan.lahan_pertanian_rusak_ha', 'dampak_ekonomi.kerugian_perumahan'] },
                    { name: 'Narasi & Kebutuhan', fields: ['narasi_kejadian.fase', 'narasi_kejadian.judul_narasi', 'narasi_kejadian.isi_narasi'] },
                    { name: 'Konfirmasi', fields: [] }
                ],
                init() {
                    if(hasErrors) {
                        for(let i=0; i<this.tabs.length; i++) {
                            if(this.tabHasErrors(i)) {
                                this.activeTab = i;
                                break;
                            }
                        }
                    }
                },
                tabHasErrors(index) {
                    if(!hasErrors) return false;
                    const fields = this.tabs[index].fields;
                    for(let i=0; i<fields.length; i++) {
                        if(errorFields.some(e => e === fields[i] || e.startsWith(fields[i]+'.'))) {
                            return true;
                        }
                    }
                    return false;
                }
            }));
        });

        function validateFormBypass() {
            const form = document.getElementById('assessment-form');
            if(!form.checkValidity()) {
                form.reportValidity();
                return false;
            }

            const btn = document.getElementById('submitBtn');
            btn.innerHTML = '<i class="bi bi-arrow-repeat animate-spin"></i> Menyimpan...';
            btn.classList.add('opacity-75', 'cursor-not-allowed');
            return true;
        }
    </script>
    @endpush
