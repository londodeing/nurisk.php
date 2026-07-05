<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-slate-800">Command Center</h2>
        </div>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                <div class="bg-blue-600 text-white rounded-2xl shadow-xl border border-blue-400/40">
                    <div class="p-6">
                        <h5 class="font-semibold opacity-90">Relawan Aktif</h5>
                        <h2 id="stat-relawan" class="text-3xl font-bold">0</h2>
                    </div>
                </div>
                <div class="bg-green-600 text-white rounded-2xl shadow-xl border border-green-400/40">
                    <div class="p-6">
                        <h5 class="font-semibold opacity-90">Posko Aktif</h5>
                        <h2 id="stat-posko" class="text-3xl font-bold">0</h2>
                    </div>
                </div>
                <div class="bg-yellow-500 text-black rounded-2xl shadow-xl border border-yellow-400/40">
                    <div class="p-6">
                        <h5 class="font-semibold opacity-80">Total Logistik</h5>
                        <h2 id="stat-logistik" class="text-3xl font-bold">0</h2>
                    </div>
                </div>
                <div class="bg-red-600 text-white rounded-2xl shadow-xl border border-red-400/40">
                    <div class="p-6">
                        <h5 class="font-semibold opacity-90">Insiden Aktif</h5>
                        <h2 id="stat-insiden" class="text-3xl font-bold">0</h2>
                    </div>
                </div>
            </div>

            <div class="bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl">
                <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                    <h5 class="font-semibold text-slate-800">Peta Situasi (Live)</h5>
                    <span id="connection-status" class="px-2 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">Mengkoneksikan...</span>
                </div>
                <div class="p-0">
                    <div id="live-map" style="height: 600px; width: 100%;"></div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="{{ asset('js/realtime-map.js') }}"></script>
@endpush
