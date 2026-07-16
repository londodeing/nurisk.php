<x-app-layout>
    <x-slot name="header">Assessment Extended Form</x-slot>

    <div class="mb-6 p-4 bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl flex flex-col md:flex-row justify-between items-center gap-4">
        <div class="flex items-center gap-4">
            <div class="p-3 bg-indigo-100 text-indigo-600 rounded-xl">
                <i class="bi bi-file-earmark-medical text-2xl"></i>
            </div>
            <div>
                <h2 class="text-xl font-bold text-slate-800">Form Assessment Komprehensif</h2>
                <p class="text-sm text-slate-500">Isi data assessment untuk insiden{{ $insidenId ? ' #' . $insidenId : '' }}.</p>
            </div>
        </div>
    </div>

    @php $insidenId = $insiden->id_insiden ?? null; @endphp
    <form method="POST" action="{{ $insidenId ? route('insiden.assessment.store', $insidenId) : '#' }}" x-data="wizard">
        @csrf
        @if($insidenId)
        <input type="hidden" name="uuid_insiden" value="{{ $insiden->uuid_insiden }}">
        @endif

        <!-- Wizard Navigation -->
        <div class="mb-6 overflow-x-auto">
            <div class="flex items-center justify-between min-w-max p-2 bg-white/50 backdrop-blur-md rounded-xl border border-white/40 shadow-sm" id="wizard-nav">
                <template x-for="(tab, index) in tabs" :key="index">
                    <button type="button"
                        @click="activeTab = index"
                        :class="activeTab === index ? 'bg-white shadow-md text-indigo-600 ring-1 ring-indigo-100' : 'text-slate-500 hover:bg-white/40'"
                        class="px-5 py-2.5 rounded-lg font-semibold text-sm transition-all duration-300 flex items-center gap-2">
                        <span class="flex items-center justify-center w-6 h-6 rounded-full text-xs"
                              :class="activeTab === index ? 'bg-indigo-100' : 'bg-slate-100'">
                            <span x-text="index + 1"></span>
                        </span>
                        <span x-text="tab.name"></span>
                    </button>
                </template>
            </div>
        </div>

        <!-- Wizard Content -->
        <div class="bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl p-6 lg:p-8 relative min-h-[500px]">

            <!-- Tab 1: Informasi & Lokasi -->
            <div x-show="activeTab === 0" x-transition.opacity.duration.300ms class="space-y-6">
                <h3 class="text-lg font-bold text-slate-800 border-b border-slate-100 pb-3">Informasi Kejadian & Lokasi</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Tanggal Kejadian <span class="text-rose-500">*</span></label>
                        <input type="date" name="event_date" required class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring focus:ring-indigo-200 transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Waktu Kejadian</label>
                        <input type="time" name="event_time" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Kecamatan</label>
                        <input type="text" name="kecamatan" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Desa</label>
                        <input type="text" name="desa" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Alamat Spesifik</label>
                        <textarea name="alamat_spesifik" rows="2" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Latitude</label>
                        <input type="number" step="any" name="latitude" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Longitude</label>
                        <input type="number" step="any" name="longitude" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Jenis Laporan</label>
                        <select name="jenis_laporan" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all">
                            <option value="kaji_cepat">Kaji Cepat</option>
                            <option value="pendataan_lanjutan">Pendataan Lanjutan</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Tab 2: Dampak Kerusakan -->
            <div x-show="activeTab === 1" x-transition.opacity.duration.300ms class="space-y-6" style="display: none;">
                <h3 class="text-lg font-bold text-slate-800 border-b border-slate-100 pb-3">Dampak Manusia & Kerusakan</h3>

                <div class="p-4 bg-slate-50 rounded-xl border border-slate-100">
                    <h4 class="font-semibold text-slate-700 mb-3">Korban Jiwa</h4>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-600 mb-1">Meninggal</label>
                            <input type="number" min="0" name="dampak_manusia[meninggal]" value="0" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-600 mb-1">Hilang</label>
                            <input type="number" min="0" name="dampak_manusia[hilang]" value="0" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-600 mb-1">Luka Berat</label>
                            <input type="number" min="0" name="dampak_manusia[luka_berat]" value="0" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-600 mb-1">Luka Ringan</label>
                            <input type="number" min="0" name="dampak_manusia[luka_ringan]" value="0" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-600 mb-1">Terdampak Jiwa</label>
                            <input type="number" min="0" name="dampak_manusia[dampak_manusia]" value="0" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-600 mb-1">Pengungsi Jiwa</label>
                            <input type="number" min="0" name="dampak_manusia[pengungsi_jiwa]" value="0" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-600 mb-1">Pengungsi KK</label>
                            <input type="number" min="0" name="dampak_manusia[pengungsi_kk]" value="0" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all">
                        </div>
                    </div>
                </div>

                <div class="p-4 bg-slate-50 rounded-xl border border-slate-100">
                    <h4 class="font-semibold text-slate-700 mb-3">Kerusakan Rumah</h4>
                    <div class="grid grid-cols-3 gap-4">
                        <div><label class="block text-sm font-semibold text-slate-600 mb-1">Berat</label><input type="number" min="0" name="dampak_rumah[berat]" value="0" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all"></div>
                        <div><label class="block text-sm font-semibold text-slate-600 mb-1">Sedang</label><input type="number" min="0" name="dampak_rumah[sedang]" value="0" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all"></div>
                        <div><label class="block text-sm font-semibold text-slate-600 mb-1">Ringan</label><input type="number" min="0" name="dampak_rumah[ringan]" value="0" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all"></div>
                    </div>
                </div>

                <div class="p-4 bg-slate-50 rounded-xl border border-slate-100">
                    <h4 class="font-semibold text-slate-700 mb-3">Fasilitas Umum Rusak</h4>
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                        <div><label class="block text-xs font-semibold text-slate-600 mb-1">Sanitasi</label><input type="number" min="0" name="dampak_fasum[sanitas]" value="0" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all"></div>
                        <div><label class="block text-xs font-semibold text-slate-600 mb-1">Pendidikan</label><input type="number" min="0" name="dampak_fasum[pendidikan]" value="0" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all"></div>
                        <div><label class="block text-xs font-semibold text-slate-600 mb-1">Kesehatan</label><input type="number" min="0" name="dampak_fasum[kesehatan]" value="0" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all"></div>
                        <div><label class="block text-xs font-semibold text-slate-600 mb-1">Ibadah</label><input type="number" min="0" name="dampak_fasum[ibadah]" value="0" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all"></div>
                        <div><label class="block text-xs font-semibold text-slate-600 mb-1">Jembatan</label><input type="number" min="0" name="dampak_fasum[jembatan]" value="0" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all"></div>
                    </div>
                </div>

                <div class="p-4 bg-slate-50 rounded-xl border border-slate-100">
                    <h4 class="font-semibold text-slate-700 mb-3">Sarana Vital & Lingkungan</h4>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div><label class="block text-xs font-semibold text-slate-600 mb-1">Air Bersih Rusak</label><input type="number" min="0" name="dampak_vital[air]" value="0" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all"></div>
                        <div><label class="block text-xs font-semibold text-slate-600 mb-1">Listrik Padam (KK)</label><input type="number" min="0" name="dampak_vital[listrik]" value="0" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all"></div>
                        <div><label class="block text-xs font-semibold text-slate-600 mb-1">Telekomunikasi Rusak</label><input type="number" min="0" name="dampak_vital[telkom]" value="0" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all"></div>
                        <div><label class="block text-xs font-semibold text-slate-600 mb-1">Jalan Rusak (Km)</label><input type="number" step="any" min="0" name="dampak_vital[jalan]" value="0" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all"></div>
                        <div><label class="block text-xs font-semibold text-slate-600 mb-1">Sawah Rusak (Ha)</label><input type="number" step="any" min="0" name="dampak_lingkungan[sawah]" value="0" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all"></div>
                        <div><label class="block text-xs font-semibold text-slate-600 mb-1">Unggas (Ekor)</label><input type="number" min="0" name="dampak_lingkungan[unggas]" value="0" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all"></div>
                        <div><label class="block text-xs font-semibold text-slate-600 mb-1">Kaki Empat (Ekor)</label><input type="number" min="0" name="dampak_lingkungan[kaki_empat]" value="0" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all"></div>
                        <div><label class="block text-xs font-semibold text-slate-600 mb-1">Perikanan Kolam (Ha)</label><input type="number" step="any" min="0" name="dampak_lingkungan[perikanan_kolam]" value="0" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all"></div>
                        <div><label class="block text-xs font-semibold text-slate-600 mb-1">Perikanan Nelayan (Unit)</label><input type="number" min="0" name="dampak_lingkungan[perikanan_nelayan]" value="0" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all"></div>
                    </div>
                </div>
            </div>

            <!-- Tab 3: Kebutuhan Lapangan -->
            <div x-show="activeTab === 2" x-transition.opacity.duration.300ms class="space-y-6" style="display: none;">
                <h3 class="text-lg font-bold text-slate-800 border-b border-slate-100 pb-3">Kebutuhan Lapangan</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Kondisi Mutakhir / Narasi</label>
                        <textarea name="kondisi_mutakhir" rows="3" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all" placeholder="Deskripsikan kondisi terkini di lapangan..."></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Upaya Penanganan</label>
                        <textarea name="upaya_penanganan" rows="3" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all" placeholder="Upaya yang sudah dilakukan..."></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Kebutuhan Dana</label>
                        <textarea name="kebutuhan[dana]" rows="2" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Kebutuhan Logistik</label>
                        <textarea name="kebutuhan[logistik]" rows="2" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Kebutuhan Medis</label>
                        <textarea name="kebutuhan[medis]" rows="2" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Kebutuhan Relawan</label>
                        <textarea name="kebutuhan[relawan]" rows="2" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 transition-all"></textarea>
                    </div>
                </div>
            </div>

            <!-- Tab 4: Konfirmasi & Simpan -->
            <div x-show="activeTab === 3" x-transition.opacity.duration.300ms class="space-y-6" style="display: none;">
                <h3 class="text-lg font-bold text-slate-800 border-b border-slate-100 pb-3">Konfirmasi & Simpan</h3>

                <div class="p-6 bg-slate-50 border border-slate-200 rounded-xl text-center max-w-2xl mx-auto mt-8">
                    <i class="bi bi-shield-check text-5xl text-emerald-500 mb-4 inline-block"></i>
                    <h4 class="text-xl font-bold text-slate-800 mb-2">Pastikan Data Telah Akurat</h4>
                    <p class="text-slate-600 mb-6">Dengan menyimpan, assessment akan diproses oleh sistem.</p>

                    <button type="submit" class="px-8 py-3 rounded-xl font-bold text-white bg-emerald-500 hover:bg-emerald-600 shadow-lg shadow-emerald-500/30 transition-all flex items-center justify-center gap-2 w-full mx-auto">
                        <i class="bi bi-cloud-arrow-up-fill"></i> Simpan Assessment Komprehensif
                    </button>
                </div>
            </div>

            <!-- Wizard Footer -->
            <div class="mt-8 pt-6 border-t border-slate-100 flex justify-between items-center">
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
        document.addEventListener('alpine:init', () => {
            Alpine.data('wizard', () => ({
                activeTab: 0,
                tabs: [
                    { name: 'Informasi & Lokasi' },
                    { name: 'Dampak Kerusakan' },
                    { name: 'Kebutuhan Lapangan' },
                    { name: 'Konfirmasi' }
                ]
            }));
        });
    </script>
    @endpush
</x-app-layout>
