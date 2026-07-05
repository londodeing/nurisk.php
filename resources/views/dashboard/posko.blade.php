<x-app-layout>
    <x-slot name="header">Posko Operational Board</x-slot>
    <x-slot name="actions">
        <!-- QUICK ACTIONS (Max 5) -->
        <div class="flex flex-wrap gap-2">
            <button onclick="alert('Modal: Buat Sitrep')" class="inline-flex items-center px-4 py-2 bg-rose-500/10 border border-rose-500/20 rounded-xl font-bold text-xs text-rose-600 uppercase tracking-widest hover:bg-rose-500/20 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <i class="bi bi-plus-lg mr-2"></i> Sitrep
            </button>
            <button onclick="alert('Modal: Buat Penugasan')" class="inline-flex items-center px-4 py-2 bg-indigo-500/10 border border-indigo-500/20 rounded-xl font-bold text-xs text-indigo-600 uppercase tracking-widest hover:bg-indigo-500/20 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <i class="bi bi-person-plus mr-2"></i> Tugas
            </button>
            <button onclick="alert('Modal: Aktivasi Posko')" class="inline-flex items-center px-4 py-2 bg-emerald-500/10 border border-emerald-500/20 rounded-xl font-bold text-xs text-emerald-600 uppercase tracking-widest hover:bg-emerald-500/20 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <i class="bi bi-house-add mr-2"></i> Posko
            </button>
            <button onclick="alert('Modal: Catat Kebutuhan')" class="inline-flex items-center px-4 py-2 bg-amber-500/10 border border-amber-500/20 rounded-xl font-bold text-xs text-amber-600 uppercase tracking-widest hover:bg-amber-500/20 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <i class="bi bi-cart-plus mr-2"></i> Kebutuhan
            </button>
            <button onclick="alert('Modal: Kirim Eskalasi')" class="inline-flex items-center px-4 py-2 bg-slate-800 border border-slate-700 rounded-xl font-bold text-xs text-white uppercase tracking-widest hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <i class="bi bi-megaphone mr-2"></i> Eskalasi
            </button>
        </div>
    </x-slot>

    <!-- Top Section: Alert Bar -->
    <div id="top-alerts" class="mb-6">
        @if(collect($initialData['decision_queue'])->where('priority', 'critical')->count() > 0)
            <div class="p-4 bg-rose-500/10 border-l-4 border-rose-500 rounded-r-xl flex items-start shadow-sm">
                <i class="bi bi-exclamation-octagon-fill text-rose-500 text-xl mr-3"></i>
                <div class="flex-1">
                    <strong class="text-rose-700">CRITICAL ALERT:</strong>
                    <span class="text-rose-600 ml-1">Terdapat peringatan kritis di antrean keputusan yang wajib segera diselesaikan!</span>
                </div>
            </div>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-6">
        <!-- Middle Section: Decision Queue -->
        <div class="lg:col-span-7">
            <div class="bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl h-full flex flex-col overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-white/50">
                    <span class="font-bold text-rose-600 flex items-center gap-2">
                        <i class="bi bi-lightning-charge-fill"></i> Decision Queue
                    </span>
                    <x-data-freshness />
                </div>
                <div class="p-0 flex-1" id="decision-queue-container">
                    <x-decision-queue :queue="$initialData['decision_queue']" />
                </div>
            </div>
        </div>

        <!-- Middle Section: Operational Metrics KPI -->
        <div class="lg:col-span-5">
            <div class="grid grid-cols-2 gap-4">
                <div id="kpi-posko">
                    <x-stat-card title="Posko Aktif" value="{{ $initialData['metrics']['posko_aktif'] }}" icon="bi-house-door" color="emerald" />
                </div>
                <div id="kpi-relawan">
                    <x-stat-card title="Relawan Aktif" value="{{ $initialData['metrics']['relawan_aktif'] }}" icon="bi-people" color="indigo" />
                </div>
                <div id="kpi-tugas">
                    <x-stat-card title="Tugas Terbuka" value="{{ $initialData['metrics']['tugas_terbuka'] }}" icon="bi-list-task" color="amber" />
                </div>
                <div id="kpi-kebutuhan">
                    <x-stat-card title="Kebutuhan Mendesak" value="{{ $initialData['metrics']['kebutuhan_mendesak'] }}" icon="bi-box-seam" color="rose" />
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Section: Operational Panels -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Activity Feed Panel -->
        <div class="bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl flex flex-col overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-white/50">
                <span class="font-bold text-slate-700 flex items-center gap-2">
                    <i class="bi bi-activity text-indigo-500"></i> Aktivitas Terbaru
                </span>
                <x-data-freshness />
            </div>
            <ul class="divide-y divide-slate-100 overflow-y-auto max-h-[400px]" id="activity-feed-list">
                @forelse($initialData['feed'] as $feed)
                    <li class="px-6 py-3 hover:bg-slate-50 transition-colors">
                        <small class="text-indigo-600 font-bold mr-2">{{ $feed['waktu'] }}</small> 
                        <span class="text-slate-600 text-sm">{{ $feed['pesan'] }}</span>
                    </li>
                @empty
                    <li class="px-6 py-4 text-slate-400 text-sm italic">Belum ada aktivitas.</li>
                @endforelse
            </ul>
        </div>

        <!-- Placeholder Panel Insiden/Penugasan -->
        <div class="bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl flex flex-col overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-white/50">
                <span class="font-bold text-slate-700 flex items-center gap-2">
                    <i class="bi bi-briefcase text-emerald-500"></i> Penugasan Hari Ini
                </span>
            </div>
            <div class="p-6 flex-1 flex flex-col items-center justify-center text-center">
                <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mb-4">
                    <i class="bi bi-person-workspace text-3xl text-slate-400"></i>
                </div>
                <p class="text-slate-500 text-sm">Klik tombol <span class="font-bold text-slate-700">Tugas</span> di pojok kanan atas untuk mulai membuat penugasan ke tim di lapangan.</p>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        $(document).ready(function() {
            let lastPolled = new Date();

            function updateFreshnessIndicators() {
                const now = new Date();
                const diffMinutes = Math.floor((now - lastPolled) / 60000);
                let badgeClass = 'bg-emerald-100 text-emerald-700 border-emerald-200';
                if (diffMinutes >= 15 && diffMinutes < 60) badgeClass = 'bg-amber-100 text-amber-700 border-amber-200';
                else if (diffMinutes >= 60) badgeClass = 'bg-rose-100 text-rose-700 border-rose-200';

                const indicator = `<span class="px-2 py-1 text-[10px] font-bold uppercase rounded-md border ${badgeClass}">Updated < 1m ago</span>`;
                $('.data-freshness-container').html(indicator);
            }

            setInterval(function() {
                $.ajax({
                    url: '/dashboard/posko/polling',
                    type: 'GET',
                    success: function(response) {
                        lastPolled = new Date();
                        // Update KPI
                        $('#kpi-posko h3').text(response.metrics.posko_aktif);
                        $('#kpi-relawan h3').text(response.metrics.relawan_aktif);
                        $('#kpi-tugas h3').text(response.metrics.tugas_terbuka);
                        $('#kpi-kebutuhan h3').text(response.metrics.kebutuhan_mendesak);

                        // Update Activity Feed
                        let feedHtml = '';
                        response.feed.forEach(f => {
                            feedHtml += `<li class="px-6 py-3 hover:bg-slate-50 transition-colors"><small class="text-indigo-600 font-bold mr-2">${f.waktu}</small> <span class="text-slate-600 text-sm">${f.pesan}</span></li>`;
                        });
                        $('#activity-feed-list').html(feedHtml || '<li class="px-6 py-4 text-slate-400 text-sm italic">Belum ada aktivitas.</li>');

                        // Update Decision Queue
                        let dqHtml = '';
                        response.decision_queue.forEach(item => {
                            let icon = 'bi-exclamation-circle text-slate-500';
                            let btnClass = 'text-slate-600 border-slate-200 hover:bg-slate-50';
                            
                            if (item.priority === 'critical') { 
                                icon = 'bi-exclamation-octagon-fill text-rose-500'; 
                                btnClass = 'text-rose-600 border-rose-200 hover:bg-rose-50'; 
                            } else if (item.priority === 'high') { 
                                icon = 'bi-exclamation-triangle-fill text-amber-500'; 
                                btnClass = 'text-amber-600 border-amber-200 hover:bg-amber-50'; 
                            }
                            
                            dqHtml += `<li class="px-6 py-4 flex justify-between items-center border-b border-slate-100 hover:bg-slate-50 transition-colors">
                                <div class="flex items-center gap-3"><i class="bi ${icon} text-lg"></i><span class="font-semibold text-slate-700 text-sm">${item.title}</span></div>
                                <a href="${item.action_url}" class="px-3 py-1 text-xs font-bold border rounded-lg transition-colors ${btnClass}">TINDAK</a>
                            </li>`;
                        });
                        $('#decision-queue-container').html(`<ul class="divide-y divide-slate-100">${dqHtml}</ul>`);

                        updateFreshnessIndicators();
                    }
                });
            }, 30000); // 30 seconds

            setInterval(updateFreshnessIndicators, 60000); // Check freshness every minute
            updateFreshnessIndicators(); // initial
        });
    </script>
    @endpush
</x-app-layout>
