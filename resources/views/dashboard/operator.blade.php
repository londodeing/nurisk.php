<x-app-layout>
    <x-slot name="header">Posko Data Entry Center</x-slot>

    <!-- Row 1: Operator Context & Freshness -->
    <div class="mb-6 p-4 bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl flex flex-col md:flex-row justify-between items-center gap-4">
        <div class="flex flex-wrap items-center gap-4">
            <span class="font-bold text-lg text-slate-800 flex items-center gap-2">
                <i class="bi bi-person-badge text-indigo-500"></i> 
                <span id="ctx-operator">{{ $initialData['shift']['operator_name'] }}</span>
            </span>
            <span class="text-slate-500 text-sm flex items-center gap-2">
                <i class="bi bi-house text-emerald-500"></i> 
                <span id="ctx-posko">{{ $initialData['shift']['posko_name'] }}</span>
            </span>
            <span class="px-3 py-1 bg-indigo-100 text-indigo-700 text-xs font-bold rounded-full border border-indigo-200">
                Shift: <span id="ctx-shift">{{ $initialData['shift']['shift'] }}</span>
            </span>
            <span class="px-3 py-1 bg-slate-800 text-slate-100 text-xs font-bold rounded-full flex items-center gap-2">
                <i class="bi bi-clock"></i> 
                <span id="ctx-time">{{ $initialData['shift']['server_time'] }}</span>
            </span>
        </div>
        <div class="flex items-center gap-4">
            <span class="px-3 py-1 bg-emerald-100 text-emerald-700 text-xs font-bold rounded-full border border-emerald-200 flex items-center gap-2" id="ctx-sync">
                <i class="bi bi-cloud-check"></i> {{ $initialData['shift']['sync_status'] }}
            </span>
            <x-data-freshness />
        </div>
    </div>

    <!-- Row 2: Quick Entry Center -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
        <button class="p-6 bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl flex flex-col items-center justify-center gap-3 hover:-translate-y-1 hover:shadow-indigo-500/20 hover:border-indigo-200 group transition-all" onclick="openModalDebounced('Modal: Assessment Baru')">
            <i class="bi bi-clipboard-data text-4xl text-indigo-500 group-hover:scale-110 transition-transform"></i>
            <span class="font-bold text-sm text-slate-700 text-center leading-tight">Assessment<br>Baru</span>
        </button>
        <button class="p-6 bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl flex flex-col items-center justify-center gap-3 hover:-translate-y-1 hover:shadow-emerald-500/20 hover:border-emerald-200 group transition-all" onclick="openModalDebounced('Modal: Sitrep Baru')">
            <i class="bi bi-file-earmark-text text-4xl text-emerald-500 group-hover:scale-110 transition-transform"></i>
            <span class="font-bold text-sm text-slate-700 text-center leading-tight">Sitrep<br>Baru</span>
        </button>
        <button class="p-6 bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl flex flex-col items-center justify-center gap-3 hover:-translate-y-1 hover:shadow-sky-500/20 hover:border-sky-200 group transition-all" onclick="openModalDebounced('Modal: Penugasan Baru')">
            <i class="bi bi-person-lines-fill text-4xl text-sky-500 group-hover:scale-110 transition-transform"></i>
            <span class="font-bold text-sm text-slate-700 text-center leading-tight">Penugasan<br>Baru</span>
        </button>
        <button class="p-6 bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl flex flex-col items-center justify-center gap-3 hover:-translate-y-1 hover:shadow-amber-500/20 hover:border-amber-200 group transition-all" onclick="openModalDebounced('Modal: Relawan Baru')">
            <i class="bi bi-person-plus-fill text-4xl text-amber-500 group-hover:scale-110 transition-transform"></i>
            <span class="font-bold text-sm text-slate-700 text-center leading-tight">Relawan<br>Baru</span>
        </button>
        <button class="p-6 bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl flex flex-col items-center justify-center gap-3 hover:-translate-y-1 hover:shadow-slate-500/20 hover:border-slate-300 group transition-all" onclick="openModalDebounced('Modal: Logistik Masuk')">
            <i class="bi bi-box-arrow-in-down text-4xl text-slate-500 group-hover:scale-110 transition-transform"></i>
            <span class="font-bold text-sm text-slate-700 text-center leading-tight">Logistik<br>Masuk</span>
        </button>
        <button class="p-6 bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl flex flex-col items-center justify-center gap-3 hover:-translate-y-1 hover:shadow-rose-500/20 hover:border-rose-200 group transition-all" onclick="openModalDebounced('Modal: Logistik Keluar')">
            <i class="bi bi-box-arrow-up text-4xl text-rose-500 group-hover:scale-110 transition-transform"></i>
            <span class="font-bold text-sm text-slate-700 text-center leading-tight">Logistik<br>Keluar</span>
        </button>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
        <!-- Row 3 & 4: Left Column -->
        <div class="xl:col-span-8 space-y-6">
            <!-- Row 3: Pending Work Queue -->
            <div class="bg-white/80 backdrop-blur-xl border-l-4 border-indigo-500 shadow-xl rounded-2xl overflow-hidden flex flex-col">
                <div class="px-6 py-4 border-b border-slate-100 bg-white/50 flex justify-between items-center">
                    <span class="font-bold text-indigo-700 flex items-center gap-2">
                        <i class="bi bi-hourglass-split"></i> Pending Work Queue
                    </span>
                </div>
                <div class="overflow-x-auto" id="pending-queue-container">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-100">
                            <tr>
                                <th class="px-6 py-3">Jenis</th>
                                <th class="px-6 py-3">Nomor</th>
                                <th class="px-6 py-3">Dibuat Oleh</th>
                                <th class="px-6 py-3">Umur</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @foreach($initialData['pending_queue'] as $pending)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-3">{{ $pending['type'] }}</td>
                                    <td class="px-6 py-3 font-bold">{{ $pending['number'] }}</td>
                                    <td class="px-6 py-3">{{ $pending['author'] }}</td>
                                    <td class="px-6 py-3">
                                        <span class="px-2 py-1 text-[10px] font-bold uppercase rounded-md border border-{{ $pending['badge'] }}-200 bg-{{ $pending['badge'] }}-100 text-{{ $pending['badge'] }}-700">
                                            {{ $pending['age_hours'] }} Jam
                                        </span>
                                    </td>
                                    <td class="px-6 py-3">{{ $pending['status'] }}</td>
                                    <td class="px-6 py-3">
                                        <button class="px-3 py-1 text-xs font-bold bg-indigo-500 text-white rounded-lg hover:bg-indigo-600 shadow-md shadow-indigo-500/20 transition-all">Lanjutkan</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Row 4: Data Quality Queue -->
            <div class="bg-white/80 backdrop-blur-xl border-l-4 border-rose-500 shadow-xl rounded-2xl overflow-hidden flex flex-col">
                <div class="px-6 py-4 border-b border-slate-100 bg-white/50 flex justify-between items-center">
                    <span class="font-bold text-rose-700 flex items-center gap-2">
                        <i class="bi bi-shield-exclamation"></i> Data Quality Queue
                    </span>
                </div>
                <div class="overflow-x-auto" id="quality-queue-container">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-100">
                            <tr>
                                <th class="px-6 py-3">Jenis Data</th>
                                <th class="px-6 py-3">Masalah</th>
                                <th class="px-6 py-3">Severity</th>
                                <th class="px-6 py-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @foreach($initialData['quality_queue'] as $quality)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-3 font-bold">{{ $quality['type'] }}</td>
                                    <td class="px-6 py-3">{{ $quality['issue'] }}</td>
                                    <td class="px-6 py-3">
                                        <span class="px-2 py-1 text-[10px] font-bold uppercase rounded-md border border-{{ $quality['badge'] }}-200 bg-{{ $quality['badge'] }}-100 text-{{ $quality['badge'] }}-700">
                                            {{ $quality['severity'] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3">
                                        <button class="px-3 py-1 text-xs font-bold border border-rose-200 text-rose-600 rounded-lg hover:bg-rose-50 transition-colors">Perbaiki</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Row 5: Submission Queue -->
            <div class="bg-white/80 backdrop-blur-xl border-l-4 border-emerald-500 shadow-xl rounded-2xl overflow-hidden flex flex-col">
                <div class="px-6 py-4 border-b border-slate-100 bg-white/50 flex justify-between items-center">
                    <span class="font-bold text-emerald-700 flex items-center gap-2">
                        <i class="bi bi-send-check"></i> Submission Queue
                    </span>
                </div>
                <div class="overflow-x-auto" id="submission-queue-container">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-100">
                            <tr>
                                <th class="px-6 py-3">Nomor</th>
                                <th class="px-6 py-3">Jenis</th>
                                <th class="px-6 py-3">Dibuat</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @foreach($initialData['submission_queue'] as $sub)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-3 font-bold">{{ $sub['number'] }}</td>
                                    <td class="px-6 py-3">{{ $sub['type'] }}</td>
                                    <td class="px-6 py-3">{{ $sub['created'] }}</td>
                                    <td class="px-6 py-3">{{ $sub['status'] }}</td>
                                    <td class="px-6 py-3 flex gap-2">
                                        <button class="px-3 py-1 text-xs font-bold border border-sky-200 text-sky-600 rounded-lg hover:bg-sky-50 transition-colors">Review</button>
                                        <button class="px-3 py-1 text-xs font-bold bg-emerald-500 text-white rounded-lg hover:bg-emerald-600 shadow-md shadow-emerald-500/20 transition-all">Submit</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Row 6: Right Column (Activity Feed) -->
        <div class="xl:col-span-4">
            <div class="bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl h-full flex flex-col overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-white/50 flex justify-between items-center">
                    <span class="font-bold text-slate-700 flex items-center gap-2">
                        <i class="bi bi-activity text-indigo-500"></i> Activity Feed
                    </span>
                </div>
                <div class="p-0 overflow-y-auto max-h-[800px]" id="activity-feed-container">
                    <ul class="divide-y divide-slate-100">
                        @foreach($initialData['activity_feed'] as $act)
                            <li class="px-6 py-4 flex justify-between items-center hover:bg-slate-50 transition-colors gap-4">
                                <span class="text-sm text-slate-700">{{ $act['text'] }}</span>
                                <span class="text-xs font-semibold text-slate-400 whitespace-nowrap">{{ $act['time'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
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
                    url: '/dashboard/operator/polling',
                    type: 'GET',
                    success: function(response) {
                        lastPolled = new Date();
                        
                        $('#ctx-operator').text(response.shift.operator_name);
                        $('#ctx-posko').text(response.shift.posko_name);
                        $('#ctx-shift').text(response.shift.shift);
                        $('#ctx-time').text(response.shift.server_time);

                        // Pending Queue
                        let pqHtml = '';
                        response.pending_queue.forEach(p => {
                            let badge = p.badge === 'danger' ? 'rose' : (p.badge === 'warning' ? 'amber' : 'indigo');
                            pqHtml += `<tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-3">${p.type}</td><td class="px-6 py-3 font-bold">${p.number}</td>
                                <td class="px-6 py-3">${p.author}</td>
                                <td class="px-6 py-3"><span class="px-2 py-1 text-[10px] font-bold uppercase rounded-md border border-${badge}-200 bg-${badge}-100 text-${badge}-700">${p.age_hours} Jam</span></td>
                                <td class="px-6 py-3">${p.status}</td>
                                <td class="px-6 py-3"><button class="px-3 py-1 text-xs font-bold bg-indigo-500 text-white rounded-lg hover:bg-indigo-600 shadow-md shadow-indigo-500/20 transition-all">Lanjutkan</button></td>
                            </tr>`;
                        });
                        $('#pending-queue-container tbody').html(pqHtml);

                        // Quality Queue
                        let qqHtml = '';
                        response.quality_queue.forEach(q => {
                            let badge = q.badge === 'danger' ? 'rose' : (q.badge === 'warning' ? 'amber' : 'emerald');
                            qqHtml += `<tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-3 font-bold">${q.type}</td><td class="px-6 py-3">${q.issue}</td>
                                <td class="px-6 py-3"><span class="px-2 py-1 text-[10px] font-bold uppercase rounded-md border border-${badge}-200 bg-${badge}-100 text-${badge}-700">${q.severity}</span></td>
                                <td class="px-6 py-3"><button class="px-3 py-1 text-xs font-bold border border-rose-200 text-rose-600 rounded-lg hover:bg-rose-50 transition-colors">Perbaiki</button></td>
                            </tr>`;
                        });
                        $('#quality-queue-container tbody').html(qqHtml);

                        // Submission Queue
                        let sqHtml = '';
                        response.submission_queue.forEach(s => {
                            sqHtml += `<tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-3 font-bold">${s.number}</td><td class="px-6 py-3">${s.type}</td>
                                <td class="px-6 py-3">${s.created}</td><td class="px-6 py-3">${s.status}</td>
                                <td class="px-6 py-3 flex gap-2"><button class="px-3 py-1 text-xs font-bold border border-sky-200 text-sky-600 rounded-lg hover:bg-sky-50 transition-colors">Review</button><button class="px-3 py-1 text-xs font-bold bg-emerald-500 text-white rounded-lg hover:bg-emerald-600 shadow-md shadow-emerald-500/20 transition-all">Submit</button></td>
                            </tr>`;
                        });
                        $('#submission-queue-container tbody').html(sqHtml);

                        // Activity Feed
                        let afHtml = '';
                        response.activity_feed.forEach(a => {
                            afHtml += `<li class="px-6 py-4 flex justify-between items-center hover:bg-slate-50 transition-colors gap-4">
                                <span class="text-sm text-slate-700">${a.text}</span><span class="text-xs font-semibold text-slate-400 whitespace-nowrap">${a.time}</span>
                            </li>`;
                        });
                        $('#activity-feed-container ul').html(afHtml);

                        updateFreshnessIndicators();
                    }
                });
            }, 30000);

            setInterval(updateFreshnessIndicators, 60000);
            updateFreshnessIndicators();

            $('.modal').on('hidden.bs.modal', function () {
                $(this).find('form').trigger('reset');
                $(this).find('.is-invalid').removeClass('is-invalid');
                $(this).find('.text-danger').text('');
            });
        });

        function debounce(func, wait, immediate) {
            var timeout;
            return function() {
                var context = this, args = arguments;
                var later = function() {
                    timeout = null;
                    if (!immediate) func.apply(context, args);
                };
                var callNow = immediate && !timeout;
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
                if (callNow) func.apply(context, args);
            };
        }

        const openModalDebounced = debounce(function(action) {
            alert(action);
        }, 500, true);
    </script>
    @endpush
</x-app-layout>
