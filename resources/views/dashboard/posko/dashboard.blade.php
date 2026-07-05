@extends('dashboard.layouts.master')

@section('styles')
<style>
.cc-stat-card { transition: background-color 0.3s; }
.cc-stat-card:hover { background-color: #f8f9fa; }
</style>
@endsection

@section('content')
<div class="row g-3 mb-4" data-cc-widget="posko-summary" data-cc-interval="30">
    <div class="col-6 col-md-3">
        <div class="card shadow-sm cc-stat-card text-center h-100">
            <div class="card-body">
                <div class="text-muted small">Personel Hadir</div>
                <div class="display-6 fw-bold cc-val-personel">0</div>
                <x-dashboard-freshness-badge :timestamp="now()" />
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card shadow-sm cc-stat-card text-center h-100">
            <div class="card-body">
                <div class="text-muted small">Tugas Aktif</div>
                <div class="display-6 fw-bold cc-val-tugas">0</div>
                <x-dashboard-freshness-badge :timestamp="now()" />
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card shadow-sm cc-stat-card text-center h-100">
            <div class="card-body">
                <div class="text-muted small">Kebutuhan</div>
                <div class="display-6 fw-bold cc-val-kebutuhan">0</div>
                <x-dashboard-freshness-badge :timestamp="now()" />
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card shadow-sm cc-stat-card text-center h-100">
            <div class="card-body">
                <div class="text-muted small">Posko Aktif</div>
                <div class="display-6 fw-bold cc-val-posko">{{ $poskoIds->count() }}</div>
                <x-dashboard-freshness-badge :timestamp="now()" />
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
                <span class="fw-semibold"><i class="bi bi-list-task me-1"></i>Tugas & Progres</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 cc-tugas-table" data-cc-widget="posko-tugas" data-cc-interval="30">
                        <thead class="table-light">
                            <tr>
                                <th>Tugas</th>
                                <th>Status</th>
                                <th>Progres</th>
                                <th>Pelaksana</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="cc-tugas-body">
                            <tr><td colspan="5" class="text-center text-muted py-3">Memuat data...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
                <span class="fw-semibold"><i class="bi bi-people me-1"></i>Personel</span>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush cc-personel-list" data-cc-widget="posko-personel" data-cc-interval="60">
                    <div class="list-group-item text-center text-muted py-3">Memuat data...</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function() {
    function loadSummary() {
        $.get('{{ route("api.dashboard.posko.summary") }}', function(res) {
            $('.cc-val-personel').text(res.personel ?? 0);
            $('.cc-val-tugas').text(res.tugas_aktif ?? 0);
            $('.cc-val-kebutuhan').text(res.kebutuhan ?? 0);
        });
    }

    function loadTugas() {
        $.get('{{ route("api.dashboard.posko.tugas") }}', function(res) {
            var html = '';
            if (res.tugas && res.tugas.length > 0) {
                res.tugas.forEach(function(t) {
                    var badge = { rencana: 'secondary', berjalan: 'primary', tertunda: 'warning', selesai: 'success' }[t.status] || 'secondary';
                    html += '<tr>' +
                        '<td class="small">' + escapeHtml(t.judul) + '</td>' +
                        '<td><span class="badge bg-' + badge + '">' + t.status + '</span></td>' +
                        '<td><div class="progress" style="height:6px;"><div class="progress-bar" style="width:' + (t.progres || 0) + '%"></div></div><small class="text-muted">' + (t.progres || 0) + '%</small></td>' +
                        '<td class="small">' + escapeHtml(t.pelaksana) + '</td>' +
                        '<td><button class="btn btn-outline-primary btn-sm" title="Update progres"><i class="bi bi-arrow-up-circle"></i></button></td>' +
                        '</tr>';
                });
            } else {
                html = '<tr><td colspan="5" class="text-center text-muted py-3">Belum ada tugas</td></tr>';
            }
            $('#cc-tugas-body').html(html);
        });
    }

    function loadPersonel() {
        $.get('{{ route("api.dashboard.posko.personel") }}', function(res) {
            var html = '';
            if (res.personel && res.personel.length > 0) {
                res.personel.forEach(function(p) {
                    var icon = p.is_hadir ? 'bi-check-circle-fill text-success' : 'bi-clock text-muted';
                    html += '<div class="list-group-item d-flex align-items-center py-2">' +
                        '<i class="bi ' + icon + ' me-2"></i>' +
                        '<div><div class="small fw-semibold">' + escapeHtml(p.nama) + '</div>' +
                        '<div class="text-muted small">' + escapeHtml(p.peran) + '</div></div></div>';
                });
            } else {
                html = '<div class="list-group-item text-center text-muted py-3">Belum ada personel</div>';
            }
            $('.cc-personel-list').html(html);
        });
    }

    loadSummary(); loadTugas(); loadPersonel();
    setInterval(loadSummary, 30000);
    setInterval(loadTugas, 30000);
    setInterval(loadPersonel, 60000);

    function escapeHtml(text) {
        if (!text) return '—';
        return $('<span>').text(text).html();
    }
});
</script>
@endpush
