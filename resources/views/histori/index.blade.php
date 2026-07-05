<x-app-layout>
    <x-slot name="header">Peta Risiko & Trend Nasional</x-slot>

    <!-- Stat Row -->
    <div class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl p-6 relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-rose-500/10 rounded-full blur-xl group-hover:bg-rose-500/20 transition-colors"></div>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Total Kejadian Historis</p>
            <h3 class="text-3xl font-black text-slate-800">1,248</h3>
            <p class="text-xs font-bold text-emerald-500 mt-2"><i class="bi bi-arrow-up-right"></i> +5.2% dari tahun lalu</p>
        </div>
        
        <div class="bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl p-6 relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-amber-500/10 rounded-full blur-xl group-hover:bg-amber-500/20 transition-colors"></div>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Bencana Dominan</p>
            <h3 class="text-2xl font-black text-slate-800">Banjir Bandang</h3>
            <p class="text-xs font-bold text-slate-500 mt-2">Menyumbang 45% kejadian</p>
        </div>

        <div class="bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl p-6 relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-sky-500/10 rounded-full blur-xl group-hover:bg-sky-500/20 transition-colors"></div>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Kab. Paling Rawan</p>
            <h3 class="text-2xl font-black text-slate-800">Kab. Lumajang</h3>
            <p class="text-xs font-bold text-rose-500 mt-2"><i class="bi bi-exclamation-triangle"></i> Indeks Risiko: 0.89</p>
        </div>

        <div class="bg-gradient-to-br from-slate-800 to-slate-900 shadow-xl rounded-2xl p-6 flex flex-col justify-center text-white relative overflow-hidden">
            <div class="absolute right-0 bottom-0 opacity-10">
                <i class="bi bi-calendar3 text-8xl"></i>
            </div>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1 relative z-10">Prediksi 30 Hari</p>
            <h3 class="text-xl font-black text-white relative z-10">Waspada Angin Puting Beliung</h3>
            <button class="mt-3 px-3 py-1.5 bg-white/20 hover:bg-white/30 text-white text-xs font-bold rounded-lg border border-white/20 transition-all self-start relative z-10">
                Lihat Kalender Musiman
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Map Area -->
        <div class="lg:col-span-2 bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl p-6 flex flex-col min-h-[500px]">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                    <i class="bi bi-map-fill text-sky-500"></i> Peta Risiko Bencana Geospasial
                </h3>
                <div class="flex gap-2">
                    <select class="text-sm border-slate-200 rounded-lg text-slate-600 focus:ring-sky-500 focus:border-sky-500 font-medium">
                        <option>Semua Bencana</option>
                        <option>Banjir</option>
                        <option>Tanah Longsor</option>
                        <option>Erupsi Gunung Berapi</option>
                    </select>
                </div>
            </div>
            
            <div class="flex-1 bg-slate-100 rounded-xl border border-slate-200 relative overflow-hidden flex items-center justify-center">
                <!-- Map placeholder -->
                <div class="absolute inset-0 bg-[url('https://upload.wikimedia.org/wikipedia/commons/4/4e/East_Java_map.svg')] bg-center bg-contain bg-no-repeat opacity-30 mix-blend-multiply"></div>
                <p class="text-slate-500 font-bold z-10"><i class="bi bi-geo-alt"></i> Leaflet Map Instance Placeholder</p>
                
                <!-- Heatmap dots -->
                <div class="absolute top-[60%] left-[40%] w-8 h-8 bg-rose-500/50 rounded-full blur-sm animate-pulse"></div>
                <div class="absolute top-[45%] left-[70%] w-12 h-12 bg-amber-500/50 rounded-full blur-sm animate-pulse"></div>
                <div class="absolute top-[30%] left-[30%] w-6 h-6 bg-rose-500/50 rounded-full blur-sm animate-pulse"></div>
            </div>
            
            <div class="flex items-center gap-4 mt-4 text-xs font-bold text-slate-500 justify-center">
                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-rose-500"></span> Tinggi</span>
                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-amber-500"></span> Sedang</span>
                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-emerald-500"></span> Rendah</span>
            </div>
        </div>

        <!-- Right Panel -->
        <div class="space-y-6">
            <div class="bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl p-6">
                <h3 class="text-lg font-bold text-slate-800 mb-4 border-b border-slate-100 pb-2">Trend 5 Tahun Terakhir</h3>
                <div class="h-48 w-full relative">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>
            
            <div class="bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl p-6">
                <h3 class="text-lg font-bold text-slate-800 mb-4 border-b border-slate-100 pb-2">Top 3 Wilayah Rawan</h3>
                <ul class="space-y-4">
                    <li class="flex items-center justify-between">
                        <div>
                            <p class="font-bold text-slate-800 text-sm">1. Lumajang</p>
                            <p class="text-xs text-slate-500">Didominasi Erupsi & Banjir Lahar</p>
                        </div>
                        <span class="px-2 py-1 bg-rose-100 text-rose-700 text-xs font-bold rounded-lg">89 Pts</span>
                    </li>
                    <li class="flex items-center justify-between">
                        <div>
                            <p class="font-bold text-slate-800 text-sm">2. Pacitan</p>
                            <p class="text-xs text-slate-500">Banjir Bandang & Longsor</p>
                        </div>
                        <span class="px-2 py-1 bg-rose-100 text-rose-700 text-xs font-bold rounded-lg">82 Pts</span>
                    </li>
                    <li class="flex items-center justify-between">
                        <div>
                            <p class="font-bold text-slate-800 text-sm">3. Bojonegoro</p>
                            <p class="text-xs text-slate-500">Banjir Luapan Bengawan Solo</p>
                        </div>
                        <span class="px-2 py-1 bg-amber-100 text-amber-700 text-xs font-bold rounded-lg">75 Pts</span>
                    </li>
                </ul>
                <button class="w-full mt-6 px-4 py-2 border border-slate-200 text-slate-600 font-bold text-sm rounded-xl hover:bg-slate-50 transition-colors">
                    Lihat Semua Wilayah
                </button>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('trendChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['2022', '2023', '2024', '2025', '2026'],
                    datasets: [{
                        label: 'Jumlah Kejadian',
                        data: [150, 180, 165, 210, 245],
                        borderColor: '#0ea5e9',
                        backgroundColor: 'rgba(14, 165, 233, 0.1)',
                        borderWidth: 3,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#0ea5e9',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, grid: { borderDash: [4, 4], color: '#f1f5f9' } },
                        x: { grid: { display: false } }
                    }
                }
            });
        });
    </script>
    @endpush
</x-app-layout>
