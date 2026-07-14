@props(['posaju' => null, 'height' => '250px'])

@php
    $lat = $posaju?->latitude ?? null;
    $lng = $posaju?->longitude ?? null;
@endphp

<div id="posajuMap{{ $posaju?->id_posaju ?? 'default' }}" style="height: {{ $height }}; width: 100%;" class="rounded-xl border border-slate-200"></div>

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script>
document.addEventListener('DOMContentLoaded', function() {
    var mapId = 'posajuMap{{ $posaju?->id_posaju ?? 'default' }}';
    var lat = {{ $lat ?? 'null' }};
    var lng = {{ $lng ?? 'null' }};
    var mapEl = document.getElementById(mapId);
    var name = '{{ $posaju?->nama_posaju ?? 'Pos Aju' }}';
    
    if (lat && lng) {
        if (window[mapId + 'Map']) return; // Prevent re-init
        
        var map = L.map(mapId).setView([lat, lng], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);
        L.marker([lat, lng]).addTo(map)
            .bindPopup('<b>' + name + '</b>');
        
        window[mapId + 'Map'] = map;
    } else {
        mapEl.innerHTML = '<div class="flex items-center justify-center h-full text-slate-500"><i class="bi bi-geo-alt text-4xl mb-2"></i><p>Koordinat belum diatur</p></div>';
    }
});
</script>
@endpush