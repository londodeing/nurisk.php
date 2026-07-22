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
                    <div class="bg-indigo-50/30 p-4 rounded-xl border border-indigo-100">
                        <label class="block text-sm font-semibold text-slate-700 mb-1"><i class="bi bi-file-earmark-text text-indigo-500 mr-2"></i>Jenis Laporan <span class="text-rose-500">*</span></label>
                        <select name="jenis_laporan" required class="w-full rounded-xl border-indigo-200 focus:border-indigo-500 focus:ring focus:ring-indigo-200 transition-all">
                            <option value="kaji_cepat" {{ old('jenis_laporan', $assessment->jenis_laporan) === 'kaji_cepat' ? 'selected' : '' }}>Kaji Cepat</option>
                            <option value="pendataan_lanjutan" {{ old('jenis_laporan', $assessment->jenis_laporan) === 'pendataan_lanjutan' ? 'selected' : '' }}>Pendataan Lanjutan</option>
                        </select>
                    </div>
                    <div class="bg-indigo-50/30 p-4 rounded-xl border border-indigo-100">
                        <label class="block text-sm font-semibold text-slate-700 mb-1"><i class="bi bi-calendar-event text-indigo-500 mr-2"></i>Waktu Assessment <span class="text-rose-500">*</span></label>
                        <input type="datetime-local" name="waktu_assesment" required
                               value="{{ old('waktu_assesment', $assessment->waktu_assesment?->format('Y-m-d\TH:i')) }}"
                               class="w-full rounded-xl border-indigo-200 focus:border-indigo-500 transition-all" max="{{ now()->format('Y-m-d\TH:i') }}">
                    </div>
                    @php $lokasi = $assessment->lokasiDetail; @endphp
                    <div class="bg-indigo-50/30 p-4 rounded-xl border border-indigo-100">
                        <label class="block text-sm font-semibold text-slate-700 mb-1"><i class="bi bi-geo-alt text-indigo-500 mr-2"></i>Kecamatan</label>
                        <select name="lokasi_detail[id_kec]" id="select_kecamatan" class="w-full rounded-xl border-indigo-200 focus:border-indigo-500 transition-all">
                            <option value="">-- Pilih Kecamatan --</option>
                            @if(isset($kecamatanList))
                                @foreach($kecamatanList as $kec)
                                    <option value="{{ $kec->id_kec }}" {{ old('lokasi_detail.id_kec', $lokasi?->id_kec) == $kec->id_kec ? 'selected' : '' }}>{{ $kec->nama_kec }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="bg-indigo-50/30 p-4 rounded-xl border border-indigo-100">
                        <label class="block text-sm font-semibold text-slate-700 mb-1"><i class="bi bi-pin-map text-indigo-500 mr-2"></i>Desa</label>
                        <select name="lokasi_detail[id_desa]" id="select_desa" class="w-full rounded-xl border-indigo-200 focus:border-indigo-500 transition-all">
                            <option value="">-- Pilih Desa --</option>
                            @if(isset($desaList))
                                @foreach($desaList as $desa)
                                    <option value="{{ $desa->id_desa }}" {{ old('lokasi_detail.id_desa', $lokasi?->id_desa) == $desa->id_desa ? 'selected' : '' }}>{{ $desa->nama_desa }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="md:col-span-2 bg-indigo-50/30 p-4 rounded-xl border border-indigo-100">
                        <label class="block text-sm font-semibold text-slate-700 mb-1"><i class="bi bi-map text-indigo-500 mr-2"></i>Cakupan Wilayah / Alamat Spesifik <span class="text-rose-500">*</span></label>
                        <textarea name="cakupan_wilayah_deskripsi" rows="2" required minlength="10" maxlength="250"
                                  class="w-full rounded-xl border-indigo-200 focus:border-indigo-500 transition-all">{{ old('cakupan_wilayah_deskripsi', $assessment->cakupan_wilayah_deskripsi) }}</textarea>
                    </div>
                    <div class="md:col-span-2 bg-indigo-50/30 p-4 rounded-xl border border-indigo-100">
                        <label class="block text-sm font-semibold text-slate-700 mb-1"><i class="bi bi-geo text-indigo-500 mr-2"></i>Region Terdampak</label>
                        <textarea name="lokasi_detail[region_terdampak]" rows="2"
                                  placeholder="Contoh: RT 01/RW 02, Dusun A"
                                  class="w-full rounded-xl border-indigo-200 focus:border-indigo-500 transition-all">{{ old('lokasi_detail.region_terdampak', $lokasi?->region_terdampak) }}</textarea>
                    </div>
                    <div class="bg-indigo-50/30 p-4 rounded-xl border border-indigo-100">
                        <label class="block text-sm font-semibold text-slate-700 mb-1"><i class="bi bi-compass text-indigo-500 mr-2"></i>Latitude</label>
                        <input type="number" step="any" min="-11" max="6" name="latitude"
                               value="{{ old('latitude', $assessment->latitude) }}"
                               class="w-full rounded-xl border-indigo-200 focus:border-indigo-500 transition-all font-mono">
                    </div>
                    <div class="bg-indigo-50/30 p-4 rounded-xl border border-indigo-100">
                        <label class="block text-sm font-semibold text-slate-700 mb-1"><i class="bi bi-compass text-indigo-500 mr-2"></i>Longitude</label>
                        <input type="number" step="any" min="95" max="141" name="longitude"
                               value="{{ old('longitude', $assessment->longitude) }}"
                               class="w-full rounded-xl border-indigo-200 focus:border-indigo-500 transition-all font-mono">
                    </div>
                </div>
            </div>

            {{-- Tab 2: Biodata Kejadian --}}
            <div x-show="activeTab === 1" x-transition.opacity.duration.300ms class="space-y-6" style="display: none;">
                <h3 class="text-lg font-bold text-slate-800 border-b border-slate-100 pb-3">Biodata Bencana</h3>
                @php $biodata = $assessment->biodataKejadian; @endphp
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-indigo-50/30 p-4 rounded-xl border border-indigo-100">
                        <label class="block text-sm font-semibold text-slate-700 mb-1"><i class="bi bi-calendar-check text-indigo-500 mr-2"></i>Tanggal Mulai Kejadian <span class="text-rose-500">*</span></label>
                        <input type="date" name="biodata_kejadian[tanggal_mulai_kejadian]" required
                               value="{{ old('biodata_kejadian.tanggal_mulai_kejadian', $biodata?->tanggal_mulai_kejadian) }}"
                               {{ Route::currentRouteName() === 'insiden.assessment.create' ? 'readonly' : '' }}
                               class="w-full rounded-xl border-indigo-200 focus:border-indigo-500 transition-all {{ Route::currentRouteName() === 'insiden.assessment.create' ? 'bg-slate-100 cursor-not-allowed' : '' }}">
                    </div>
                    <div class="bg-indigo-50/30 p-4 rounded-xl border border-indigo-100">
                        <label class="block text-sm font-semibold text-slate-700 mb-1"><i class="bi bi-clock text-indigo-500 mr-2"></i>Jam Mulai</label>
                        <input type="time" name="biodata_kejadian[jam_mulai_kejadian]"
                               value="{{ old('biodata_kejadian.jam_mulai_kejadian', $biodata?->jam_mulai_kejadian) }}"
                               {{ Route::currentRouteName() === 'insiden.assessment.create' ? 'readonly' : '' }}
                               class="w-full rounded-xl border-indigo-200 focus:border-indigo-500 transition-all {{ Route::currentRouteName() === 'insiden.assessment.create' ? 'bg-slate-100 cursor-not-allowed' : '' }}">
                    </div>
                    <div class="md:col-span-2 bg-indigo-50/30 p-4 rounded-xl border border-indigo-100">
                        <label class="block text-sm font-semibold text-slate-700 mb-1"><i class="bi bi-card-text text-indigo-500 mr-2"></i>Kronologi Singkat <span class="text-rose-500">*</span></label>
                        <textarea name="biodata_kejadian[kronologi_singkat]" rows="3" required maxlength="1000"
                                  class="w-full rounded-xl border-indigo-200 focus:border-indigo-500 transition-all">{{ old('biodata_kejadian.kronologi_singkat', $biodata?->kronologi_singkat) }}</textarea>
                    </div>
                    <div class="bg-indigo-50/30 p-4 rounded-xl border border-indigo-100">
                        <label class="block text-sm font-semibold text-slate-700 mb-1"><i class="bi bi-bar-chart text-indigo-500 mr-2"></i>Skala Kejadian</label>
                        <select name="biodata_kejadian[skala_kejadian]" class="w-full rounded-xl border-indigo-200 focus:border-indigo-500 transition-all">
                            @foreach(['lokal', 'kecamatan', 'kabupaten', 'provinsi', 'nasional'] as $skala)
                            <option value="{{ $skala }}" {{ old('biodata_kejadian.skala_kejadian', $biodata?->skala_kejadian) === $skala ? 'selected' : '' }}>{{ ucfirst($skala) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="bg-indigo-50/30 p-4 rounded-xl border border-indigo-100">
                        <label class="block text-sm font-semibold text-slate-700 mb-1"><i class="bi bi-exclamation-triangle text-indigo-500 mr-2"></i>Penyebab Utama</label>
                        <input type="text" name="biodata_kejadian[penyebab_utama]"
                               value="{{ old('biodata_kejadian.penyebab_utama', $biodata?->penyebab_utama) }}"
                               class="w-full rounded-xl border-indigo-200 focus:border-indigo-500 transition-all">
                    </div>
                </div>
            </div>

            {{-- Tab 3: Narasi --}}
            <div x-show="activeTab === 2" x-transition.opacity.duration.300ms class="space-y-6" style="display: none;">
                <h3 class="text-lg font-bold text-slate-800 border-b border-slate-100 pb-3">Narasi / Catatan Lapangan</h3>
                @php $narasi = ($assessment->narasiKejadian ?? collect())->first(); @endphp
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-indigo-50/30 p-4 rounded-xl border border-indigo-100">
                        <label class="block text-sm font-semibold text-slate-700 mb-1"><i class="bi bi-flag-fill text-indigo-500 mr-2"></i>Fase Narasi <span class="text-rose-500">*</span></label>
                        <select name="narasi_kejadian[fase]" required class="w-full rounded-xl border-indigo-200 focus:border-indigo-500 transition-all">
                            @foreach(['saat_bencana', 'pra_bencana', 'pasca_bencana'] as $fase)
                            <option value="{{ $fase }}" {{ old('narasi_kejadian.fase', $narasi?->fase) === $fase ? 'selected' : '' }}>{{ str_replace('_', ' ', ucfirst($fase)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="bg-indigo-50/30 p-4 rounded-xl border border-indigo-100">
                        <label class="block text-sm font-semibold text-slate-700 mb-1"><i class="bi bi-type-h1 text-indigo-500 mr-2"></i>Judul Narasi <span class="text-rose-500">*</span></label>
                        <input type="text" name="narasi_kejadian[judul_narasi]" required
                               value="{{ old('narasi_kejadian.judul_narasi', $narasi?->judul_narasi) }}"
                               class="w-full rounded-xl border-indigo-200 focus:border-indigo-500 transition-all">
                    </div>
                    <div class="md:col-span-2 bg-indigo-50/30 p-4 rounded-xl border border-indigo-100">
                        <label class="block text-sm font-semibold text-slate-700 mb-1"><i class="bi bi-card-text text-indigo-500 mr-2"></i>Isi Narasi <span class="text-rose-500">*</span></label>
                        <textarea name="narasi_kejadian[isi_narasi]" rows="6" required
                                  class="w-full rounded-xl border-indigo-200 focus:border-indigo-500 transition-all">{{ old('narasi_kejadian.isi_narasi', $narasi?->isi_narasi) }}</textarea>
                    </div>

                    <div class="md:col-span-2 space-y-4">
                        @php $narasiDet = $assessment->narasiDetail; @endphp
                        
                        <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                            <label class="block text-sm font-semibold text-slate-700 mb-1"><i class="bi bi-bullseye text-slate-500 mr-2"></i>Sebaran Dampak</label>
                            <textarea name="narasi_detail[sebaran_dampak]" rows="3"
                                    class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all">{{ old('narasi_detail.sebaran_dampak', $narasiDet?->sebaran_dampak) }}</textarea>
                        </div>
                        <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                            <label class="block text-sm font-semibold text-slate-700 mb-1"><i class="bi bi-tools text-slate-500 mr-2"></i>Upaya Penanganan</label>
                            <textarea name="narasi_detail[upaya_penanganan]" rows="3"
                                    class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all">{{ old('narasi_detail.upaya_penanganan', $narasiDet?->upaya_penanganan) }}</textarea>
                        </div>
                        <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                            <label class="block text-sm font-semibold text-slate-700 mb-1"><i class="bi bi-exclamation-triangle text-slate-500 mr-2"></i>Kendala Lapangan</label>
                            <textarea name="narasi_detail[kendala_lapangan]" rows="3"
                                    class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all">{{ old('narasi_detail.kendala_lapangan', $narasiDet?->kendala_lapangan) }}</textarea>
                        </div>
                        <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                            <label class="block text-sm font-semibold text-slate-700 mb-1"><i class="bi bi-info-circle text-slate-500 mr-2"></i>Kendala Tambahan (Opsional)</label>
                            <textarea name="narasi_detail[kendala_tambahan]" rows="3"
                                    class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all">{{ old('narasi_detail.kendala_tambahan', $narasiDet?->kendala_tambahan) }}</textarea>
                        </div>
                        <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                            <label class="block text-sm font-semibold text-slate-700 mb-1"><i class="bi bi-lightbulb text-slate-500 mr-2"></i>Rekomendasi Aksi</label>
                            <textarea name="narasi_detail[rekomendasi_aksi]" rows="3"
                                    class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all">{{ old('narasi_detail.rekomendasi_aksi', $narasiDet?->rekomendasi_aksi) }}</textarea>
                        </div>
                    </div>

                    <div class="md:col-span-2 mt-6">
                        <h4 class="text-md font-bold text-slate-700 mb-3 border-t pt-4"><i class="bi bi-ui-checks-grid text-slate-500 mr-2"></i>Kebutuhan Numerik (Terstruktur)</h4>
                        <div class="space-y-4">
                            @php $kebLama = $assessment->kebutuhanNumerik; @endphp
                            <template x-for="(item, idx) in kebutuhanMendesakList" :key="idx">
                                <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 bg-indigo-50/50 p-4 rounded-xl relative border border-indigo-100">
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-600 mb-1"><i class="bi bi-box-seam text-indigo-500 mr-1"></i>Pilih Item</label>
                                        <select :name="'kebutuhan_numerik['+idx+'][id_item]'" x-model="item.id_item" required class="w-full text-sm rounded-xl border-indigo-200 focus:border-indigo-500">
                                            <option value="">-- Pilih Kebutuhan --</option>
                                            @foreach($kebutuhanMaster as $km)
                                                <option value="{{ $km->id_item }}">{{ $km->nama_item }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-600 mb-1"><i class="bi bi-calculator text-indigo-500 mr-1"></i>Jumlah Dibutuhkan</label>
                                        <input type="number" min="0" step="any" :name="'kebutuhan_numerik['+idx+'][jumlah_dibutuhkan]'" x-model="item.jumlah_dibutuhkan" required class="w-full text-sm rounded-xl border-indigo-200 focus:border-indigo-500 font-mono">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-600 mb-1"><i class="bi bi-check2-circle text-emerald-500 mr-1"></i>Jumlah Tersedia</label>
                                        <input type="number" min="0" step="any" :name="'kebutuhan_numerik['+idx+'][jumlah_tersedia]'" x-model="item.jumlah_tersedia" class="w-full text-sm rounded-xl border-indigo-200 focus:border-indigo-500 font-mono">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-600 mb-1"><i class="bi bi-rulers text-indigo-500 mr-1"></i>Satuan</label>
                                        <select :name="'kebutuhan_numerik['+idx+'][satuan]'" x-model="item.satuan" required class="w-full text-sm rounded-xl border-indigo-200 focus:border-indigo-500">
                                            <option value="">-- Pilih Satuan --</option>
                                            <option value="unit">Unit</option>
                                            <option value="paket">Paket</option>
                                            <option value="kg">Kg</option>
                                            <option value="dus">Dus</option>
                                            <option value="liter">Liter</option>
                                            <option value="lembar">Lembar</option>
                                            <option value="set">Set</option>
                                            <option value="lusin">Lusin</option>
                                            <option value="kaleng">Kaleng</option>
                                            <option value="orang">Orang</option>
                                            <option value="kk">KK</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-600 mb-1"><i class="bi bi-exclamation-circle text-orange-500 mr-1"></i>Prioritas</label>
                                        <select :name="'kebutuhan_numerik['+idx+'][prioritas]'" x-model="item.prioritas" class="w-full text-sm rounded-xl border-indigo-200 focus:border-indigo-500">
                                            <option value="normal">Normal</option>
                                            <option value="penting">Penting</option>
                                            <option value="darurat">Darurat</option>
                                        </select>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label class="block text-xs font-semibold text-slate-600 mb-1"><i class="bi bi-chat-text text-indigo-500 mr-1"></i>Keterangan</label>
                                        <div class="flex gap-2">
                                            <input type="text" :name="'kebutuhan_numerik['+idx+'][keterangan]'" x-model="item.keterangan" class="w-full text-sm rounded-xl border-indigo-200 focus:border-indigo-500">
                                            <button type="button" @click="kebutuhanMendesakList.splice(idx, 1)" class="p-2 text-rose-500 hover:bg-rose-100 hover:text-rose-600 bg-white rounded-xl transition-colors border border-rose-200">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </template>
                            <button type="button" @click="kebutuhanMendesakList.push({ id_item: '', jumlah_dibutuhkan: 1, jumlah_tersedia: 0, satuan: '', prioritas: 'normal', keterangan: '' })" class="px-4 py-2 bg-indigo-50 border border-dashed border-indigo-300 text-indigo-600 rounded-xl text-sm font-semibold hover:bg-indigo-100 transition-colors flex items-center gap-2">
                                <i class="bi bi-plus-lg"></i> Tambah Kebutuhan Numerik
                            </button>
                        </div>
                    </div>
                    
                    <div class="md:col-span-2 mt-6">
                        <h4 class="text-md font-bold text-slate-700 mb-3 border-t pt-4"><i class="bi bi-list-check text-slate-500 mr-2"></i>Kebutuhan Lanjutan (Narasi)</h4>
                        @php $kebLanjut = $assessment->kebutuhanLanjutan; @endphp
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-indigo-50/30 p-4 rounded-xl border border-indigo-100">
                                <label class="block text-sm font-semibold text-slate-700 mb-1"><i class="bi bi-person-hearts text-indigo-500 mr-2"></i>Kebutuhan Relawan</label>
                                <textarea name="kebutuhan_lanjutan[kebutuhan_relawan]" rows="2"
                                          class="w-full rounded-xl border-indigo-200 focus:border-indigo-500 transition-all">{{ old('kebutuhan_lanjutan.kebutuhan_relawan', $kebLanjut?->kebutuhan_relawan) }}</textarea>
                            </div>
                            <div class="bg-indigo-50/30 p-4 rounded-xl border border-indigo-100">
                                <label class="block text-sm font-semibold text-slate-700 mb-1"><i class="bi bi-box-seam text-indigo-500 mr-2"></i>Kebutuhan Logistik</label>
                                <textarea name="kebutuhan_lanjutan[kebutuhan_logistik]" rows="2"
                                          class="w-full rounded-xl border-indigo-200 focus:border-indigo-500 transition-all">{{ old('kebutuhan_lanjutan.kebutuhan_logistik', $kebLanjut?->kebutuhan_logistik) }}</textarea>
                            </div>
                            <div class="bg-indigo-50/30 p-4 rounded-xl border border-indigo-100">
                                <label class="block text-sm font-semibold text-slate-700 mb-1"><i class="bi bi-tools text-indigo-500 mr-2"></i>Kebutuhan Peralatan</label>
                                <textarea name="kebutuhan_lanjutan[kebutuhan_peralatan]" rows="2"
                                          class="w-full rounded-xl border-indigo-200 focus:border-indigo-500 transition-all">{{ old('kebutuhan_lanjutan.kebutuhan_peralatan', $kebLanjut?->kebutuhan_peralatan) }}</textarea>
                            </div>
                            <div class="bg-indigo-50/30 p-4 rounded-xl border border-indigo-100">
                                <label class="block text-sm font-semibold text-slate-700 mb-1"><i class="bi bi-heart-pulse text-indigo-500 mr-2"></i>Kebutuhan Medis</label>
                                <textarea name="kebutuhan_lanjutan[kebutuhan_medis]" rows="2"
                                          class="w-full rounded-xl border-indigo-200 focus:border-indigo-500 transition-all">{{ old('kebutuhan_lanjutan.kebutuhan_medis', $kebLanjut?->kebutuhan_medis) }}</textarea>
                            </div>
                            <div class="bg-indigo-50/30 p-4 rounded-xl border border-indigo-100">
                                <label class="block text-sm font-semibold text-slate-700 mb-1"><i class="bi bi-cup-hot text-indigo-500 mr-2"></i>Kebutuhan Pangan</label>
                                <textarea name="kebutuhan_lanjutan[kebutuhan_pangan]" rows="2"
                                          class="w-full rounded-xl border-indigo-200 focus:border-indigo-500 transition-all">{{ old('kebutuhan_lanjutan.kebutuhan_pangan', $kebLanjut?->kebutuhan_pangan) }}</textarea>
                            </div>
                            <div class="bg-indigo-50/30 p-4 rounded-xl border border-indigo-100">
                                <label class="block text-sm font-semibold text-slate-700 mb-1"><i class="bi bi-three-dots text-indigo-500 mr-2"></i>Kebutuhan Lainnya</label>
                                <textarea name="kebutuhan_lanjutan[kebutuhan_lainnya]" rows="2"
                                          class="w-full rounded-xl border-indigo-200 focus:border-indigo-500 transition-all">{{ old('kebutuhan_lanjutan.kebutuhan_lainnya', $kebLanjut?->kebutuhan_lainnya) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tab 4: Korban Jiwa --}}
            <div x-show="activeTab === 3" x-transition.opacity.duration.300ms class="space-y-6" style="display: none;">
                <h3 class="text-lg font-bold text-slate-800 border-b border-slate-100 pb-3">Dampak Korban Jiwa & Pengungsi</h3>
                @php $dm = $assessment->dampakManusiaV2; @endphp
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-rose-50/50 p-4 rounded-xl border border-rose-100">
                        <label class="block text-xs font-semibold text-slate-600 mb-2"><i class="bi bi-person-x-fill text-rose-500 mr-1"></i>Meninggal Dunia</label>
                        <input type="number" min="{{ $dm?->meninggal ?? 0 }}" name="dampak_manusia[meninggal]"
                                value="{{ old('dampak_manusia.meninggal', $dm?->meninggal ?? 0) }}"
                                class="w-full rounded-lg border-rose-200 focus:border-rose-500 font-mono">
                    </div>
                    <div class="bg-rose-50/50 p-4 rounded-xl border border-rose-100">
                        <label class="block text-xs font-semibold text-slate-600 mb-2"><i class="bi bi-question-circle-fill text-rose-500 mr-1"></i>Hilang</label>
                        <input type="number" min="0" name="dampak_manusia[hilang]"
                                value="{{ old('dampak_manusia.hilang', $dm?->hilang ?? 0) }}"
                                class="w-full rounded-lg border-rose-200 focus:border-rose-500 font-mono">
                    </div>
                    <div class="bg-orange-50/50 p-4 rounded-xl border border-orange-100">
                        <label class="block text-xs font-semibold text-slate-600 mb-2"><i class="bi bi-bandaid-fill text-orange-500 mr-1"></i>Luka Berat</label>
                        <input type="number" min="0" name="dampak_manusia[luka_berat]"
                                value="{{ old('dampak_manusia.luka_berat', $dm?->luka_berat ?? 0) }}"
                                class="w-full rounded-lg border-orange-200 focus:border-orange-500 font-mono">
                    </div>
                    <div class="bg-yellow-50/50 p-4 rounded-xl border border-yellow-100">
                        <label class="block text-xs font-semibold text-slate-600 mb-2"><i class="bi bi-bandaid text-yellow-500 mr-1"></i>Luka Ringan</label>
                        <input type="number" min="0" name="dampak_manusia[luka_ringan]"
                                value="{{ old('dampak_manusia.luka_ringan', $dm?->luka_ringan ?? 0) }}"
                                class="w-full rounded-lg border-yellow-200 focus:border-yellow-500 font-mono">
                    </div>
                </div>

                <h4 class="font-semibold text-slate-700 mt-4 border-t pt-4">Terdampak & Pengungsi</h4>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-blue-50/50 p-4 rounded-xl border border-blue-100">
                        <label class="block text-xs font-semibold text-slate-600 mb-2"><i class="bi bi-people text-blue-500 mr-1"></i>Terdampak Jiwa</label>
                        <input type="number" min="0" name="dampak_manusia[menderita_mengungsi]"
                                value="{{ old('dampak_manusia.menderita_mengungsi', $dm?->terdampak_jiwa ?? 0) }}"
                                class="w-full rounded-lg border-blue-200 focus:border-blue-500 font-mono">
                    </div>
                    <div class="bg-blue-50/50 p-4 rounded-xl border border-blue-100">
                        <label class="block text-xs font-semibold text-slate-600 mb-2"><i class="bi bi-house-door text-blue-500 mr-1"></i>Terdampak KK</label>
                        <input type="number" min="0" name="dampak_manusia[terdampak_kk]"
                                value="{{ old('dampak_manusia.terdampak_kk', $dm?->terdampak_kk ?? 0) }}"
                                class="w-full rounded-lg border-blue-200 focus:border-blue-500 font-mono">
                    </div>
                    <div class="bg-indigo-50/50 p-4 rounded-xl border border-indigo-100">
                        <label class="block text-xs font-semibold text-slate-600 mb-2"><i class="bi bi-person-walking text-indigo-500 mr-1"></i>Pengungsi Jiwa</label>
                        <input type="number" min="0" name="dampak_manusia[pengungsi_jiwa]"
                                value="{{ old('dampak_manusia.pengungsi_jiwa', $dm?->pengungsi_jiwa ?? 0) }}"
                                class="w-full rounded-lg border-indigo-200 focus:border-indigo-500 font-mono">
                    </div>
                    <div class="bg-indigo-50/50 p-4 rounded-xl border border-indigo-100">
                        <label class="block text-xs font-semibold text-slate-600 mb-2"><i class="bi bi-houses text-indigo-500 mr-1"></i>Pengungsi KK</label>
                        <input type="number" min="0" name="dampak_manusia[pengungsi_kk]"
                                value="{{ old('dampak_manusia.pengungsi_kk', $dm?->pengungsi_kk ?? 0) }}"
                                class="w-full rounded-lg border-indigo-200 focus:border-indigo-500 font-mono">
                    </div>
                </div>

                <h4 class="font-semibold text-slate-700 mt-4 border-t pt-4">Kelompok Rentan (Pengungsi)</h4>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-purple-50/50 p-4 rounded-xl border border-purple-100">
                        <label class="block text-xs font-semibold text-slate-600 mb-2"><i class="bi bi-emoji-smile text-purple-500 mr-1"></i>Balita</label>
                        <input type="number" min="0" name="dampak_manusia[pengungsi_balita]"
                                value="{{ old('dampak_manusia.pengungsi_balita', $dm?->pengungsi_balita ?? 0) }}"
                                class="w-full rounded-lg border-purple-200 focus:border-purple-500 font-mono">
                    </div>
                    <div class="bg-purple-50/50 p-4 rounded-xl border border-purple-100">
                        <label class="block text-xs font-semibold text-slate-600 mb-2"><i class="bi bi-person-lines-fill text-purple-500 mr-1"></i>Lansia</label>
                        <input type="number" min="0" name="dampak_manusia[pengungsi_lansia]"
                                value="{{ old('dampak_manusia.pengungsi_lansia', $dm?->pengungsi_lansia ?? 0) }}"
                                class="w-full rounded-lg border-purple-200 focus:border-purple-500 font-mono">
                    </div>
                    <div class="bg-purple-50/50 p-4 rounded-xl border border-purple-100">
                        <label class="block text-xs font-semibold text-slate-600 mb-2"><i class="bi bi-person-wheelchair text-purple-500 mr-1"></i>Disabilitas</label>
                        <input type="number" min="0" name="dampak_manusia[pengungsi_disabilitas]"
                                value="{{ old('dampak_manusia.pengungsi_disabilitas', $dm?->pengungsi_disabilitas ?? 0) }}"
                                class="w-full rounded-lg border-purple-200 focus:border-purple-500 font-mono">
                    </div>
                    <div class="bg-purple-50/50 p-4 rounded-xl border border-purple-100">
                        <label class="block text-xs font-semibold text-slate-600 mb-2"><i class="bi bi-gender-female text-purple-500 mr-1"></i>Ibu Hamil</label>
                        <input type="number" min="0" name="dampak_manusia[pengungsi_ibu_hamil]"
                                value="{{ old('dampak_manusia.pengungsi_ibu_hamil', $dm?->pengungsi_ibu_hamil ?? 0) }}"
                                class="w-full rounded-lg border-purple-200 focus:border-purple-500 font-mono">
                    </div>
                </div>
            </div>

            {{-- Tab 5: Infrastruktur --}}
            <div x-show="activeTab === 4" x-transition.opacity.duration.300ms class="space-y-6" style="display: none;">
                <h3 class="text-lg font-bold text-slate-800 border-b border-slate-100 pb-3">Dampak Infrastruktur</h3>
                @php $infra = $assessment->dampakInfrastruktur; @endphp
                <div class="space-y-6">
                    <div>
                        <h4 class="font-semibold text-slate-700 mb-3"><i class="bi bi-house-door-fill text-slate-500 mr-2"></i>Dampak Perumahan</h4>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div class="bg-red-50/50 p-4 rounded-xl border border-red-100">
                                <label class="block text-xs font-semibold text-slate-600 mb-2"><i class="bi bi-house-x text-red-500 mr-1"></i>Rusak Berat</label>
                                <input type="number" min="0" name="dampak_infrastruktur[rumah_rusak_berat]"
                                        value="{{ old('dampak_infrastruktur.rumah_rusak_berat', $infra?->rumah_rusak_berat ?? 0) }}"
                                        class="w-full rounded-lg border-red-200 focus:border-red-500 font-mono">
                            </div>
                            <div class="bg-orange-50/50 p-4 rounded-xl border border-orange-100">
                                <label class="block text-xs font-semibold text-slate-600 mb-2"><i class="bi bi-house-exclamation text-orange-500 mr-1"></i>Rusak Sedang</label>
                                <input type="number" min="0" name="dampak_infrastruktur[rumah_rusak_sedang]"
                                        value="{{ old('dampak_infrastruktur.rumah_rusak_sedang', $infra?->rumah_rusak_sedang ?? 0) }}"
                                        class="w-full rounded-lg border-orange-200 focus:border-orange-500 font-mono">
                            </div>
                            <div class="bg-yellow-50/50 p-4 rounded-xl border border-yellow-100">
                                <label class="block text-xs font-semibold text-slate-600 mb-2"><i class="bi bi-house text-yellow-500 mr-1"></i>Rusak Ringan</label>
                                <input type="number" min="0" name="dampak_infrastruktur[rumah_rusak_ringan]"
                                        value="{{ old('dampak_infrastruktur.rumah_rusak_ringan', $infra?->rumah_rusak_ringan ?? 0) }}"
                                        class="w-full rounded-lg border-yellow-200 focus:border-yellow-500 font-mono">
                            </div>
                            <div class="bg-cyan-50/50 p-4 rounded-xl border border-cyan-100">
                                <label class="block text-xs font-semibold text-slate-600 mb-2"><i class="bi bi-water text-cyan-500 mr-1"></i>Terendam</label>
                                <input type="number" min="0" name="dampak_infrastruktur[rumah_terendam]"
                                        value="{{ old('dampak_infrastruktur.rumah_terendam', $infra?->rumah_terendam ?? 0) }}"
                                        class="w-full rounded-lg border-cyan-200 focus:border-cyan-500 font-mono">
                            </div>
                            <div class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                                <label class="block text-xs font-semibold text-slate-600 mb-2"><i class="bi bi-exclamation-triangle text-slate-500 mr-1"></i>Terancam</label>
                                <input type="number" min="0" name="dampak_infrastruktur[rumah_terancam]"
                                        value="{{ old('dampak_infrastruktur.rumah_terancam', $rumah?->terancam ?? 0) }}"
                                        class="w-full rounded-lg border-slate-300 focus:border-slate-500 font-mono">
                            </div>
                        </div>
                    </div>

                    <div>
                        <h4 class="font-semibold text-slate-700 mb-3 border-t pt-4"><i class="bi bi-building-fill text-slate-500 mr-2"></i>Fasilitas Umum & Sosial</h4>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div class="bg-sky-50/50 p-4 rounded-xl border border-sky-100">
                                <label class="block text-xs font-semibold text-slate-600 mb-2"><i class="bi bi-hospital text-sky-500 mr-1"></i>Kesehatan</label>
                                <input type="number" min="0" name="dampak_infrastruktur[fasilitas_kesehatan_rusak]"
                                        value="{{ old('dampak_infrastruktur.fasilitas_kesehatan_rusak', $infra?->fasilitas_kesehatan_rusak ?? 0) }}"
                                        class="w-full rounded-lg border-sky-200 focus:border-sky-500 font-mono">
                            </div>
                            <div class="bg-sky-50/50 p-4 rounded-xl border border-sky-100">
                                <label class="block text-xs font-semibold text-slate-600 mb-2"><i class="bi bi-book text-sky-500 mr-1"></i>Pendidikan</label>
                                <input type="number" min="0" name="dampak_infrastruktur[fasilitas_pendidikan_rusak]"
                                        value="{{ old('dampak_infrastruktur.fasilitas_pendidikan_rusak', $infra?->fasilitas_pendidikan_rusak ?? 0) }}"
                                        class="w-full rounded-lg border-sky-200 focus:border-sky-500 font-mono">
                            </div>
                            <div class="bg-sky-50/50 p-4 rounded-xl border border-sky-100">
                                <label class="block text-xs font-semibold text-slate-600 mb-2"><i class="bi bi-moon-stars text-sky-500 mr-1"></i>Tempat Ibadah</label>
                                <input type="number" min="0" name="dampak_infrastruktur[tempat_ibadah_rusak]"
                                        value="{{ old('dampak_infrastruktur.tempat_ibadah_rusak', $infra?->tempat_ibadah_rusak ?? 0) }}"
                                        class="w-full rounded-lg border-sky-200 focus:border-sky-500 font-mono">
                            </div>
                            <div class="bg-sky-50/50 p-4 rounded-xl border border-sky-100">
                                <label class="block text-xs font-semibold text-slate-600 mb-2"><i class="bi bi-bank text-sky-500 mr-1"></i>Perkantoran</label>
                                <input type="number" min="0" name="dampak_infrastruktur[kantor_pemerintah_rusak]"
                                        value="{{ old('dampak_infrastruktur.kantor_pemerintah_rusak', $infra?->kantor_pemerintah_rusak ?? 0) }}"
                                        class="w-full rounded-lg border-sky-200 focus:border-sky-500 font-mono">
                            </div>
                            <div class="bg-sky-50/50 p-4 rounded-xl border border-sky-100">
                                <label class="block text-xs font-semibold text-slate-600 mb-2"><i class="bi bi-shop text-sky-500 mr-1"></i>Pasar</label>
                                <input type="number" min="0" name="dampak_infrastruktur[pasar]"
                                        value="{{ old('dampak_infrastruktur.pasar', $fasum?->pasar ?? 0) }}"
                                        class="w-full rounded-lg border-sky-200 focus:border-sky-500 font-mono">
                            </div>
                        </div>
                    </div>

                    <div>
                        <h4 class="font-semibold text-slate-700 mb-3 border-t pt-4"><i class="bi bi-cone-striped text-slate-500 mr-2"></i>Infrastruktur Vital</h4>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div class="bg-zinc-50 p-4 rounded-xl border border-zinc-200">
                                <label class="block text-xs font-semibold text-slate-600 mb-2"><i class="bi bi-signpost-split text-zinc-500 mr-1"></i>Jalan Rusak (Km)</label>
                                <input type="number" step="any" min="0" name="dampak_infrastruktur[jalan_rusak_km]"
                                        value="{{ old('dampak_infrastruktur.jalan_rusak_km', $infra?->jalan_rusak_km ?? 0) }}"
                                        class="w-full rounded-lg border-zinc-300 focus:border-zinc-500 font-mono">
                            </div>
                            <div class="bg-zinc-50 p-4 rounded-xl border border-zinc-200">
                                <label class="block text-xs font-semibold text-slate-600 mb-2"><i class="bi bi-bezier text-zinc-500 mr-1"></i>Jembatan Putus</label>
                                <input type="number" min="0" name="dampak_infrastruktur[jembatan_putus]"
                                        value="{{ old('dampak_infrastruktur.jembatan_putus', $infra?->jembatan_putus ?? 0) }}"
                                        class="w-full rounded-lg border-zinc-300 focus:border-zinc-500 font-mono">
                            </div>
                            <div class="bg-zinc-50 p-4 rounded-xl border border-zinc-200">
                                <label class="block text-xs font-semibold text-slate-600 mb-2"><i class="bi bi-bezier2 text-zinc-500 mr-1"></i>Jembatan Rusak</label>
                                <input type="number" min="0" name="dampak_infrastruktur[jembatan_rusak]"
                                        value="{{ old('dampak_infrastruktur.jembatan_rusak', $infra?->jembatan_rusak ?? 0) }}"
                                        class="w-full rounded-lg border-zinc-300 focus:border-zinc-500 font-mono">
                            </div>
                            <div class="bg-zinc-50 p-4 rounded-xl border border-zinc-200">
                                <label class="block text-xs font-semibold text-slate-600 mb-2"><i class="bi bi-lightning-fill text-zinc-500 mr-1"></i>Listrik Padam (KK)</label>
                                <input type="number" min="0" name="dampak_infrastruktur[jaringan_listrik_padam_kk]"
                                        value="{{ old('dampak_infrastruktur.jaringan_listrik_padam_kk', $infra?->jaringan_listrik_padam_kk ?? 0) }}"
                                        class="w-full rounded-lg border-zinc-300 focus:border-zinc-500 font-mono">
                            </div>
                            <div class="bg-zinc-50 p-4 rounded-xl border border-zinc-200">
                                <label class="block text-xs font-semibold text-slate-600 mb-2"><i class="bi bi-fuel-pump text-zinc-500 mr-1"></i>SPBU Rusak</label>
                                <input type="number" min="0" name="dampak_infrastruktur[spbu]"
                                        value="{{ old('dampak_infrastruktur.spbu', $fasum?->spbu ?? 0) }}"
                                        class="w-full rounded-lg border-zinc-300 focus:border-zinc-500 font-mono">
                            </div>
                            <div class="bg-zinc-50 p-4 rounded-xl border border-zinc-200">
                                <label class="block text-xs font-semibold text-slate-600 mb-2"><i class="bi bi-droplet text-zinc-500 mr-1"></i>Sanitasi Rusak</label>
                                <input type="number" min="0" name="dampak_infrastruktur[sanitasi]"
                                        value="{{ old('dampak_infrastruktur.sanitasi', $fasum?->sanitasi ?? 0) }}"
                                        class="w-full rounded-lg border-zinc-300 focus:border-zinc-500 font-mono">
                            </div>
                            <div class="bg-zinc-50 p-4 rounded-xl border border-zinc-200">
                                <label class="block text-xs font-semibold text-slate-600 mb-2"><i class="bi bi-water text-zinc-500 mr-1"></i>Air Bersih Rusak</label>
                                <select name="dampak_infrastruktur[sarana_air_bersih_rusak]" class="w-full rounded-lg border-zinc-300 focus:border-zinc-500">
                                    <option value="0" {{ old('dampak_infrastruktur.sarana_air_bersih_rusak', $infra?->sarana_air_bersih_rusak) == '0' ? 'selected' : '' }}>Tidak</option>
                                    <option value="1" {{ old('dampak_infrastruktur.sarana_air_bersih_rusak', $infra?->sarana_air_bersih_rusak) == '1' ? 'selected' : '' }}>Ya</option>
                                </select>
                            </div>
                            <div class="bg-zinc-50 p-4 rounded-xl border border-zinc-200">
                                <label class="block text-xs font-semibold text-slate-600 mb-2"><i class="bi bi-broadcast text-zinc-500 mr-1"></i>Komunikasi Putus</label>
                                <select name="dampak_infrastruktur[jaringan_komunikasi_putus]" class="w-full rounded-lg border-zinc-300 focus:border-zinc-500">
                                    <option value="0" {{ old('dampak_infrastruktur.jaringan_komunikasi_putus', $infra?->jaringan_komunikasi_putus) == '0' ? 'selected' : '' }}>Tidak</option>
                                    <option value="1" {{ old('dampak_infrastruktur.jaringan_komunikasi_putus', $infra?->jaringan_komunikasi_putus) == '1' ? 'selected' : '' }}>Ya</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="md:col-span-4 mt-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Catatan Tambahan Infrastruktur</label>
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
                <div class="space-y-6">
                    <div>
                        <h4 class="font-semibold text-slate-700 mb-3"><i class="bi bi-tree-fill text-emerald-500 mr-2"></i>Kerusakan Lahan & Hutan</h4>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            <div class="bg-emerald-50/50 p-4 rounded-xl border border-emerald-100">
                                <label class="block text-xs font-semibold text-slate-600 mb-2"><i class="bi bi-flower1 text-emerald-500 mr-1"></i>Lahan Pertanian Rusak (Ha)</label>
                                <input type="number" step="any" min="0" name="dampak_lingkungan[lahan_pertanian_rusak_ha]"
                                       value="{{ old('dampak_lingkungan.lahan_pertanian_rusak_ha', $ling?->lahan_pertanian_rusak_ha ?? 0) }}"
                                       class="w-full rounded-lg border-emerald-200 focus:border-emerald-500 font-mono">
                            </div>
                            <div class="bg-emerald-50/50 p-4 rounded-xl border border-emerald-100">
                                <label class="block text-xs font-semibold text-slate-600 mb-2"><i class="bi bi-tree text-emerald-500 mr-1"></i>Hutan Terdampak (Ha)</label>
                                <input type="number" step="any" min="0" name="dampak_lingkungan[hutan_terdampak_ha]"
                                       value="{{ old('dampak_lingkungan.hutan_terdampak_ha', $ling?->hutan_terdampak_ha ?? 0) }}"
                                       class="w-full rounded-lg border-emerald-200 focus:border-emerald-500 font-mono">
                            </div>
                            <div class="bg-emerald-50/50 p-4 rounded-xl border border-emerald-100">
                                <label class="block text-xs font-semibold text-slate-600 mb-2"><i class="bi bi-bug text-emerald-500 mr-1"></i>Lahan Tercemar (Ha)</label>
                                <input type="number" step="any" min="0" name="dampak_lingkungan[lahan_tercemar_ha]"
                                       value="{{ old('dampak_lingkungan.lahan_tercemar_ha', $ling?->lahan_tercemar_ha ?? 0) }}"
                                       class="w-full rounded-lg border-emerald-200 focus:border-emerald-500 font-mono">
                            </div>
                        </div>
                    </div>

                    <div>
                        <h4 class="font-semibold text-slate-700 mb-3 border-t pt-4"><i class="bi bi-tencent-qq text-indigo-500 mr-2"></i>Dampak Hewan & Perikanan</h4>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div class="bg-indigo-50/50 p-4 rounded-xl border border-indigo-100">
                                <label class="block text-xs font-semibold text-slate-600 mb-2"><i class="bi bi-twitter text-indigo-500 mr-1"></i>Ternak Unggas (Ekor)</label>
                                <input type="number" min="0" name="dampak_lingkungan[unggas]"
                                       value="{{ old('dampak_lingkungan.unggas', $ling?->ternak_unggas_ekor ?? 0) }}"
                                       class="w-full rounded-lg border-indigo-200 focus:border-indigo-500 font-mono">
                            </div>
                            <div class="bg-indigo-50/50 p-4 rounded-xl border border-indigo-100">
                                <label class="block text-xs font-semibold text-slate-600 mb-2"><i class="bi bi-github text-indigo-500 mr-1"></i>Kaki Empat (Ekor)</label>
                                <input type="number" min="0" name="dampak_lingkungan[kaki_empat]"
                                       value="{{ old('dampak_lingkungan.kaki_empat', $ling?->ternak_kaki_empat_ekor ?? 0) }}"
                                       class="w-full rounded-lg border-indigo-200 focus:border-indigo-500 font-mono">
                            </div>
                            <div class="bg-indigo-50/50 p-4 rounded-xl border border-indigo-100">
                                <label class="block text-xs font-semibold text-slate-600 mb-2"><i class="bi bi-water text-indigo-500 mr-1"></i>Perikanan Kolam (Ha)</label>
                                <input type="number" step="any" min="0" name="dampak_lingkungan[perikanan_kolam]"
                                       value="{{ old('dampak_lingkungan.perikanan_kolam', $ling?->perikanan_kolam_ha ?? 0) }}"
                                       class="w-full rounded-lg border-indigo-200 focus:border-indigo-500 font-mono">
                            </div>
                            <div class="bg-indigo-50/50 p-4 rounded-xl border border-indigo-100">
                                <label class="block text-xs font-semibold text-slate-600 mb-2"><i class="bi bi-tsunami text-indigo-500 mr-1"></i>Perikanan Nelayan (Unit)</label>
                                <input type="number" min="0" name="dampak_lingkungan[perikanan_nelayan]"
                                       value="{{ old('dampak_lingkungan.perikanan_nelayan', $ling?->perikanan_nelayan_unit ?? 0) }}"
                                       class="w-full rounded-lg border-indigo-200 focus:border-indigo-500 font-mono">
                            </div>
                        </div>
                    </div>

                    <div>
                        <h4 class="font-semibold text-slate-700 mb-3 border-t pt-4"><i class="bi bi-exclamation-triangle-fill text-rose-500 mr-2"></i>Pencemaran & Ekosistem</h4>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div class="bg-rose-50/50 p-4 rounded-xl border border-rose-100">
                                <label class="block text-xs font-semibold text-slate-600 mb-2"><i class="bi bi-droplet-half text-rose-500 mr-1"></i>Air Tercemar</label>
                                <select name="dampak_lingkungan[sumber_air_tercemar]" class="w-full rounded-lg border-rose-200 focus:border-rose-500">
                                    <option value="0" {{ old('dampak_lingkungan.sumber_air_tercemar', $ling?->sumber_air_tercemar) == '0' ? 'selected' : '' }}>Tidak</option>
                                    <option value="1" {{ old('dampak_lingkungan.sumber_air_tercemar', $ling?->sumber_air_tercemar) == '1' ? 'selected' : '' }}>Ya</option>
                                </select>
                            </div>
                            <div class="bg-rose-50/50 p-4 rounded-xl border border-rose-100">
                                <label class="block text-xs font-semibold text-slate-600 mb-2"><i class="bi bi-trash3 text-rose-500 mr-1"></i>Pencemaran Tanah</label>
                                <select name="dampak_lingkungan[pencemaran_tanah]" class="w-full rounded-lg border-rose-200 focus:border-rose-500">
                                    <option value="0" {{ old('dampak_lingkungan.pencemaran_tanah', $ling?->pencemaran_tanah) == '0' ? 'selected' : '' }}>Tidak</option>
                                    <option value="1" {{ old('dampak_lingkungan.pencemaran_tanah', $ling?->pencemaran_tanah) == '1' ? 'selected' : '' }}>Ya</option>
                                </select>
                            </div>
                            <div class="bg-rose-50/50 p-4 rounded-xl border border-rose-100">
                                <label class="block text-xs font-semibold text-slate-600 mb-2"><i class="bi bi-layers text-rose-500 mr-1"></i>Erosi/Sedimentasi</label>
                                <select name="dampak_lingkungan[erosi_sedimentasi]" class="w-full rounded-lg border-rose-200 focus:border-rose-500">
                                    <option value="0" {{ old('dampak_lingkungan.erosi_sedimentasi', $ling?->erosi_sedimentasi) == '0' ? 'selected' : '' }}>Tidak</option>
                                    <option value="1" {{ old('dampak_lingkungan.erosi_sedimentasi', $ling?->erosi_sedimentasi) == '1' ? 'selected' : '' }}>Ya</option>
                                </select>
                            </div>
                            <div class="bg-rose-50/50 p-4 rounded-xl border border-rose-100">
                                <label class="block text-xs font-semibold text-slate-600 mb-2"><i class="bi bi-water text-rose-500 mr-1"></i>Kerusakan DAS</label>
                                <select name="dampak_lingkungan[kerusakan_daerah_aliran_sungai]" class="w-full rounded-lg border-rose-200 focus:border-rose-500">
                                    <option value="0" {{ old('dampak_lingkungan.kerusakan_daerah_aliran_sungai', $ling?->kerusakan_daerah_aliran_sungai) == '0' ? 'selected' : '' }}>Tidak</option>
                                    <option value="1" {{ old('dampak_lingkungan.kerusakan_daerah_aliran_sungai', $ling?->kerusakan_daerah_aliran_sungai) == '1' ? 'selected' : '' }}>Ya</option>
                                </select>
                            </div>
                            <div class="bg-rose-50/50 p-4 rounded-xl border border-rose-100">
                                <label class="block text-xs font-semibold text-slate-600 mb-2"><i class="bi bi-sunset text-rose-500 mr-1"></i>Kerusakan Pesisir</label>
                                <select name="dampak_lingkungan[kerusakan_pesisir]" class="w-full rounded-lg border-rose-200 focus:border-rose-500">
                                    <option value="0" {{ old('dampak_lingkungan.kerusakan_pesisir', $ling?->kerusakan_ekosistem_pesisir) == '0' ? 'selected' : '' }}>Tidak</option>
                                    <option value="1" {{ old('dampak_lingkungan.kerusakan_pesisir', $ling?->kerusakan_ekosistem_pesisir) == '1' ? 'selected' : '' }}>Ya</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tab 7: Dampak Ekonomi Komunal --}}
            <div x-show="activeTab === 6" x-transition.opacity.duration.300ms class="space-y-6" style="display: none;">
                <h3 class="text-lg font-bold text-slate-800 border-b border-slate-100 pb-3">Dampak Ekonomi Komunal</h3>
                @php $eko = $assessment->dampakEkonomi; @endphp
                <div class="space-y-6">
                    <div class="bg-amber-50/50 p-4 rounded-xl border border-amber-100">
                        <label class="block text-sm font-semibold text-slate-700 mb-2"><i class="bi bi-pie-chart-fill text-amber-500 mr-2"></i>Estimasi % Populasi Terdampak Ekonomi</label>
                        <select name="dampak_ekonomi[persentase]" class="w-full md:w-1/2 rounded-xl border-amber-200 focus:border-amber-500 transition-all font-semibold text-amber-800">
                            <option value="">-- Pilih Persentase --</option>
                            @foreach([
                                '< 25%' => '< 25% (Rendah)',
                                '25% - 50%' => '25% - 50% (Sedang)',
                                '51% - 75%' => '51% - 75% (Tinggi)',
                                '> 75%' => '> 75% (Lumpuh Total)',
                            ] as $val => $label)
                                <option value="{{ $val }}" {{ old('dampak_ekonomi.persentase', $eko?->persentase_ekonomi_terdampak) === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="border border-indigo-100 rounded-xl p-5 bg-indigo-50/30">
                        <h4 class="font-bold text-slate-700 mb-4 flex items-center"><i class="bi bi-briefcase-fill text-indigo-500 mr-2"></i>Sektor Mata Pencaharian Terbesar (Top 3)</h4>
                        @for ($i = 1; $i <= 3; $i++)
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end mb-4 border-b border-indigo-100 pb-4 last:border-0 last:mb-0 last:pb-0">
                            <div class="md:col-span-5">
                                <label class="block text-xs font-semibold text-slate-600 mb-1"><i class="bi bi-{{ $i }}-circle text-indigo-400 mr-1"></i>Nama Sektor</label>
                                <input type="text" name="dampak_ekonomi[sektor_{{ $i }}]" placeholder="Contoh: Pertanian"
                                       value="{{ old('dampak_ekonomi.sektor_'.$i, $eko?->{'sektor_pencaharian_'.$i}) }}"
                                       class="w-full rounded-lg border-indigo-200 focus:border-indigo-500">
                            </div>
                            <div class="md:col-span-3">
                                <label class="block text-xs font-semibold text-slate-600 mb-1">Kontribusi (%)</label>
                                <div class="relative">
                                    <input type="number" step="any" max="100" name="dampak_ekonomi[kontribusi_{{ $i }}]"
                                           value="{{ old('dampak_ekonomi.kontribusi_'.$i, $eko?->{'kontribusi_'.$i}) }}"
                                           class="w-full rounded-lg border-indigo-200 focus:border-indigo-500 font-mono pr-8">
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs font-bold">%</span>
                                </div>
                            </div>
                            <div class="md:col-span-4">
                                <label class="block text-xs font-semibold text-slate-600 mb-1">Status Kehilangan</label>
                                <select name="dampak_ekonomi[status_{{ $i }}]" class="w-full rounded-lg border-indigo-200 focus:border-indigo-500">
                                    <option value="">-- Status --</option>
                                    <option value="tidak_terdampak" {{ old('dampak_ekonomi.status_'.$i, $eko?->{'status_terdampak_'.$i}) === 'tidak_terdampak' ? 'selected' : '' }}>Tidak Terdampak</option>
                                    <option value="sementara" {{ old('dampak_ekonomi.status_'.$i, $eko?->{'status_terdampak_'.$i}) === 'sementara' ? 'selected' : '' }}>Sementara</option>
                                    <option value="permanen" {{ old('dampak_ekonomi.status_'.$i, $eko?->{'status_terdampak_'.$i}) === 'permanen' ? 'selected' : '' }}>Permanen</option>
                                </select>
                            </div>
                        </div>
                        @endfor
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Fasilitas Distribusi/Logistik</label>
                            <select name="dampak_ekonomi[distribusi]" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all">
                                <option value="">-- Pilih --</option>
                                <option value="berfungsi" {{ old('dampak_ekonomi.distribusi', $eko?->distribusi_hasil_panen) === 'berfungsi' ? 'selected' : '' }}>Berfungsi</option>
                                <option value="rusak_sebagian" {{ old('dampak_ekonomi.distribusi', $eko?->distribusi_hasil_panen) === 'rusak_sebagian' ? 'selected' : '' }}>Rusak Sebagian</option>
                                <option value="rusak_total" {{ old('dampak_ekonomi.distribusi', $eko?->distribusi_hasil_panen) === 'rusak_total' ? 'selected' : '' }}>Rusak Total / Lumpuh</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Fasilitas Pengolahan Kolektif</label>
                            <select name="dampak_ekonomi[fasilitas]" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all">
                                <option value="">-- Pilih --</option>
                                <option value="berfungsi" {{ old('dampak_ekonomi.fasilitas', $eko?->fasilitas_pengolahan_kolektif) === 'berfungsi' ? 'selected' : '' }}>Berfungsi</option>
                                <option value="rusak_sebagian" {{ old('dampak_ekonomi.fasilitas', $eko?->fasilitas_pengolahan_kolektif) === 'rusak_sebagian' ? 'selected' : '' }}>Rusak Sebagian</option>
                                <option value="rusak_total" {{ old('dampak_ekonomi.fasilitas', $eko?->fasilitas_pengolahan_kolektif) === 'rusak_total' ? 'selected' : '' }}>Rusak Total / Lumpuh</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tab 8: Simpan --}}
            <div x-show="activeTab === 7" x-transition.opacity.duration.300ms class="space-y-6" style="display: none;">
                <h3 class="text-lg font-bold text-slate-800 border-b border-slate-100 pb-3">Simpan Assessment</h3>

                <div class="bg-indigo-50/50 p-6 rounded-2xl border border-indigo-100 text-center mt-8">
                    <i class="bi bi-check-circle text-4xl text-indigo-500 mb-3"></i>
                    <h4 class="text-lg font-bold text-slate-800">Selesai Memasukkan Data</h4>
                    <p class="text-slate-600 text-sm mt-1 mb-6">Pastikan semua field bertanda <span class="text-rose-500">*</span> sudah terisi sebelum menyimpan.</p>
                    <button type="submit" @click="validateEditForm($event)" class="px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-lg shadow-indigo-500/30 transition-all text-lg w-full md:w-auto">
                        <i class="bi bi-save mr-2"></i> Simpan Assessment
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
        $kebutuhanMendesakJson = json_encode(old('kebutuhan_numerik', ($assessment->kebutuhanNumerik ?? collect())->map(fn($item) => [
            'id_item' => $item->id_item,
            'jumlah_dibutuhkan' => $item->jumlah_dibutuhkan,
            'jumlah_tersedia' => $item->jumlah_tersedia,
            'satuan' => $item->satuan,
            'prioritas' => $item->prioritas,
            'keterangan' => $item->keterangan,
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

        function validateEditForm(event) {
            const form = document.getElementById('assessment-edit-form') || document.getElementById('assessment-form') || event.target.closest('form');
            if (!form.checkValidity()) {
                if (event) event.preventDefault(); // Stop native HTML5 validation from throwing focus errors on hidden tabs
                
                const firstInvalid = form.querySelector(':invalid');
                if (firstInvalid) {
                    const tabDiv = firstInvalid.closest('[x-show]');
                    if (tabDiv) {
                        const match = tabDiv.getAttribute('x-show').match(/activeTab\s*===\s*(\d+)/);
                        if (match && window.Alpine) {
                            const wizardEl = document.querySelector('[x-data="wizard"]');
                            if (wizardEl) {
                                Alpine.$data(wizardEl).activeTab = parseInt(match[1]);
                                // Wait for Alpine transition to finish before focusing
                                setTimeout(() => {
                                    form.reportValidity();
                                    firstInvalid.focus();
                                }, 350);
                            }
                        }
                    }
                }
                return false;
            }

            const btn = form.querySelector('button[type="submit"]');
            if (btn) {
                btn.innerHTML = '<i class="bi bi-arrow-repeat animate-spin"></i> Menyimpan...';
                btn.classList.add('opacity-75', 'cursor-not-allowed');
            }
            return true;
        }
    </script>
    @endpush
