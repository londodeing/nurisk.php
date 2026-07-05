<x-app-layout>
    <x-slot name="header">Registrasi Aset Baru</x-slot>

    <div class="mb-6 p-4 bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl flex items-center gap-4">
        <a href="{{ url('inventaris') }}" class="p-2 bg-slate-100 text-slate-600 rounded-xl hover:bg-slate-200 transition-colors">
            <i class="bi bi-arrow-left text-xl"></i>
        </a>
        <div>
            <h2 class="text-xl font-bold text-slate-800">Form Registrasi Aset</h2>
            <p class="text-sm text-slate-500">Masukkan data aset inventaris PWNU Jatim secara lengkap.</p>
        </div>
    </div>

    <form class="space-y-6">
        <div class="bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl p-6 lg:p-8 relative overflow-hidden">
            <!-- Decorative circle -->
            <div class="absolute -right-20 -bottom-20 w-64 h-64 bg-sky-500/5 rounded-full blur-3xl pointer-events-none"></div>
            
            <h3 class="text-lg font-bold text-slate-800 border-b border-slate-100 pb-3 mb-6 relative z-10">Informasi Utama Aset</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 relative z-10">
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Nama Aset <span class="text-rose-500">*</span></label>
                    <input type="text" placeholder="Cth: Mobil Rescue Triton 4x4" class="w-full rounded-xl border-slate-200 focus:border-sky-500 focus:ring focus:ring-sky-200 transition-all">
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Kategori <span class="text-rose-500">*</span></label>
                    <select class="w-full rounded-xl border-slate-200 focus:border-sky-500 focus:ring focus:ring-sky-200 transition-all text-slate-600">
                        <option value="">Pilih Kategori</option>
                        <option>Kendaraan Operasional</option>
                        <option>Peralatan Medis</option>
                        <option>Perlengkapan Posko</option>
                        <option>Elektronik & Mesin</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Pemilik (Unit) <span class="text-rose-500">*</span></label>
                    <select class="w-full rounded-xl border-slate-200 focus:border-sky-500 focus:ring focus:ring-sky-200 transition-all text-slate-600">
                        <option value="">Pilih Unit Pemilik</option>
                        <option>PWNU Jatim</option>
                        <option>PCNU Surabaya</option>
                        <option>PCNU Lumajang</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Tahun Pengadaan</label>
                    <input type="number" placeholder="YYYY" min="1990" max="2100" class="w-full rounded-xl border-slate-200 focus:border-sky-500 focus:ring focus:ring-sky-200 transition-all">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Sumber Dana</label>
                    <select class="w-full rounded-xl border-slate-200 focus:border-sky-500 focus:ring focus:ring-sky-200 transition-all text-slate-600">
                        <option>Pembelian Mandiri</option>
                        <option>Hibah Pemerintah</option>
                        <option>Donasi Ummat</option>
                        <option>CSR Perusahaan</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Nilai Perolehan (Rp)</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 font-bold">Rp</span>
                        <input type="number" class="w-full pl-10 pr-4 rounded-xl border-slate-200 focus:border-sky-500 focus:ring focus:ring-sky-200 transition-all">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Dapat Dikerahkan Untuk Bencana?</label>
                    <label class="relative inline-flex items-center cursor-pointer mt-2">
                        <input type="checkbox" value="" class="sr-only peer" checked>
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-sky-500"></div>
                        <span class="ml-3 text-sm font-medium text-slate-600">Ya, masukkan ke aset operasional</span>
                    </label>
                </div>

            </div>
        </div>

        <div class="flex justify-end gap-4 mt-6">
            <button type="button" class="px-6 py-2.5 rounded-xl font-bold text-slate-600 border border-slate-200 hover:bg-slate-50 transition-colors">Batal</button>
            <button type="button" class="px-6 py-2.5 rounded-xl font-bold text-white bg-sky-500 hover:bg-sky-600 shadow-md shadow-sky-500/30 transition-all">Simpan Aset</button>
        </div>
    </form>
</x-app-layout>
