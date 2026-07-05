<x-app-layout>
    <x-slot name="header">Cluster Coordinator: Gap Management Center</x-slot>

    <!-- Row 1: Sector Status Overview -->
    <div class="row mb-4">
        <div class="col-md-3 col-6"><x-stat-card title="Total Kebutuhan" value="{{ $initialData['stats']['total_kebutuhan'] }}" icon="bi-list-ul" color="primary" id="stat-kebutuhan" /></div>
        <div class="col-md-3 col-6"><x-stat-card title="Posko Butuh Bantuan" value="{{ $initialData['stats']['posko_butuh_bantuan'] }}" icon="bi-house-exclamation" color="danger" id="stat-posko" /></div>
        <div class="col-md-3 col-6"><x-stat-card title="Area Belum Terlayani" value="{{ $initialData['stats']['area_unserved'] }}" icon="bi-geo-alt" color="warning" id="stat-area" /></div>
        <div class="col-md-3 col-6"><x-stat-card title="Permintaan Menunggu" value="{{ $initialData['stats']['permintaan_menunggu'] }}" icon="bi-hourglass-split" color="info" id="stat-permintaan" /></div>
    </div>

    <!-- Row 2: Decision Queue -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0 border-start border-danger border-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-danger"><i class="bi bi-lightning-charge-fill me-1"></i> Tactical Decision Queue</span>
                    <x-data-freshness />
                </div>
                <div class="card-body p-0" id="decision-queue-container">
                    <div class="list-group list-group-flush">
                        @foreach($initialData['decision_queue'] as $item)
                            <div class="list-group-item p-3 border-bottom">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h5 class="fw-bold mb-0">
                                        @if($item['priority'] === 'critical')
                                            <i class="bi bi-exclamation-octagon-fill text-danger me-1"></i>
                                        @elseif($item['priority'] === 'high')
                                            <i class="bi bi-exclamation-triangle-fill text-warning me-1"></i>
                                        @else
                                            <i class="bi bi-info-circle-fill text-info me-1"></i>
                                        @endif
                                        {{ $item['title'] }}
                                    </h5>
                                    <span class="badge bg-{{ $item['priority'] === 'critical' ? 'danger' : ($item['priority'] === 'high' ? 'warning text-dark' : 'info') }}">
                                        {{ strtoupper($item['priority']) }}
                                    </span>
                                </div>
                                <p class="mb-1 text-dark"><strong>Dampak:</strong> {{ $item['impact'] }}</p>
                                <p class="mb-3 text-muted"><strong>Rekomendasi:</strong> {{ $item['recommendation'] }}</p>
                                <a href="{{ $item['action_url'] }}" class="btn btn-outline-primary fw-bold">{{ $item['action_label'] }}</a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <!-- Row 3: Gap Analysis Matrix -->
        <div class="col-lg-8 mb-4 mb-lg-0">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white fw-bold"><i class="bi bi-table me-1"></i> Gap Analysis Matrix (Surplus/Defisit)</div>
                <div class="card-body p-0 table-responsive" id="gap-matrix-container">
                    <table class="table table-bordered table-hover mb-0 text-center align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="text-start">Posko</th>
                                <th>Kesehatan</th>
                                <th>Logistik</th>
                                <th>Relawan</th>
                                <th>Perlindungan</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($initialData['gap_matrix'] as $row)
                                <tr>
                                    <td class="text-start fw-bold">{{ $row['posko'] }}</td>
                                    <td class="fw-bold {{ $row['kesehatan'] < 0 ? 'text-danger' : ($row['kesehatan'] > 0 ? 'text-success' : 'text-muted') }}">{{ $row['kesehatan'] > 0 ? '+'.$row['kesehatan'] : $row['kesehatan'] }}</td>
                                    <td class="fw-bold {{ $row['logistik'] < 0 ? 'text-danger' : ($row['logistik'] > 0 ? 'text-success' : 'text-muted') }}">{{ $row['logistik'] > 0 ? '+'.$row['logistik'] : $row['logistik'] }}</td>
                                    <td class="fw-bold {{ $row['relawan'] < 0 ? 'text-danger' : ($row['relawan'] > 0 ? 'text-success' : 'text-muted') }}">{{ $row['relawan'] > 0 ? '+'.$row['relawan'] : $row['relawan'] }}</td>
                                    <td class="fw-bold {{ $row['perlindungan'] < 0 ? 'text-danger' : ($row['perlindungan'] > 0 ? 'text-success' : 'text-muted') }}">{{ $row['perlindungan'] > 0 ? '+'.$row['perlindungan'] : $row['perlindungan'] }}</td>
                                    <td><span class="badge {{ $row['status'] == 'KRITIS' ? 'bg-danger' : ($row['status'] == 'Aman (Surplus)' ? 'bg-success' : 'bg-warning text-dark') }}">{{ $row['status'] }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Row 4: Unserved Area -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 border-start border-warning border-4 h-100">
                <div class="card-header bg-white fw-bold"><i class="bi bi-geo-alt-fill me-1"></i> Unserved Area (Blind Spots)</div>
                <div class="card-body p-0" id="unserved-area-container">
                    <ul class="list-group list-group-flush">
                        @foreach($initialData['unserved_area'] as $area)
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <h6 class="mb-0 fw-bold">{{ $area['area'] }}</h6>
                                    <span class="badge bg-danger">{{ $area['duration'] }}</span>
                                </div>
                                <small class="text-muted"><i class="bi bi-exclamation-circle"></i> {{ $area['issue'] }}</small>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Row 5: Resource Redistribution -->
        <div class="col-lg-6 mb-4 mb-lg-0">
            <div class="card shadow-sm border-0 border-start border-success border-4 h-100">
                <div class="card-header bg-white fw-bold text-success"><i class="bi bi-arrow-left-right me-1"></i> Resource Redistribution (AI Suggestion)</div>
                <div class="card-body p-0" id="redistribution-container">
                    <ul class="list-group list-group-flush">
                        @foreach($initialData['redistribution'] as $redis)
                            <li class="list-group-item d-flex justify-content-between align-items-center p-3">
                                <div>
                                    <span class="badge bg-success me-1">Surplus: {{ $redis['source'] }}</span>
                                    <i class="bi bi-arrow-right mx-1"></i>
                                    <span class="badge bg-danger ms-1">Defisit: {{ $redis['target'] }}</span>
                                    <div class="mt-2 fw-bold text-dark"><i class="bi bi-box"></i> {{ $redis['resource'] }}</div>
                                </div>
                                <button class="btn btn-sm btn-success fw-bold">{{ $redis['action_label'] }}</button>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <!-- Row 6: Escalation Queue -->
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white fw-bold"><i class="bi bi-megaphone-fill me-1"></i> Escalation Tracking</div>
                <div class="card-body p-0 table-responsive" id="escalation-container">
                    <table class="table table-hover mb-0">
                        <thead class="table-light"><tr><th>Ditujukan Ke</th><th>Isu Eskalasi</th><th>Status</th></tr></thead>
                        <tbody>
                            @foreach($initialData['escalations'] as $esc)
                                <tr>
                                    <td class="fw-bold">{{ $esc['to'] }}</td>
                                    <td>{{ $esc['issue'] }}</td>
                                    <td><span class="badge bg-{{ $esc['badge'] }}">{{ $esc['status'] }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
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
                    url: '/dashboard/cluster/polling',
                    type: 'GET',
                    success: function(response) {
                        lastPolled = new Date();
                        
                        $('#stat-kebutuhan h3').text(response.stats.total_kebutuhan);
                        $('#stat-posko h3').text(response.stats.posko_butuh_bantuan);
                        $('#stat-area h3').text(response.stats.area_unserved);
                        $('#stat-permintaan h3').text(response.stats.permintaan_menunggu);

                        // Decision Queue
                        let dqHtml = '';
                        response.decision_queue.forEach(item => {
                            let icon = '<i class="bi bi-info-circle-fill text-info me-1"></i>', badgeClass = 'info';
                            if (item.priority === 'critical') { icon = '<i class="bi bi-exclamation-octagon-fill text-danger me-1"></i>'; badgeClass = 'danger'; }
                            if (item.priority === 'high') { icon = '<i class="bi bi-exclamation-triangle-fill text-warning me-1"></i>'; badgeClass = 'warning text-dark'; }
                            dqHtml += `<div class="list-group-item p-3 border-bottom">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h5 class="fw-bold mb-0">${icon}${item.title}</h5>
                                    <span class="badge bg-${badgeClass}">${item.priority.toUpperCase()}</span>
                                </div>
                                <p class="mb-1 text-dark"><strong>Dampak:</strong> ${item.impact}</p>
                                <p class="mb-3 text-muted"><strong>Rekomendasi:</strong> ${item.recommendation}</p>
                                <a href="${item.action_url}" class="btn btn-outline-primary fw-bold">${item.action_label}</a>
                            </div>`;
                        });
                        $('#decision-queue-container .list-group').html(dqHtml);

                        // Gap Matrix
                        let gmHtml = '';
                        response.gap_matrix.forEach(r => {
                            let format = (val) => val > 0 ? `<td class="fw-bold text-success">+${val}</td>` : (val < 0 ? `<td class="fw-bold text-danger">${val}</td>` : `<td class="fw-bold text-muted">${val}</td>`);
                            let badge = r.status === 'KRITIS' ? 'bg-danger' : (r.status === 'Aman (Surplus)' ? 'bg-success' : 'bg-warning text-dark');
                            gmHtml += `<tr>
                                <td class="text-start fw-bold">${r.posko}</td>
                                ${format(r.kesehatan)} ${format(r.logistik)} ${format(r.relawan)} ${format(r.perlindungan)}
                                <td><span class="badge ${badge}">${r.status}</span></td>
                            </tr>`;
                        });
                        $('#gap-matrix-container tbody').html(gmHtml);

                        // Unserved Area
                        let uaHtml = '';
                        response.unserved_area.forEach(a => {
                            uaHtml += `<li class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <h6 class="mb-0 fw-bold">${a.area}</h6><span class="badge bg-danger">${a.duration}</span>
                                </div><small class="text-muted"><i class="bi bi-exclamation-circle"></i> ${a.issue}</small>
                            </li>`;
                        });
                        $('#unserved-area-container .list-group').html(uaHtml);

                        // Redistribution
                        let rdHtml = '';
                        response.redistribution.forEach(r => {
                            rdHtml += `<li class="list-group-item d-flex justify-content-between align-items-center p-3">
                                <div><span class="badge bg-success me-1">Surplus: ${r.source}</span> <i class="bi bi-arrow-right mx-1"></i> <span class="badge bg-danger ms-1">Defisit: ${r.target}</span>
                                <div class="mt-2 fw-bold text-dark"><i class="bi bi-box"></i> ${r.resource}</div></div>
                                <button class="btn btn-sm btn-success fw-bold">${r.action_label}</button>
                            </li>`;
                        });
                        $('#redistribution-container .list-group').html(rdHtml);

                        // Escalations
                        let esHtml = '';
                        response.escalations.forEach(e => {
                            esHtml += `<tr><td class="fw-bold">${e.to}</td><td>${e.issue}</td><td><span class="badge bg-${e.badge}">${e.status}</span></td></tr>`;
                        });
                        $('#escalation-container tbody').html(esHtml);

                        updateFreshnessIndicators();
                    }
                });
            }, 30000);

            setInterval(updateFreshnessIndicators, 60000);
            updateFreshnessIndicators();
        });
    </script>
    @endpush
</x-app-layout>
