<x-app-layout>
    <x-slot name="header">Detail Aset</x-slot>

    <!-- Header Panel -->
    <div class="mb-6 p-6 bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl flex flex-col md:flex-row justify-between items-start md:items-center gap-6 relative overflow-hidden">
        <!-- Decorative bg -->
        <div class="absolute top-0 right-0 w-64 h-64 bg-slate-100 rounded-full blur-3xl -mr-20 -mt-20 pointer-events-none"></div>

        <div class="flex items-start gap-4 relative z-10">
            <div class="w-16 h-16 bg-sky-100 text-sky-600 rounded-2xl flex items-center justify-center border border-sky-200 shrink-0 shadow-sm">
                <i class="bi bi-truck text-3xl"></i>
            </div>
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <h2 class="text-2xl font-black text-slate-800 tracking-tight">Mobil Rescue Triton 4x4</h2>
                    <span class="px-2 py-1 bg-emerald-100 text-emerald-700 text-[10px] font-bold uppercase rounded border border-emerald-200">
                        <i class="bi bi-check-circle-fill"></i> Baik
                    </span>
                </div>
                <p class="text-slate-500 font-mono text-sm font-bold bg-slate-100 inline-block px-2 py-0.5 rounded border border-slate-200">AST-2026-001</p>
                <p class="text-sm text-slate-500 mt-1">Kendaraan Operasional &bull; PWNU Jatim</p>
            </div>
        </div>

        <div class="flex gap-3 relative z-10 w-full md:w-auto">
            <button class="flex-1 md:flex-none px-4 py-2 border border-slate-200 bg-white text-slate-600 font-bold rounded-xl hover:bg-slate-50 transition-all flex justify-center items-center gap-2 shadow-sm">
                <i class="bi bi-pencil"></i> Edit
            </button>
            <button class="flex-1 md:flex-none px-4 py-2 bg-indigo-600 text-white font-bold rounded-xl shadow-md shadow-indigo-500/30 hover:bg-indigo-700 transition-all flex justify-center items-center gap-2">
                <i class="bi bi-send-fill"></i> Deploy Aset
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Kolom Kiri: Detail Data -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl overflow-hidden relative">
                <div class="px-5 py-4 border-b border-slate-100 bg-white/50 flex justify-between items-center">
                    <span class="font-bold text-slate-700"><i class="bi bi-info-circle text-sky-500"></i> Informasi Spesifik</span>
                </div>
                <div class="p-5 space-y-4">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Tahun Pengadaan</p>
                        <p class="text-sm font-semibold text-slate-800">2021</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Sumber Dana</p>
                        <p class="text-sm font-semibold text-slate-800">Hibah Pemprov Jatim</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Nilai Perolehan</p>
                        <p class="text-sm font-semibold text-slate-800">Rp 450.000.000</p>
                    </div>
                    <div class="pt-4 border-t border-slate-100">
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Dokumen BPKB</p>
                        <a href="#" class="inline-flex items-center gap-2 text-sm text-sky-600 font-bold hover:underline">
                            <i class="bi bi-file-earmark-pdf text-rose-500"></i> BPKB_Triton_001.pdf
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Lokasi Saat Ini -->
            <div class="bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl overflow-hidden relative">
                <div class="px-5 py-4 border-b border-slate-100 bg-white/50 flex justify-between items-center">
                    <span class="font-bold text-slate-700"><i class="bi bi-geo-alt-fill text-rose-500"></i> Posisi Saat Ini</span>
                </div>
                <div class="h-40 bg-slate-200 w-full relative">
                    <!-- Placeholder Map -->
                    <div class="absolute inset-0 flex items-center justify-center text-slate-400">
                        <i class="bi bi-map text-4xl opacity-50"></i>
                    </div>
                </div>
                <div class="p-4 bg-slate-50">
                    <p class="text-sm font-bold text-slate-800">Gudang Logistik PWNU</p>
                    <p class="text-xs text-slate-500 mt-0.5">Jl. Masjid Al Akbar Timur No.9, Surabaya</p>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Riwayat Pemeliharaan & Deployment -->
        <div class="lg:col-span-2 space-y-6">
            
            <div class="bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-white/50 flex justify-between items-center">
                    <span class="font-bold text-slate-700 flex items-center gap-2">
                        <i class="bi bi-wrench-adjustable text-amber-500"></i> Riwayat Pemeliharaan
                    </span>
                    <button class="text-xs font-bold text-amber-600 bg-amber-50 px-3 py-1.5 rounded-lg border border-amber-200 hover:bg-amber-100 transition-colors">
                        Catat Servis
                    </button>
                </div>
                <div class="p-0">
                    <ul class="divide-y divide-slate-100">
                        <li class="p-6 hover:bg-slate-50 transition-colors">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <h4 class="font-bold text-slate-800">Ganti Oli & Filter Rutin</h4>
                                    <p class="text-xs text-slate-500">Servis Berkala per 10.000 KM</p>
                                </div>
                                <span class="text-xs font-bold text-slate-400">12 Ags 2026</span>
                            </div>
                            <div class="flex items-center gap-4 mt-3">
                                <span class="px-2.5 py-1 bg-slate-100 text-slate-600 rounded text-xs font-bold border border-slate-200">
                                    Biaya: Rp 1.250.000
                                </span>
                                <span class="text-xs font-bold text-emerald-600"><i class="bi bi-check-lg"></i> Selesai</span>
                            </div>
                        </li>
                        <li class="p-6 hover:bg-slate-50 transition-colors">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <h4 class="font-bold text-slate-800">Ganti Ban MT (4 pcs)</h4>
                                    <p class="text-xs text-slate-500">Persiapan musim hujan</p>
                                </div>
                                <span class="text-xs font-bold text-slate-400">05 Feb 2026</span>
                            </div>
                            <div class="flex items-center gap-4 mt-3">
                                <span class="px-2.5 py-1 bg-slate-100 text-slate-600 rounded text-xs font-bold border border-slate-200">
                                    Biaya: Rp 8.000.000
                                </span>
                                <span class="text-xs font-bold text-emerald-600"><i class="bi bi-check-lg"></i> Selesai</span>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Riwayat Deployment -->
            <div class="bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-white/50 flex justify-between items-center">
                    <span class="font-bold text-slate-700 flex items-center gap-2">
                        <i class="bi bi-rocket-takeoff text-indigo-500"></i> Riwayat Deployment Bencana
                    </span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-100">
                            <tr>
                                <th class="px-6 py-3">Insiden</th>
                                <th class="px-6 py-3">Lokasi</th>
                                <th class="px-6 py-3">Waktu Berangkat</th>
                                <th class="px-6 py-3">Waktu Kembali</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 font-bold">Erupsi Semeru</td>
                                <td class="px-6 py-4">Lumajang</td>
                                <td class="px-6 py-4"><span class="text-indigo-600 font-medium">10 Des 2025</span></td>
                                <td class="px-6 py-4">25 Des 2025</td>
                            </tr>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 font-bold">Banjir Bandang Karanganyar</td>
                                <td class="px-6 py-4">Demak</td>
                                <td class="px-6 py-4"><span class="text-indigo-600 font-medium">12 Feb 2025</span></td>
                                <td class="px-6 py-4">20 Feb 2025</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
