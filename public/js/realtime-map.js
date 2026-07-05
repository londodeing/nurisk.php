document.addEventListener("DOMContentLoaded", function () {
    // Inisialisasi Map Leaflet
    var map = L.map('live-map').setView([-2.548926, 118.0148634], 5); // Pusat Indonesia

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    var markerLayer = L.layerGroup().addTo(map);

    // Fungsi Fetch Data Awal
    function fetchCommandCenterData() {
        fetch(window.location.href, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            updateStats(data.stats);
            updateMap(data.points);
        })
        .catch(err => console.error('Gagal memuat data Command Center', err));
    }

    // Fungsi Update Statistik
    function updateStats(stats) {
        document.getElementById('stat-relawan').innerText = stats.relawan_aktif;
        document.getElementById('stat-posko').innerText = stats.posko_aktif;
        document.getElementById('stat-logistik').innerText = Number(stats.total_logistik).toLocaleString();
        document.getElementById('stat-insiden').innerText = stats.insiden_aktif;
    }

    // Fungsi Update Peta
    function updateMap(points) {
        markerLayer.clearLayers();
        let hasPoints = false;
        let bounds = L.latLngBounds();

        points.forEach(point => {
            if (point.latitude && point.longitude) {
                let color = point.type === 'posko' ? 'blue' : 'red';
                let marker = L.circleMarker([point.latitude, point.longitude], {
                    radius: 8,
                    fillColor: color,
                    color: "#fff",
                    weight: 1,
                    opacity: 1,
                    fillOpacity: 0.8
                }).addTo(markerLayer);

                marker.bindPopup(`<b>${point.name}</b><br>Status: ${point.status}`);
                bounds.extend([point.latitude, point.longitude]);
                hasPoints = true;
            }
        });

        if (hasPoints) {
            map.fitBounds(bounds, { padding: [50, 50] });
        }
    }

    // Load data awal
    fetchCommandCenterData();

    // Koneksi Realtime menggunakan Laravel Echo (jika tersedia)
    if (typeof Echo !== 'undefined') {
        const connStatus = document.getElementById('connection-status');
        connStatus.innerText = 'Terkoneksi';
        connStatus.classList.replace('bg-secondary', 'bg-success');

        // Dengarkan channel global "operasi.global" (contoh)
        Echo.channel('operasi.global')
            .listen('OperasiUpdated', (e) => {
                console.log('Update Live Diterima', e);
                // Refresh data
                fetchCommandCenterData();
            });
    } else {
        const connStatus = document.getElementById('connection-status');
        connStatus.innerText = 'Polling Mode';
        connStatus.classList.replace('bg-secondary', 'bg-warning');

        // Fallback polling tiap 30 detik
        setInterval(fetchCommandCenterData, 30000);
    }
});
