<x-app-layout>
    <x-slot name="header">Inventaris Aset PWNU</x-slot>

    <!-- Header & Stats Row -->
    <div class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="md:col-span-3 p-6 bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl flex flex-col md:flex-row justify-between items-center gap-4 relative overflow-hidden">
            <!-- Decorative shape -->
            <div class="absolute -right-10 -top-10 w-40 h-40 bg-sky-500/10 rounded-full blur-2xl pointer-events-none"></div>

            <div>
                <h2 class="text-2xl font-black text-slate-800 flex items-center gap-3">
                    <i class="bi bi-box-seam text-sky-500"></i> Manajemen Aset
                </h2>
                <p class="text-sm text-slate-500 font-medium mt-1">Kelola daftar inventaris, kondisi, dan kesiapan operasional.</p>
            </div>
            <div class="flex items-center gap-3 relative z-10">
                <button class="px-4 py-2 bg-white text-slate-600 font-bold border border-slate-200 shadow-sm rounded-xl hover:bg-slate-50 transition-all flex items-center gap-2">
                    <i class="bi bi-funnel"></i> Filter
                </button>
                <a href="{{ url('inventaris/create') }}" class="px-4 py-2 bg-sky-500 text-white font-bold border border-sky-600 shadow-md shadow-sky-500/30 rounded-xl hover:bg-sky-600 hover:-translate-y-0.5 transition-all flex items-center gap-2">
                    <i class="bi bi-plus-lg"></i> Tambah Aset
                </a>
            </div>
        </div>

        <div class="p-6 bg-gradient-to-br from-indigo-500 to-indigo-700 text-white shadow-xl shadow-indigo-500/30 rounded-2xl flex flex-col justify-center relative overflow-hidden group hover:-translate-y-1 transition-transform cursor-pointer">
            <div class="absolute -right-5 -bottom-5 text-indigo-400/30 group-hover:scale-110 transition-transform">
                <i class="bi bi-shield-check text-8xl"></i>
            </div>
            <p class="text-indigo-100 font-bold text-sm uppercase tracking-wider relative z-10">Siap Operasi</p>
            <h3 class="text-4xl font-black relative z-10">142 <span class="text-sm font-normal text-indigo-200">unit</span></h3>
        </div>
    </div>

    <!-- Data Table Container -->
    <div class="bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl overflow-hidden flex flex-col">
        <div class="px-6 py-4 border-b border-slate-100 bg-white/50 flex justify-between items-center">
            <div class="relative w-72">
                <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="text" placeholder="Cari aset, kode, atau pemilik..." class="w-full pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition-all text-sm">
            </div>
            <div class="text-sm text-slate-500 font-medium">Menampilkan 1-10 dari 250 aset</div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-4">Kode Aset</th>
                        <th class="px-6 py-4">Nama Aset</th>
                        <th class="px-6 py-4">Kategori</th>
                        <th class="px-6 py-4">Pemilik</th>
                        <th class="px-6 py-4">Kondisi</th>
                        <th class="px-6 py-4">Status Deployment</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    <!-- Row 1: Tersedia -->
                    <tr class="hover:bg-sky-50/50 transition-colors">
                        <td class="px-6 py-4 font-mono font-bold text-slate-800">AST-2026-001</td>
                        <td class="px-6 py-4 font-bold text-sky-700">Mobil Rescue Triton 4x4</td>
                        <td class="px-6 py-4">Kendaraan Operasional</td>
                        <td class="px-6 py-4">PWNU Jatim</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-bold uppercase bg-emerald-100 text-emerald-700 border border-emerald-200">
                                <i class="bi bi-check-circle-fill"></i> Baik
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-bold uppercase bg-slate-100 text-slate-600 border border-slate-200">
                                <i class="bi bi-house-door-fill"></i> Standby (Gudang)
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <a href="{{ url('inventaris/1') }}" class="px-3 py-1.5 text-xs font-bold border border-sky-200 text-sky-600 rounded-lg hover:bg-sky-50 transition-colors">Detail</a>
                        </td>
                    </tr>

                    <!-- Row 2: Dipinjam/Deployment -->
                    <tr class="hover:bg-sky-50/50 transition-colors">
                        <td class="px-6 py-4 font-mono font-bold text-slate-800">AST-2026-045</td>
                        <td class="px-6 py-4 font-bold text-sky-700">Tenda Peleton Standar BNPB</td>
                        <td class="px-6 py-4">Perlengkapan Posko</td>
                        <td class="px-6 py-4">PCNU Lumajang</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-bold uppercase bg-emerald-100 text-emerald-700 border border-emerald-200">
                                <i class="bi bi-check-circle-fill"></i> Baik
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-bold uppercase bg-indigo-100 text-indigo-700 border border-indigo-200">
                                <i class="bi bi-send-fill"></i> Deployed (Pronojiwo)
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <a href="{{ url('inventaris/2') }}" class="px-3 py-1.5 text-xs font-bold border border-sky-200 text-sky-600 rounded-lg hover:bg-sky-50 transition-colors">Detail</a>
                        </td>
                    </tr>

                    <!-- Row 3: Maintenance -->
                    <tr class="hover:bg-sky-50/50 transition-colors">
                        <td class="px-6 py-4 font-mono font-bold text-slate-800">AST-2025-112</td>
                        <td class="px-6 py-4 font-bold text-sky-700">Genset Honda 5000 Watt</td>
                        <td class="px-6 py-4">Elektronik & Mesin</td>
                        <td class="px-6 py-4">PWNU Jatim</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-bold uppercase bg-amber-100 text-amber-700 border border-amber-200">
                                <i class="bi bi-wrench-adjustable-circle-fill"></i> Rusak Ringan
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-bold uppercase bg-rose-100 text-rose-700 border border-rose-200">
                                <i class="bi bi-tools"></i> Maintenance
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <a href="{{ url('inventaris/3') }}" class="px-3 py-1.5 text-xs font-bold border border-sky-200 text-sky-600 rounded-lg hover:bg-sky-50 transition-colors">Detail</a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination Placeholder -->
        <div class="px-6 py-4 border-t border-slate-100 bg-white/50 flex justify-between items-center">
            <button class="px-4 py-2 text-sm font-semibold text-slate-500 hover:bg-slate-100 rounded-lg transition-colors border border-slate-200">Sebelumnya</button>
            <div class="flex gap-1">
                <button class="w-8 h-8 flex items-center justify-center rounded-lg bg-sky-500 text-white font-bold">1</button>
                <button class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-slate-100 text-slate-600 font-bold">2</button>
                <button class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-slate-100 text-slate-600 font-bold">3</button>
            </div>
            <button class="px-4 py-2 text-sm font-semibold text-slate-500 hover:bg-slate-100 rounded-lg transition-colors border border-slate-200">Selanjutnya</button>
        </div>
    </div>
</x-app-layout>
