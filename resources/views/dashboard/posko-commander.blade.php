<x-app-layout>
    <x-slot name="header">Posko Commander Dashboard</x-slot>
    <x-slot name="actions">
        <!-- QUICK ACTIONS (Max 5) -->
        <div class="btn-group shadow-sm">
            <button class="btn btn-sm btn-primary fw-bold" onclick="alert('Buat Sitrep')"><i class="bi bi-file-text"></i> Buat Sitrep</button>
            <button class="btn btn-sm btn-danger fw-bold" onclick="alert('Buat Eskalasi')"><i class="bi bi-megaphone"></i> Buat Eskalasi</button>
            <button class="btn btn-sm btn-warning fw-bold text-dark" onclick="alert('Hubungi PCNU')"><i class="bi bi-telephone"></i> Hubungi PCNU</button>
            <button class="btn btn-sm btn-info fw-bold text-dark" onclick="alert('Lihat Posko')"><i class="bi bi-house-door"></i> Lihat Posko</button>
            <button class="btn btn-sm btn-success fw-bold" onclick="alert('Lihat Logistik')"><i class="bi bi-box-seam"></i> Lihat Logistik</button>
        </div>
    </x-slot>

    <!-- Row 1: KPI -->
    <div class="row mb-4">
        <div class="col-md-3 col-6"><x-stat-card title="Posko Aktif" value="{{ $initialData['kpi']['posko_aktif'] }}" icon="bi-house-check" color="success" id="kpi-posko" /></div>
        <div class="col-md-3 col-6"><x-stat-card title="Personel Bertugas" value="{{ $initialData['kpi']['personel_bertugas'] }}" icon="bi-people" color="primary" id="kpi-personel" /></div>
        <div class="col-md-3 col-6"><x-stat-card title="Logistik Kritis" value="{{ $initialData['kpi']['logistik_kritis'] }}" icon="bi-exclamation-triangle" color="danger" id="kpi-logistik" /></div>
        <div class="col-md-3 col-6"><x-stat-card title="Eskalasi Aktif" value="{{ $initialData['kpi']['eskalasi_aktif'] }}" icon="bi-megaphone" color="warning" id="kpi-eskalasi" /></div>
    </div>

    <!-- Row 2: ALERT BAR -->
    <div class="row mb-4" id="alerts-container">
        @foreach($initialData['alerts'] as $alert)
            <div class="col-12 mb-2">
                <div class="alert alert-{{ str_replace(' text-dark', '', $alert['badge']) }} fw-bold m-0 d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-exclamation-circle-fill me-2"></i> {{ $alert['message'] }}</span>
                    <span class="badge bg-white text-dark border border-{{ str_replace(' text-dark', '', $alert['badge']) }}">ALERT</span>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Row 3 & 4 & 5 -->
    <div class="row">
        <!-- DECISION QUEUE -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-danger"><i class="bi bi-lightning-charge-fill me-1"></i> Decision Queue</span>
                    <x-data-freshness />
                </div>
                <div class="card-body p-0" id="decision-queue-container">
                    <div class="list-group list-group-flush">
                        @foreach($initialData['decision_queue'] as $item)
                            <div class="list-group-item p-3 border-bottom">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="fw-bold mb-0">
                                        @if($item['priority'] === 'critical')
                                            <i class="bi bi-exclamation-octagon-fill text-danger me-1"></i>
                                        @elseif($item['priority'] === 'high')
                                            <i class="bi bi-exclamation-triangle-fill text-warning me-1"></i>
                                        @else
                                            <i class="bi bi-info-circle-fill text-info me-1"></i>
                                        @endif
                                        {{ $item['title'] }}
                                    </h6>
                                    <span class="badge bg-{{ $item['priority'] === 'critical' ? 'danger' : ($item['priority'] === 'high' ? 'warning text-dark' : 'info') }}">
                                        {{ strtoupper($item['priority']) }}
                                    </span>
                                </div>
                                <p class="mb-1 small"><strong>Dampak:</strong> {{ $item['impact'] }}</p>
                                <p class="mb-2 small text-muted"><strong>Rekomendasi:</strong> {{ $item['recommendation'] }}</p>
                                <a href="{{ $item['action_url'] }}" class="btn btn-sm btn-outline-primary fw-bold">{{ $item['action_label'] }}</a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <!-- RESOURCE STATUS -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white fw-bold"><i class="bi bi-pie-chart-fill me-1"></i> Resource Status</div>
                <div class="card-body" id="resources-container">
                    @php $res = $initialData['resources']; @endphp
                    <div class="mb-3">
                        <div class="d-flex justify-content-between"><small class="fw-bold">Personel ({{ $res['personel']['percent'] }}%)</small><small>{{ $res['personel']['used'] }}/{{ $res['personel']['total'] }}</small></div>
                        <div class="progress" style="height: 10px;"><div class="progress-bar bg-primary" style="width: {{ $res['personel']['percent'] }}%"></div></div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between"><small class="fw-bold">Logistik ({{ $res['logistik']['percent'] }}%)</small><small>{{ $res['logistik']['used'] }}/{{ $res['logistik']['total'] }}</small></div>
                        <div class="progress" style="height: 10px;"><div class="progress-bar bg-success" style="width: {{ $res['logistik']['percent'] }}%"></div></div>
                    </div>
                    <div class="mb-0">
                        <div class="d-flex justify-content-between"><small class="fw-bold">Kapasitas Posko</small><small>{{ $res['posko']['status'] }}</small></div>
                        <div class="progress" style="height: 10px;"><div class="progress-bar bg-info" style="width: {{ $res['posko']['percent'] }}%"></div></div>
                    </div>
                </div>
            </div>

            <!-- ESCALATION CENTER -->
            <div class="card shadow-sm border-0 border-start border-warning border-4 h-100">
                <div class="card-header bg-white fw-bold"><i class="bi bi-megaphone-fill me-1"></i> Escalation Center</div>
                <div class="card-body p-0" id="escalation-container">
                    <div class="list-group list-group-flush">
                        @foreach($initialData['escalations'] as $esc)
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1 fw-bold">{{ $esc['summary'] }}</h6>
                                    <small class="text-muted"><span class="badge bg-{{ $esc['badge'] }} me-1">{{ $esc['severity'] }}</span> {{ $esc['time'] }}</small>
                                </div>
                                <button class="btn btn-sm btn-outline-danger">Eskalasi</button>
                            </div>
                        @endforeach
                    </div>
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
                let badgeClass = 'bg-success';
                if (diffMinutes >= 15 && diffMinutes < 60) badgeClass = 'bg-warning text-dark';
                else if (diffMinutes >= 60) badgeClass = 'bg-danger';

                const indicator = `<span class="badge ${badgeClass}">Updated < 1m ago</span>`;
                $('.data-freshness-container').html(indicator);
            }

            setInterval(function() {
                $.ajax({
                    url: '/dashboard/posko-commander/polling',
                    type: 'GET',
                    success: function(response) {
                        lastPolled = new Date();
                        
                        // Update KPI
                        $('#kpi-posko h3').text(response.kpi.posko_aktif);
                        $('#kpi-personel h3').text(response.kpi.personel_bertugas);
                        $('#kpi-logistik h3').text(response.kpi.logistik_kritis);
                        $('#kpi-eskalasi h3').text(response.kpi.eskalasi_aktif);

                        // Update Alerts
                        let aHtml = '';
                        response.alerts.forEach(alert => {
                            let cleanBadge = alert.badge.replace(' text-dark', '');
                            aHtml += `<div class="col-12 mb-2">
                                <div class="alert alert-${cleanBadge} fw-bold m-0 d-flex justify-content-between align-items-center">
                                    <span><i class="bi bi-exclamation-circle-fill me-2"></i> ${alert.message}</span>
                                    <span class="badge bg-white text-dark border border-${cleanBadge}">ALERT</span>
                                </div>
                            </div>`;
                        });
                        $('#alerts-container').html(aHtml);

                        // Update Decision Queue
                        let dqHtml = '';
                        response.decision_queue.forEach(item => {
                            let icon = '<i class="bi bi-info-circle-fill text-info me-1"></i>', badgeClass = 'info';
                            if (item.priority === 'critical') { icon = '<i class="bi bi-exclamation-octagon-fill text-danger me-1"></i>'; badgeClass = 'danger'; }
                            if (item.priority === 'high') { icon = '<i class="bi bi-exclamation-triangle-fill text-warning me-1"></i>'; badgeClass = 'warning text-dark'; }
                            dqHtml += `<div class="list-group-item p-3 border-bottom">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="fw-bold mb-0">${icon}${item.title}</h6>
                                    <span class="badge bg-${badgeClass}">${item.priority.toUpperCase()}</span>
                                </div>
                                <p class="mb-1 small"><strong>Dampak:</strong> ${item.impact}</p>
                                <p class="mb-2 small text-muted"><strong>Rekomendasi:</strong> ${item.recommendation}</p>
                                <a href="${item.action_url}" class="btn btn-sm btn-outline-primary fw-bold">${item.action_label}</a>
                            </div>`;
                        });
                        $('#decision-queue-container .list-group').html(dqHtml);

                        // Update Resources
                        let res = response.resources;
                        let rHtml = `<div class="mb-3">
                            <div class="d-flex justify-content-between"><small class="fw-bold">Personel (${res.personel.percent}%)</small><small>${res.personel.used}/${res.personel.total}</small></div>
                            <div class="progress" style="height: 10px;"><div class="progress-bar bg-primary" style="width: ${res.personel.percent}%"></div></div>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between"><small class="fw-bold">Logistik (${res.logistik.percent}%)</small><small>${res.logistik.used}/${res.logistik.total}</small></div>
                            <div class="progress" style="height: 10px;"><div class="progress-bar bg-success" style="width: ${res.logistik.percent}%"></div></div>
                        </div>
                        <div class="mb-0">
                            <div class="d-flex justify-content-between"><small class="fw-bold">Kapasitas Posko</small><small>${res.posko.status}</small></div>
                            <div class="progress" style="height: 10px;"><div class="progress-bar bg-info" style="width: ${res.posko.percent}%"></div></div>
                        </div>`;
                        $('#resources-container').html(rHtml);

                        // Update Escalations
                        let eHtml = '';
                        response.escalations.forEach(esc => {
                            eHtml += `<div class="list-group-item d-flex justify-content-between align-items-center">
                                <div><h6 class="mb-1 fw-bold">${esc.summary}</h6>
                                <small class="text-muted"><span class="badge bg-${esc.badge} me-1">${esc.severity}</span> ${esc.time}</small></div>
                                <button class="btn btn-sm btn-outline-danger">Eskalasi</button>
                            </div>`;
                        });
                        $('#escalation-container .list-group').html(eHtml);

                        updateFreshnessIndicators();
                    }
                });
            }, 30000); // 30 seconds

            setInterval(updateFreshnessIndicators, 60000);
            updateFreshnessIndicators();
        });
    </script>
    @endpush
</x-app-layout>
