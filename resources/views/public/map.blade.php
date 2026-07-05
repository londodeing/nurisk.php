@extends('layouts.public')

@section('title', 'Peta Bencana — NURISK')
@section('nav-map', 'active')
@section('layout-class', 'full-width')

@push('head')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endpush

@push('styles')
<style>
    html, body { height: 100%; margin: 0; }
    #tacticalMap {
        width: 100%;
        height: calc(100vh - var(--header-height));
    }
    .page-container { max-width: 100%; padding: 0; width: 100%; }
    .public-content { padding: 0 !important; }
    .bottom-dock {
        z-index: 9999 !important;
        position: fixed !important;
        background: rgba(255,255,255,0.55) !important;
        backdrop-filter: blur(24px) saturate(200%) !important;
        -webkit-backdrop-filter: blur(24px) saturate(200%) !important;
        border: 1px solid rgba(255,255,255,0.35) !important;
        box-shadow: 0 4px 32px rgba(0,0,0,0.10) !important;
    }

    .tactical-popup .leaflet-popup-content-wrapper {
        border-radius: 12px;
        box-shadow: 0 8px 32px rgba(0,0,0,0.12);
        overflow: hidden;
    }
    .tactical-popup .leaflet-popup-content { margin: 12px; }

    .tactical-tooltip {
        background: rgba(0,100,50,0.9);
        color: #fff;
        font-size: 10px;
        font-weight: 700;
        border: none;
        border-radius: 6px;
        padding: 4px 8px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    @keyframes pulse-ring {
        0% { transform: scale(0.5); opacity: 0; }
        50% { opacity: 0.3; }
        100% { transform: scale(2.5); opacity: 0; }
    }
    .quake-pulse {
        animation: pulse-ring 1.5s cubic-bezier(0.215, 0.61, 0.355, 1) infinite;
    }
</style>
@endpush

@section('content')
<div class="page-container" style="position:relative;">
    <div id="tacticalMap"></div>


</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.heat@0.2.0/dist/leaflet-heat.js"></script>
<script>
(function() {
    'use strict';

    const CENTER_JATENG = [-7.15, 110.14];
    const JATENG_BOUNDS = [[-8.8, 108.3], [-5.4, 111.9]];
    const INARISK_WMS_URL = "{{ env('INARISK_WMS_URL', 'https://inarisk1.bnpb.go.id:8443/geoserver/raster/wms') }}";

    const STATUS_COLORS = {
        'draft': '#64748b',
        'terverifikasi': '#3b82f6',
        'respon': '#ef4444',
        'pemulihan': '#eab308',
        'selesai': '#0f172a',
        'dibatalkan': '#6b7280',
    };

    const DISASTER_ICONS = {
        'banjir': 'faucet-drip',
        'banjir bandang': 'cloud-showers-heavy',
        'cuaca ekstrim': 'bolt-lightning',
        'gelombang ekstrim dan abrasi': 'water',
        'gempabumi': 'house-crack',
        'kebakaran hutan dan lahan': 'fire-flame-curved',
        'kekeringan': 'sun-plant-wilt',
        'letusan gunung api': 'volcano',
        'tanah longsor': 'hill-rockslide',
        'tsunami': 'house-tsunami',
        'likuefaksi': 'house-flood-water-circle-arrow-right',
    };

    function getTacticalIcon(type, status) {
        const finalColor = STATUS_COLORS[status] || '#ef4444';
        const iconClass = DISASTER_ICONS[type?.toLowerCase()] || 'satellite-dish';

        if (type?.toLowerCase().includes('gunung api')) {
            return L.divIcon({
                html: '<div class="relative flex items-center justify-center">' +
                    '<div class="quake-pulse absolute h-8 w-8 rounded-full bg-orange-500 opacity-20"></div>' +
                    '<div style="width:0;height:0;border-left:10px solid transparent;border-right:10px solid transparent;border-bottom:22px solid ' + finalColor + ';filter:drop-shadow(0 0 5px ' + finalColor + ');"></div>' +
                    '<div class="absolute top-[12px] text-[7px] font-black text-white italic" style="left:50%;transform:translateX(-50%);">AI</div>' +
                    '</div>',
                className: '',
                iconSize: [24, 24],
                iconAnchor: [12, 22],
            });
        }

        return L.divIcon({
            html: '<div style="background-color:' + finalColor + '" class="w-8 h-8 rounded-lg border-2 border-white shadow-2xl flex items-center justify-center" style="transform:rotate(45deg)">' +
                '<div style="transform:rotate(-45deg)"><i class="fas fa-' + iconClass + ' text-white text-[10px]"></i></div>' +
                '</div>',
            className: '',
            iconSize: [32, 32],
            iconAnchor: [16, 16],
        });
    }

    // Init map
    const map = L.map('tacticalMap', {
        center: CENTER_JATENG,
        zoom: 9,
        minZoom: 7,
        maxZoom: 16,
        zoomControl: false,
    });

    // Fix map size
    setTimeout(function() {
        map.invalidateSize();
        map.setMaxBounds(JATENG_BOUNDS);
    }, 500);

    // Base layers
    const streetLayer = L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OSM</a>, &copy; CARTO',
        maxZoom: 19,
    });

    const satelliteLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        attribution: '&copy; Esri',
        maxNativeZoom: 17,
        maxZoom: 19,
    });

    // Overlay layers
    const himawariLayer = L.tileLayer('https://gibs.earthdata.nasa.gov/wmts/epsg3857/best/Himawari9_AHI_Brightness_Temp_Band13/default/default/GoogleMapsCompatible_Level6/{z}/{y}/{x}.png', {
        opacity: 0.3,
        maxNativeZoom: 6,
        zIndex: 50,
        attribution: 'NASA GIBS',
    });

    function makeWMSLayer(layers, label) {
        return L.tileLayer.wms(INARISK_WMS_URL, {
            layers: layers,
            format: 'image/png',
            transparent: true,
            version: '1.3.0',
            styles: 'index_bahaya',
            opacity: 0.5,
            zIndex: 10,
            attribution: 'BNPB',
            maxNativeZoom: 13,
        });
    }

    const wmsBanjir = makeWMSLayer('raster:INDEKS_BAHAYA_BANJIR1', 'Banjir');
    const wmsBanjirBandang = makeWMSLayer('raster:INDEKS_BAHAYA_BANJIRBANDANG1', 'Banjir Bandang');
    const wmsLongsor = makeWMSLayer('raster:INDEKS_BAHAYA_TANAHLONGSOR1', 'Tanah Longsor');
    const wmsCuaca = makeWMSLayer('raster:INDEKS_BAHAYA_CUACAEKSTRIM1', 'Cuaca Ekstrim');
    const wmsKekeringan = makeWMSLayer('raster:INDEKS_BAHAYA_KEKERINGAN1', 'Kekeringan');
    const wmsGunungApi = makeWMSLayer('raster:INDEKS_BAHAYA_GUNUNGAPI1', 'Gunung Api');

    // Heatmap layers
    const incidentHeat = L.heatLayer([], { radius: 20, blur: 15, maxZoom: 14, max: 1.0, gradient: { 0.4: 'blue', 0.6: 'cyan', 0.7: 'lime', 0.8: 'yellow', 1.0: 'red' } });
    const historicalHeat = L.heatLayer([], { radius: 25, blur: 20, maxZoom: 14, max: 1.0, gradient: { 0.4: 'blue', 0.6: 'cyan', 0.8: 'yellow', 1.0: 'red' } });

    // Layer control
    const baseMaps = {
        'Tactical View (Street)': streetLayer,
        'NASA Satellite Recon': satelliteLayer,
    };

    const overlayMaps = {
        '🌊 [MODEL] Bahaya Banjir': wmsBanjir,
        '🌊 [MODEL] Banjir Bandang': wmsBanjirBandang,
        '⛰️ [MODEL] Tanah Longsor': wmsLongsor,
        '⚡ [MODEL] Cuaca Ekstrim': wmsCuaca,
        '☀️ [MODEL] Kekeringan': wmsKekeringan,
        '🌋 [MODEL] Gunung Api': wmsGunungApi,
        '🛰️ Satelit Himawari-9 (NASA)': himawariLayer,
        '🔥 Heatmap Konsentrasi': incidentHeat,
        '🔥 Historis (Hotspots)': historicalHeat,
    };

    streetLayer.addTo(map);
    L.control.layers(baseMaps, overlayMaps, { position: 'bottomright' }).addTo(map);
    L.control.zoom({ position: 'bottomright' }).addTo(map);

    // GeoJSON boundaries
    fetch('/geojson/indonesia-provinsi.geojson')
        .then(function(r) { return r.json(); })
        .then(function(data) {
            var geoLayer = L.geoJSON(data, {
                filter: function(feature) {
                    return feature.properties.PROVINSI === 'Jawa Tengah';
                },
                style: {
                    color: '#006432',
                    weight: 1.5,
                    fillOpacity: 0,
                    dashArray: '3',
                },
                onEachFeature: function(feature, layer) {
                    layer.bindTooltip('Jawa Tengah', {
                        sticky: true,
                        className: 'tactical-tooltip'
                    });
                }
            });
            geoLayer.addTo(map);
        })
        .catch(function(e) { console.warn('GeoJSON boundary load failed:', e); });

    // ---- DATA LAYERS ----
    var incidentGroup = L.layerGroup().addTo(map);
    var bmkgGroup = L.layerGroup().addTo(map);

    function loadIncidents() {
        fetch('/api/public/dashboard', { headers: { 'Accept': 'application/json' } })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                if (!res.insiden) return;
                incidentGroup.clearLayers();
                var heatPoints = [];

                res.insiden.forEach(function(inc) {
                    if (!inc.lat || !inc.lng) return;

                    heatPoints.push([parseFloat(inc.lat), parseFloat(inc.lng), 0.7]);

                    var popupHtml = '<div class="p-1 w-64 font-sans">' +
                        '<div class="flex justify-between items-start mb-2">' +
                        '<h4 class="font-black text-[#006432] uppercase text-xs leading-tight">' + (inc.jenis || '') + '</h4>' +
                        '<span class="text-[7px] bg-green-600 text-white px-2 py-0.5 rounded font-black uppercase">' + (inc.status || '') + '</span>' +
                        '</div>' +
                        '<div class="space-y-2 border-t pt-2">' +
                        '<div class="flex items-center gap-2 text-slate-500">' +
                        '<i class="fas fa-location-dot text-[10px]"></i>' +
                        '<p class="text-[10px] font-bold uppercase">' + (inc.pcnu || '') + '</p>' +
                        '</div>' +
                        '<p class="text-[11px] leading-relaxed bg-slate-50 p-2 rounded">' + (inc.description || 'Sedang dalam penanganan unit aksi relawan.') + '</p>';

                    if (inc.needs_numeric && Object.keys(inc.needs_numeric).length > 0) {
                        var hasNeeds = false;
                        var needsHtml = '';
                        for (var k in inc.needs_numeric) {
                            if (inc.needs_numeric[k] > 0) {
                                hasNeeds = true;
                                needsHtml += '<span class="text-[7px] bg-white px-1.5 py-0.5 rounded border border-amber-100 font-bold uppercase">' + k + ': ' + inc.needs_numeric[k] + '</span>';
                            }
                        }
                        if (hasNeeds) {
                            popupHtml += '<div class="mt-2 bg-amber-50 p-2 rounded-xl border border-amber-200">' +
                                '<p class="text-[8px] font-black text-amber-700 uppercase mb-1">Kebutuhan Mendesak:</p>' +
                                '<div class="flex flex-wrap gap-1">' + needsHtml + '</div></div>';
                        }
                    }

                    popupHtml += '</div></div>';

                    var marker = L.marker([parseFloat(inc.lat), parseFloat(inc.lng)], {
                        icon: getTacticalIcon(inc.disaster_type || inc.jenis, inc.status)
                    }).bindPopup(popupHtml, { className: 'tactical-popup', maxWidth: 300 });

                    incidentGroup.addLayer(marker);
                });

                incidentHeat.setLatLngs(heatPoints);
            })
            .catch(function(e) { console.warn('Incident load failed:', e); });
    }

    function loadBMKG() {
        fetch('/api/external/bmkg/gempa', { headers: { 'Accept': 'application/json' } })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                bmkgGroup.clearLayers();
                var gempa = data.Infogempa?.gempa || [];
                gempa.forEach(function(g) {
                    if (!g.Coordinates) return;
                    var parts = g.Coordinates.split(',');
                    var lat = parseFloat(parts[0]);
                    var lng = parseFloat(parts[1]);
                    if (isNaN(lat) || isNaN(lng)) return;

                    var popupHtml = '<div class="p-1 w-56 font-sans">' +
                        '<h4 class="font-black text-red-600 uppercase text-[10px] mb-2">Peringatan Seismik</h4>' +
                        '<div class="space-y-1 text-slate-700 border-t pt-2">' +
                        '<p class="text-xs font-bold uppercase">' + (g.Wilayah || '') + '</p>' +
                        '<div class="flex gap-3 mt-2">' +
                        '<div class="bg-red-50 p-2 rounded flex-1">' +
                        '<p class="text-[8px] font-black text-red-600 uppercase">Magnitudo</p>' +
                        '<p class="text-sm font-black">' + (g.Magnitude || '') + ' SR</p>' +
                        '</div>' +
                        '<div class="bg-slate-50 p-2 rounded flex-1">' +
                        '<p class="text-[8px] font-black text-slate-500 uppercase">Kedalaman</p>' +
                        '<p class="text-sm font-black">' + (g.Kedalaman || '') + '</p>' +
                        '</div>' +
                        '</div></div></div>';

                    var icon = L.divIcon({
                        html: '<div class="bg-red-600 w-5 h-5 rounded-full border-2 border-white shadow-[0_0_15px_red] quake-pulse"></div>',
                        className: '',
                        iconSize: [20, 20],
                        iconAnchor: [10, 10],
                    });

                    var marker = L.marker([lat, lng], { icon: icon })
                        .bindPopup(popupHtml, { className: 'tactical-popup', maxWidth: 280 });

                    bmkgGroup.addLayer(marker);
                });
            })
            .catch(function(e) { console.warn('BMKG load failed:', e); });
    }

    function loadHistorical() {
        fetch('/api/histori/bencana', { headers: { 'Accept': 'application/json' } })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                var heatPoints = [];
                (res.data || []).forEach(function(h) {
                    if (h.latitude && h.longitude) {
                        heatPoints.push([parseFloat(h.latitude), parseFloat(h.longitude), h.fixed_intensity || 0.3]);
                    }
                });
                historicalHeat.setLatLngs(heatPoints);
            })
            .catch(function(e) { console.warn('Historical load failed:', e); });
    }

    function syncIntelligence() {
        loadIncidents();
        loadBMKG();
        loadHistorical();
    }

    // ---- INIT (staggered) ----
    loadIncidents();
    setTimeout(loadBMKG, 1000);
    setTimeout(loadHistorical, 2000);

    // ---- POLLING 5 MENIT ----
    setInterval(syncIntelligence, 300000);

})();
</script>
@endpush
