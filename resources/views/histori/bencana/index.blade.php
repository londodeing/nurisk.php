<x-app-layout>
    <x-slot name="header">Daftar Kejadian Bencana</x-slot>

    <!-- Header Panel -->
    <div class="mb-6 p-6 bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl flex flex-col md:flex-row justify-between items-center gap-4">
        <div>
            <h2 class="text-2xl font-black text-slate-800 flex items-center gap-3">
                <i class="bi bi-journal-text text-indigo-500"></i> Data Histori Tabular
            </h2>
            <p class="text-sm text-slate-500 mt-1">Basis data rekaman kejadian bencana yang telah diverifikasi.</p>
        </div>
        <div class="flex items-center gap-3">
            <button class="px-4 py-2 bg-white border border-slate-200 text-slate-600 font-bold rounded-xl shadow-sm hover:bg-slate-50 transition-all flex items-center gap-2">
                <i class="bi bi-download"></i> Export CSV
            </button>
            <a href="{{ url('histori/bencana/create') }}" class="px-4 py-2 bg-indigo-600 text-white font-bold rounded-xl shadow-md shadow-indigo-500/30 hover:bg-indigo-700 transition-all flex items-center gap-2">
                <i class="bi bi-plus-lg"></i> Tambah Data Manual
            </a>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="mb-6 p-4 bg-white/50 backdrop-blur-md border border-white/40 shadow-sm rounded-xl flex gap-4 overflow-x-auto">
        <select class="rounded-lg border-slate-200 text-sm text-slate-600 focus:ring-indigo-500">
            <option>Semua Wilayah</option>
            <option>Kab. Lumajang</option>
            <option>Kab. Pacitan</option>
        </select>
        <select class="rounded-lg border-slate-200 text-sm text-slate-600 focus:ring-indigo-500">
            <option>Semua Bencana</option>
            <option>Banjir</option>
            <option>Erupsi</option>
        </select>
        <select class="rounded-lg border-slate-200 text-sm text-slate-600 focus:ring-indigo-500">
            <option>Tahun 2026</option>
            <option>Tahun 2025</option>
            <option>Semua Tahun</option>
        </select>
        <div class="relative flex-1 min-w-[200px]">
            <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input type="text" placeholder="Cari nama kejadian..." class="w-full pl-9 pr-3 py-2 rounded-lg border-slate-200 text-sm text-slate-600 focus:ring-indigo-500">
        </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-4">Tgl Kejadian</th>
                        <th class="px-6 py-4">Nama Kejadian</th>
                        <th class="px-6 py-4">Jenis</th>
                        <th class="px-6 py-4">Wilayah</th>
                        <th class="px-6 py-4">Kerugian (Juta)</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    <tr class="hover:bg-indigo-50/50 transition-colors">
                        <td class="px-6 py-4 text-slate-500">12 Ags 2026</td>
                        <td class="px-6 py-4 font-bold text-slate-800">Erupsi Gunung Semeru</td>
                        <td class="px-6 py-4">Erupsi Gunung Berapi</td>
                        <td class="px-6 py-4">Kab. Lumajang</td>
                        <td class="px-6 py-4 font-mono font-medium">Rp 4.500</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-emerald-100 text-emerald-700 text-xs font-bold rounded border border-emerald-200">
                                <i class="bi bi-patch-check-fill"></i> Terverifikasi
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <button class="px-3 py-1.5 text-xs font-bold text-indigo-600 bg-indigo-50 border border-indigo-200 rounded hover:bg-indigo-100">Detail</button>
                        </td>
                    </tr>
                    <tr class="hover:bg-indigo-50/50 transition-colors">
                        <td class="px-6 py-4 text-slate-500">03 Feb 2026</td>
                        <td class="px-6 py-4 font-bold text-slate-800">Banjir Bandang Demak</td>
                        <td class="px-6 py-4">Banjir Bandang</td>
                        <td class="px-6 py-4">Kab. Demak</td>
                        <td class="px-6 py-4 font-mono font-medium">Rp 12.000</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-emerald-100 text-emerald-700 text-xs font-bold rounded border border-emerald-200">
                                <i class="bi bi-patch-check-fill"></i> Terverifikasi
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <button class="px-3 py-1.5 text-xs font-bold text-indigo-600 bg-indigo-50 border border-indigo-200 rounded hover:bg-indigo-100">Detail</button>
                        </td>
                    </tr>
                    <tr class="hover:bg-indigo-50/50 transition-colors">
                        <td class="px-6 py-4 text-slate-500">20 Jan 2026</td>
                        <td class="px-6 py-4 font-bold text-slate-800">Tanah Longsor Pacitan</td>
                        <td class="px-6 py-4">Tanah Longsor</td>
                        <td class="px-6 py-4">Kab. Pacitan</td>
                        <td class="px-6 py-4 font-mono font-medium">Rp 800</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-amber-100 text-amber-700 text-xs font-bold rounded border border-amber-200">
                                <i class="bi bi-hourglass-split"></i> Review
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <button class="px-3 py-1.5 text-xs font-bold text-amber-600 bg-amber-50 border border-amber-200 rounded hover:bg-amber-100">Verifikasi</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-slate-100 bg-white/50 flex justify-between items-center text-sm">
            <span class="text-slate-500">Menampilkan 1-10 dari 1.248 data</span>
            <div class="flex gap-2">
                <button class="px-3 py-1 rounded border border-slate-200 text-slate-600 hover:bg-slate-50">Prev</button>
                <button class="px-3 py-1 rounded bg-indigo-600 text-white font-bold">1</button>
                <button class="px-3 py-1 rounded border border-slate-200 text-slate-600 hover:bg-slate-50">2</button>
                <button class="px-3 py-1 rounded border border-slate-200 text-slate-600 hover:bg-slate-50">3</button>
                <button class="px-3 py-1 rounded border border-slate-200 text-slate-600 hover:bg-slate-50">Next</button>
            </div>
        </div>
    </div>
</x-app-layout>
