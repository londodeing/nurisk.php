<x-app-layout>
    <x-slot name="header">Analisis Wilayah: Kab. Lumajang</x-slot>

    <!-- Header Panel -->
    <div class="mb-6 p-6 bg-gradient-to-r from-slate-800 to-indigo-900 text-white shadow-xl rounded-2xl flex flex-col md:flex-row justify-between items-center gap-6 relative overflow-hidden">
        <!-- Decor -->
        <div class="absolute right-0 top-0 w-64 h-64 bg-white/5 rounded-full blur-3xl pointer-events-none"></div>

        <div class="flex items-center gap-4 relative z-10">
            <div class="w-16 h-16 bg-white/10 text-white rounded-2xl flex items-center justify-center border border-white/20 backdrop-blur-md">
                <i class="bi bi-bar-chart-line text-3xl"></i>
            </div>
            <div>
                <h2 class="text-2xl font-black tracking-tight text-white mb-1">Kabupaten Lumajang</h2>
                <p class="text-indigo-200 font-medium text-sm">Provinsi Jawa Timur &bull; ID: 3508</p>
            </div>
        </div>

        <div class="bg-white/10 backdrop-blur-md px-6 py-4 rounded-xl border border-white/20 text-center relative z-10">
            <p class="text-xs font-bold text-indigo-200 uppercase tracking-wider mb-1">Indeks Risiko Keseluruhan</p>
            <p class="text-3xl font-black text-white">0.89 <span class="text-rose-400 text-lg uppercase tracking-widest font-bold">(Tinggi)</span></p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Kalender Musiman -->
        <div class="bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl p-6 flex flex-col">
            <h3 class="text-lg font-bold text-slate-800 mb-2 border-b border-slate-100 pb-2 flex items-center gap-2">
                <i class="bi bi-calendar-range text-indigo-500"></i> Kalender Musiman Bencana
            </h3>
            <p class="text-sm text-slate-500 mb-6">Distribusi historis kejadian bencana per bulan berdasarkan data 10 tahun terakhir.</p>
            
            <div class="flex-1 min-h-[300px] relative w-full">
                <canvas id="kalenderChart"></canvas>
            </div>
        </div>

        <!-- Probabilitas 30/90 Hari -->
        <div class="bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl p-6">
            <h3 class="text-lg font-bold text-slate-800 mb-2 border-b border-slate-100 pb-2 flex items-center gap-2">
                <i class="bi bi-dice-5 text-emerald-500"></i> Probabilitas Mendatang (Forecast)
            </h3>
            <p class="text-sm text-slate-500 mb-6">Prediksi persentase kemungkinan terjadinya insiden dalam waktu dekat berdasarkan tren musiman.</p>
            
            <div class="space-y-4">
                <!-- Erupsi -->
                <div class="p-4 bg-slate-50 rounded-xl border border-slate-100 relative overflow-hidden">
                    <!-- Progress Background -->
                    <div class="absolute top-0 left-0 bottom-0 bg-rose-100/50 w-[85%] z-0"></div>
                    <div class="relative z-10 flex justify-between items-center mb-2">
                        <div class="font-bold text-slate-800 flex items-center gap-2">
                            <i class="bi bi-cone-striped text-rose-500"></i> Erupsi Gunung Berapi
                        </div>
                        <div class="text-right">
                            <span class="text-2xl font-black text-rose-600">85%</span>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">30 Hari Ke Depan</p>
                        </div>
                    </div>
                    <div class="relative z-10 w-full bg-white rounded-full h-2 border border-slate-200">
                        <div class="bg-rose-500 h-1.5 rounded-full" style="width: 85%"></div>
                    </div>
                </div>

                <!-- Banjir Lahar -->
                <div class="p-4 bg-slate-50 rounded-xl border border-slate-100 relative overflow-hidden">
                    <div class="absolute top-0 left-0 bottom-0 bg-amber-100/50 w-[60%] z-0"></div>
                    <div class="relative z-10 flex justify-between items-center mb-2">
                        <div class="font-bold text-slate-800 flex items-center gap-2">
                            <i class="bi bi-water text-amber-500"></i> Banjir Lahar Dingin
                        </div>
                        <div class="text-right">
                            <span class="text-2xl font-black text-amber-600">60%</span>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">30 Hari Ke Depan</p>
                        </div>
                    </div>
                    <div class="relative z-10 w-full bg-white rounded-full h-2 border border-slate-200">
                        <div class="bg-amber-500 h-1.5 rounded-full" style="width: 60%"></div>
                    </div>
                </div>

                <!-- Longsor -->
                <div class="p-4 bg-slate-50 rounded-xl border border-slate-100 relative overflow-hidden">
                    <div class="absolute top-0 left-0 bottom-0 bg-sky-100/50 w-[25%] z-0"></div>
                    <div class="relative z-10 flex justify-between items-center mb-2">
                        <div class="font-bold text-slate-800 flex items-center gap-2">
                            <i class="bi bi-tree text-sky-500"></i> Tanah Longsor
                        </div>
                        <div class="text-right">
                            <span class="text-2xl font-black text-sky-600">25%</span>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">30 Hari Ke Depan</p>
                        </div>
                    </div>
                    <div class="relative z-10 w-full bg-white rounded-full h-2 border border-slate-200">
                        <div class="bg-sky-500 h-1.5 rounded-full" style="width: 25%"></div>
                    </div>
                </div>
            </div>
            
            <div class="mt-6 p-4 bg-indigo-50 border border-indigo-100 rounded-xl flex items-start gap-3">
                <i class="bi bi-info-circle-fill text-indigo-500 mt-0.5"></i>
                <p class="text-sm text-indigo-800">
                    Berdasarkan histori musiman, bulan depan (September) adalah puncak kejadian historis untuk Erupsi. Disarankan untuk memvalidasi kesiapan logistik di posko wilayah.
                </p>
            </div>
        </div>

    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('kalenderChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'],
                    datasets: [
                        {
                            label: 'Erupsi',
                            data: [2, 1, 0, 0, 1, 3, 5, 8, 12, 10, 4, 3],
                            backgroundColor: '#f43f5e', // rose-500
                            borderRadius: 4
                        },
                        {
                            label: 'Banjir',
                            data: [15, 12, 8, 4, 2, 1, 0, 0, 1, 5, 10, 14],
                            backgroundColor: '#0ea5e9', // sky-500
                            borderRadius: 4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: { stacked: true, grid: { display: false } },
                        y: { stacked: true, grid: { borderDash: [4, 4], color: '#f1f5f9' } }
                    },
                    plugins: {
                        legend: { position: 'top', align: 'end', labels: { usePointStyle: true, boxWidth: 8 } }
                    }
                }
            });
        });
    </script>
    @endpush
</x-app-layout>
