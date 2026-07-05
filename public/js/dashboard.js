/**
 * NURISK Command Center — Dashboard Polling Manager
 * jQuery-based AJAX polling with stratified intervals.
 * No SSE, WebSocket, or Redis.
 */
(function($) {
    'use strict';

    var CC_POLLING = {
        red: 30000,
        blue: 60000,
        black: 300000
    };

    var activeTimers = {};

    function getEndpoint($el) {
        var endpoint = $el.data('cc-endpoint');
        if (endpoint) return endpoint;

        var widget = $el.data('cc-widget');
        if (!widget) return null;

        var page = window.location.pathname.split('/').pop() || 'posko';
        return '/dashboard/api/' + page + '/' + widget;
    }

    function render($el, data) {
        var widget = $el.data('cc-widget');
        if (!widget) return;

        switch (widget) {
            case 'posko-summary':
                $el.find('.cc-val-personel').text(data.personel || 0);
                $el.find('.cc-val-tugas').text(data.tugas_aktif || 0);
                $el.find('.cc-val-kebutuhan').text(data.kebutuhan || 0);
                break;

            case 'posko-tugas':
                var html = '';
                if (data.tugas && data.tugas.length) {
                    data.tugas.forEach(function(t) {
                        var badgeMap = { rencana: 'secondary', berjalan: 'primary', tertunda: 'warning', selesai: 'success' };
                        var badge = badgeMap[t.status] || 'secondary';
                        html += '<tr><td class="small">' + esc(t.judul) + '</td>' +
                            '<td><span class="badge bg-' + badge + '">' + (t.status || '') + '</span></td>' +
                            '<td><div class="progress" style="height:6px"><div class="progress-bar" style="width:' + (t.progres || 0) + '%"></div></div><small class="text-muted">' + (t.progres || 0) + '%</small></td>' +
                            '<td class="small">' + esc(t.pelaksana) + '</td>' +
                            '<td><button class="btn btn-outline-primary btn-sm"><i class="bi bi-arrow-up-circle"></i></button></td></tr>';
                    });
                } else {
                    html = '<tr><td colspan="5" class="text-center text-muted py-3">Belum ada tugas</td></tr>';
                }
                $el.find('tbody').html(html);
                break;

            case 'posko-personel':
                var html = '';
                if (data.personel && data.personel.length) {
                    data.personel.forEach(function(p) {
                        var icon = p.is_hadir ? 'bi-check-circle-fill text-success' : 'bi-clock text-muted';
                        html += '<div class="list-group-item d-flex align-items-center py-2">' +
                            '<i class="bi ' + icon + ' me-2"></i>' +
                            '<div><div class="small fw-semibold">' + esc(p.nama) + '</div>' +
                            '<div class="text-muted small">' + esc(p.peran) + '</div></div></div>';
                    });
                } else {
                    html = '<div class="list-group-item text-center text-muted py-3">Belum ada personel</div>';
                }
                $el.html(html);
                break;

            case 'decision-queue':
                var count = (data.queue && data.queue.length) || 0;
                $el.find('.cc-queue-count').text(count);
                if (data.queue && data.queue.length) {
                    var html = '';
                    data.queue.forEach(function(item) {
                        var severityIcon = 'bi-info-circle-fill text-info';
                        if (item.severity === 'critical') severityIcon = 'bi-exclamation-circle-fill text-danger';
                        else if (item.severity === 'high') severityIcon = 'bi-exclamation-triangle-fill text-warning';
                        html += '<div class="d-flex align-items-start p-2 border-bottom">' +
                            '<span class="me-2 mt-1"><i class="bi ' + severityIcon + '"></i></span>' +
                            '<div class="flex-grow-1"><div class="fw-semibold small">' + esc(item.judul) + '</div>' +
                            '<div class="text-muted small">' + esc(item.deskripsi) + '</div></div></div>';
                    });
                    $el.find('.cc-queue-body').html(html);
                } else {
                    $el.find('.cc-queue-body').html('<div class="text-center text-muted py-3 small"><i class="bi bi-check-circle text-success"></i> Tidak ada keputusan yang menunggu</div>');
                }
                $el.find('.cc-last-checked').text(new Date().toLocaleTimeString('id-ID'));
                break;
        }

        if (data.timestamp) {
            $el.find('.cc-freshness').each(function() {
                var label = momentAgo(data.timestamp);
                $(this).text(label);
            });
        }
    }

    function poll($el) {
        var endpoint = getEndpoint($el);
        if (!endpoint) return;

        $.get(endpoint, function(data) {
            render($el, data);
        }).fail(function() {
            console.warn('Polling failed:', endpoint);
        });
    }

    function register($el) {
        var interval = parseInt($el.data('cc-interval'), 10);
        if (!interval) return;

        var key = ($el.data('cc-widget') || '') + '_' + $el.data('cc-endpoint') || Math.random();
        if (activeTimers[key]) return;

        poll($el);
        activeTimers[key] = setInterval(function() { poll($el); }, interval * 1000);
    }

    function esc(text) {
        if (!text) return '—';
        return $('<span>').text(text).html();
    }

    function momentAgo(iso) {
        if (!iso) return '—';
        var diff = (new Date() - new Date(iso)) / 1000;
        if (diff < 60) return 'baru saja';
        if (diff < 3600) return Math.floor(diff / 60) + ' menit lalu';
        if (diff < 86400) return Math.floor(diff / 3600) + ' jam lalu';
        return Math.floor(diff / 86400) + ' hari lalu';
    }

    $(document).ready(function() {
        $('[data-cc-widget]').each(function() { register($(this)); });
    });

    window.CC_Polling = { register: register, poll: poll, render: render };

})(jQuery);
