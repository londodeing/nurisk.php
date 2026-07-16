<x-app-layout>
    <x-slot name="header">Dasbor Radar Skor Assessment</x-slot>

    <div class="mb-6 p-4 bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl flex flex-col md:flex-row justify-between items-center gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('insiden.show', $insiden->kode_kejadian) }}" class="p-2 bg-slate-100 text-slate-600 rounded-xl hover:bg-slate-200 transition-colors">
                <i class="bi bi-arrow-left text-xl"></i>
            </a>
            <div class="p-3 bg-indigo-100 text-indigo-600 rounded-xl">
                <i class="bi bi-radar text-2xl"></i>
            </div>
            <div>
                <h2 class="text-xl font-bold text-slate-800">Analitik Tingkat Keparahan</h2>
                <p class="text-sm text-slate-500">Insiden #{{ $insiden->kode_kejadian }} &bull; Assessment ID: {{ $assessment->id_assessment_utama }}</p>
            </div>
        </div>
    </div>

    <div x-data="scoringDashboard()" x-init="fetchData()" class="space-y-6">
        
        <!-- Loading State -->
        <div x-show="loading" class="flex flex-col justify-center items-center h-64 bg-white/50 backdrop-blur-md rounded-2xl border border-white/40 shadow-sm">
            <i class="bi bi-arrow-repeat text-4xl text-indigo-500 animate-spin mb-4"></i>
            <p class="text-slate-600 font-medium">Mengkalkulasi model skoring multi-domain...</p>
        </div>

        <div x-show="!loading && !error" class="grid grid-cols-1 lg:grid-cols-3 gap-6" style="display: none;">
            
            <!-- Left Column: Radar Chart -->
            <div class="lg:col-span-1 bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl p-6 flex flex-col items-center">
                <h3 class="text-lg font-bold text-slate-800 w-full mb-4 border-b border-slate-100 pb-2">Distribusi Dampak</h3>
                
                <div class="relative w-full max-w-[300px] aspect-square flex-grow flex items-center justify-center">
                    <canvas id="radarChart"></canvas>
                </div>
                
                <div class="w-full mt-6 bg-slate-50 rounded-xl p-4 border border-slate-100">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm font-semibold text-slate-600">Skor Agregat Total:</span>
                        <span class="text-xl font-black" :class="getColorClass(ringkasan?.skor_total)" x-text="formatScore(ringkasan?.skor_total)"></span>
                    </div>
                    <div class="w-full bg-slate-200 rounded-full h-2.5">
                        <div class="h-2.5 rounded-full" :class="getBgColorClass(ringkasan?.skor_total)" :style="`width: ${ringkasan?.skor_total}%`"></div>
                    </div>
                </div>

                <button @click="recalculate()" class="mt-4 w-full px-4 py-2 bg-indigo-50 text-indigo-600 hover:bg-indigo-100 rounded-lg font-semibold text-sm transition-colors flex items-center justify-center gap-2">
                    <i class="bi bi-arrow-clockwise"></i> Kalkulasi Ulang
                </button>
            </div>

            <!-- Right Column: Metrics & Recommendations -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Top Status Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl p-6">
                        <h4 class="text-sm font-semibold text-slate-500 mb-1 uppercase tracking-wider">Tingkat Keparahan</h4>
                        <div class="flex items-center gap-3">
                            <i class="bi bi-exclamation-triangle-fill text-3xl" :class="getKeparahanColor(ringkasan?.tingkat_keparahan)"></i>
                            <span class="text-2xl font-bold text-slate-800 capitalize" x-text="(ringkasan?.tingkat_keparahan || '').replace('_', ' ')"></span>
                        </div>
                    </div>
                    
                    <div class="bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl p-6">
                        <h4 class="text-sm font-semibold text-slate-500 mb-1 uppercase tracking-wider">Rekomendasi Respon</h4>
                        <div class="flex items-center gap-3">
                            <i class="bi bi-shield-fill-check text-3xl" :class="getRekomendasiColor(ringkasan?.rekomendasi_respon)"></i>
                            <span class="text-2xl font-bold text-slate-800 capitalize" x-text="(ringkasan?.rekomendasi_respon || '').replace('_', ' ')"></span>
                        </div>
                    </div>
                </div>

                <!-- Detailed Items Table -->
                <div class="bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl p-6 overflow-hidden">
                    <h3 class="text-lg font-bold text-slate-800 mb-4 border-b border-slate-100 pb-2 flex justify-between items-center">
                        <span>Rincian Indikator</span>
                        <span class="text-xs font-normal text-slate-500 bg-slate-100 px-2 py-1 rounded-md">Skala Indeks 1-5</span>
                    </h3>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider">
                                    <th class="p-3 rounded-tl-lg">Domain</th>
                                    <th class="p-3">Indikator</th>
                                    <th class="p-3 text-right">Nilai Terukur</th>
                                    <th class="p-3 text-center rounded-tr-lg">Indeks</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm text-slate-700 divide-y divide-slate-100">
                                <template x-for="item in items" :key="item.id_skor">
                                    <tr class="hover:bg-slate-50/50 transition-colors">
                                        <td class="p-3 capitalize font-medium text-slate-600" x-text="item.indikator?.domain"></td>
                                        <td class="p-3">
                                            <div class="font-semibold text-slate-800" x-text="item.indikator?.nama_indikator"></div>
                                            <div class="text-xs text-slate-400" x-text="item.indikator?.kode_indikator"></div>
                                        </td>
                                        <td class="p-3 text-right font-mono">
                                            <span x-text="formatNumber(item.nilai_terukur)"></span>
                                            <span class="text-xs text-slate-400" x-text="item.indikator?.satuan"></span>
                                        </td>
                                        <td class="p-3 text-center">
                                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full font-bold"
                                                  :class="getBadgeColor(item.skor_1_5)" x-text="item.skor_1_5"></span>
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="items.length === 0">
                                    <td colspan="4" class="p-6 text-center text-slate-500 italic">Belum ada data indikator yang dikalkulasi.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>

        <!-- Error State -->
        <div x-show="error" class="p-6 bg-rose-50 border border-rose-200 text-rose-600 rounded-2xl" style="display: none;">
            <div class="flex items-center gap-3 mb-2">
                <i class="bi bi-x-circle-fill text-2xl"></i>
                <h3 class="font-bold text-lg">Gagal Memuat Data</h3>
            </div>
            <p x-text="errorMessage"></p>
            <button @click="fetchData()" class="mt-4 px-4 py-2 bg-rose-100 hover:bg-rose-200 text-rose-700 rounded-lg transition-colors text-sm font-semibold">
                Coba Lagi
            </button>
        </div>

    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('scoringDashboard', () => ({
                loading: true,
                error: false,
                errorMessage: '',
                ringkasan: null,
                items: [],
                chartInstance: null,
                
                insidenId: '{{ $insiden->id_insiden }}',
                assessmentId: '{{ $assessment->id_assessment_utama }}',

                async fetchData() {
                    this.loading = true;
                    this.error = false;
                    try {
                        const response = await fetch(`/api/insiden/${this.insidenId}/assessment/${this.assessmentId}/ringkasan-skor`);
                        if(!response.ok) throw new Error('Network response was not ok');
                        const json = await response.json();
                        
                        if(json.success) {
                            this.ringkasan = json.data.ringkasan;
                            this.items = json.data.items;
                            this.$nextTick(() => {
                                this.initChart();
                            });
                        } else {
                            throw new Error(json.message || 'Unknown API error');
                        }
                    } catch (err) {
                        this.error = true;
                        this.errorMessage = err.message;
                    } finally {
                        this.loading = false;
                    }
                },

                async recalculate() {
                    this.loading = true;
                    try {
                        const response = await fetch(`/api/insiden/${this.insidenId}/assessment/${this.assessmentId}/hitung-skor`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        });
                        const json = await response.json();
                        if(json.success) {
                            await this.fetchData(); // reload
                        } else {
                            throw new Error(json.message);
                        }
                    } catch (err) {
                        alert("Gagal rekalkulasi: " + err.message);
                        this.loading = false;
                    }
                },

                initChart() {
                    const ctx = document.getElementById('radarChart');
                    if(!ctx) return;
                    
                    if(this.chartInstance) {
                        this.chartInstance.destroy();
                    }

                    const data = [
                        this.ringkasan?.skor_manusia || 0,
                        this.ringkasan?.skor_infrastruktur || 0,
                        this.ringkasan?.skor_lingkungan || 0,
                        this.ringkasan?.skor_ekonomi || 0,
                        this.ringkasan?.skor_sosial || 0,
                        this.ringkasan?.skor_kapasitas || 0
                    ];

                    this.chartInstance = new Chart(ctx, {
                        type: 'radar',
                        data: {
                            labels: ['Manusia', 'Infrastruktur', 'Lingkungan', 'Ekonomi', 'Sosial', 'Kapasitas'],
                            datasets: [{
                                label: 'Skor Dampak (0-100)',
                                data: data,
                                backgroundColor: 'rgba(99, 102, 241, 0.2)',
                                borderColor: 'rgba(99, 102, 241, 1)',
                                pointBackgroundColor: 'rgba(99, 102, 241, 1)',
                                pointBorderColor: '#fff',
                                pointHoverBackgroundColor: '#fff',
                                pointHoverBorderColor: 'rgba(99, 102, 241, 1)',
                                borderWidth: 2,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                r: {
                                    min: 0,
                                    max: 100,
                                    ticks: {
                                        stepSize: 20,
                                        display: false
                                    },
                                    grid: {
                                        color: 'rgba(0,0,0,0.05)'
                                    },
                                    angleLines: {
                                        color: 'rgba(0,0,0,0.05)'
                                    },
                                    pointLabels: {
                                        font: {
                                            family: "'Inter', sans-serif",
                                            size: 11,
                                            weight: '600'
                                        },
                                        color: '#64748b'
                                    }
                                }
                            },
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    backgroundColor: 'rgba(15, 23, 42, 0.9)',
                                    padding: 12,
                                    cornerRadius: 8,
                                    titleFont: { size: 13, family: "'Inter', sans-serif" },
                                    bodyFont: { size: 14, family: "'Inter', sans-serif", weight: 'bold' },
                                    displayColors: false
                                }
                            }
                        }
                    });
                },

                formatNumber(val) {
                    if(!val) return '0';
                    return new Intl.NumberFormat('id-ID').format(val);
                },

                formatScore(val) {
                    if(!val) return '0.00';
                    return parseFloat(val).toFixed(2);
                },

                getColorClass(score) {
                    if(score >= 80) return 'text-rose-600';
                    if(score >= 60) return 'text-orange-500';
                    if(score >= 40) return 'text-amber-500';
                    if(score >= 20) return 'text-yellow-500';
                    return 'text-emerald-500';
                },

                getBgColorClass(score) {
                    if(score >= 80) return 'bg-rose-500';
                    if(score >= 60) return 'bg-orange-500';
                    if(score >= 40) return 'bg-amber-500';
                    if(score >= 20) return 'bg-yellow-500';
                    return 'bg-emerald-500';
                },

                getBadgeColor(skor) {
                    switch(skor) {
                        case 5: return 'bg-rose-100 text-rose-700';
                        case 4: return 'bg-orange-100 text-orange-700';
                        case 3: return 'bg-amber-100 text-amber-700';
                        case 2: return 'bg-yellow-100 text-yellow-700';
                        default: return 'bg-emerald-100 text-emerald-700';
                    }
                },

                getKeparahanColor(tingkat) {
                    switch(tingkat) {
                        case 'katastrofik': return 'text-rose-600';
                        case 'berat': return 'text-orange-500';
                        case 'signifikan': return 'text-amber-500';
                        case 'sedang': return 'text-yellow-500';
                        default: return 'text-emerald-500';
                    }
                },

                getRekomendasiColor(rek) {
                    switch(rek) {
                        case 'eskalasi_nasional': return 'text-rose-600';
                        case 'mobilisasi_besar': return 'text-orange-500';
                        case 'tanggap_cepat': return 'text-amber-500';
                        case 'siaga': return 'text-yellow-500';
                        default: return 'text-emerald-500';
                    }
                }
            }));
        });
    </script>
    @endpush
</x-app-layout>
