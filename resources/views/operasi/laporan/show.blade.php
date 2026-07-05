<x-app-layout>
    <x-slot name="header">Detail Laporan</x-slot>
    <x-slot name="breadcrumb">
        <a href="{{ route('dashboard.pwnu') }}" class="text-gray-500 hover:text-gray-700">Home</a>
        <span class="text-gray-400 mx-1">/</span>
        <a href="{{ route('dashboard.laporan.index') }}" class="text-gray-500 hover:text-gray-700">Laporan</a>
        <span class="text-gray-400 mx-1">/</span>
        Detail
    </x-slot>

    @if($laporan->is_valid === 'ya')
    <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-700 flex items-center gap-3">
        <i class="bi bi-check-circle-fill text-xl"></i>
        <div><span class="font-semibold">Laporan Terverifikasi</span> — {{ $laporan->catatan_validasi }}</div>
    </div>
    @elseif($laporan->is_valid === 'tidak')
    <div class="mb-6 p-4 bg-rose-50 border border-rose-200 rounded-xl text-rose-700 flex items-center gap-3">
        <i class="bi bi-x-circle-fill text-xl"></i>
        <div>
            <span class="font-semibold">Laporan Ditolak</span>
            @if($laporan->alasan_tolak === 'hoax')
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700 ml-2">Hoax</span>
            @elseif($laporan->alasan_tolak === 'duplikat')
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-700 ml-2">Duplikat</span>
            @endif
            <div class="text-sm mt-1">{{ $laporan->catatan_validasi ?? 'Tidak ada catatan' }}</div>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
        <div class="lg:col-span-3 space-y-4">
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">Informasi Laporan</h3>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-xs font-medium text-gray-500">Kode Kejadian</dt>
                        <dd class="mt-0.5 text-sm font-mono font-semibold text-gray-900">{{ $laporan->kode_kejadian }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500">Status</dt>
                        <dd class="mt-0.5"><x-badge-status :status="$laporan->is_valid" map="laporan" /></dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500">Pelapor</dt>
                        <dd class="mt-0.5 text-sm text-gray-900">{{ $laporan->nama_pelapor }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500">No. HP Pelapor</dt>
                        <dd class="mt-0.5 text-sm text-gray-900">{{ $laporan->hp_pelapor ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500">Jenis Bencana</dt>
                        <dd class="mt-0.5 text-sm text-gray-900">{{ $laporan->jenisBencana?->nama_bencana ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500">Waktu Kejadian</dt>
                        <dd class="mt-0.5 text-sm text-gray-900">{{ $laporan->waktu_kejadian ? Carbon\Carbon::parse($laporan->waktu_kejadian)->locale('id')->isoFormat('D MMM YYYY, HH:mm') : '-' }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-medium text-gray-500">Lokasi</dt>
                        <dd class="mt-0.5 text-sm text-gray-900">{{ $laporan->titik_kenal ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500">Kabupaten/Kota</dt>
                        <dd class="mt-0.5 text-sm text-gray-900">{{ $laporan->kabupaten?->nama_kab ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500">Kecamatan</dt>
                        <dd class="mt-0.5 text-sm text-gray-900">{{ $laporan->kecamatan?->nama_kec ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500">Desa/Kelurahan</dt>
                        <dd class="mt-0.5 text-sm text-gray-900">{{ $laporan->desa?->nama_desa ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500">Latitude</dt>
                        <dd class="mt-0.5 text-sm font-mono text-gray-900">{{ $laporan->latitude ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500">Longitude</dt>
                        <dd class="mt-0.5 text-sm font-mono text-gray-900">{{ $laporan->longitude ?? '-' }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-medium text-gray-500">Situasi</dt>
                        <dd class="mt-0.5 text-sm text-gray-700 whitespace-pre-wrap bg-gray-50 rounded-lg p-3 border border-gray-100">{{ $laporan->keterangan_situasi ?? 'Tidak ada keterangan' }}</dd>
                    </div>
                </dl>
            </div>

            @if($laporan->photo_path)
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Foto Kejadian</h3>
                <img src="{{ media_url($laporan->photo_path) }}" alt="Foto Kejadian" class="rounded-lg max-w-full h-auto border border-gray-200">
            </div>
            @endif
        </div>

        <div class="lg:col-span-2 space-y-4">
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Lokasi Peta</h3>
                @if($laporan->latitude && $laporan->longitude)
                <div id="peta-laporan" style="height:200px" class="rounded-lg border border-gray-200"></div>
                @else
                <div class="bg-gray-100 flex items-center justify-center h-48 rounded-lg text-gray-400 text-sm">Koordinat tidak tersedia</div>
                @endif
            </div>

            @can('validasi', $laporan)
            @if($laporan->is_valid === 'menunggu')
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="text-sm font-semibold text-gray-700">Tindak Lanjut Laporan</h3>
                    <a href="{{ route('dashboard.laporan.edit', $laporan) }}" class="px-2 py-1 bg-yellow-500 text-white text-xs font-medium rounded hover:bg-yellow-600">
                        <i class="bi bi-pencil-square mr-1"></i>Revisi Laporan
                    </a>
                </div>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <div class="border border-green-200 rounded-lg p-3">
                        <h4 class="text-xs font-semibold text-green-700 mb-2">✓ Validasi & Buat Insiden</h4>
                        <form action="{{ route('dashboard.laporan.verify', $laporan) }}" method="POST" class="space-y-2">
                            @csrf
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-0.5">PCNU Tujuan</label>
                                <select name="id_pcnu" id="pcnu-select" required class="w-full rounded-lg border-gray-300 text-sm focus:border-green-500 focus:ring-green-500">
                                    <option value="">-- Pilih PCNU --</option>
                                    @foreach($pcnuList as $pcnu)
                                    <option value="{{ $pcnu->id_pcnu }}" {{ old('id_pcnu', $laporan->id_pcnu) == $pcnu->id_pcnu ? 'selected' : '' }}>{{ $pcnu->nama_pcnu }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-0.5">Prioritas</label>
                                <select name="prioritas" required class="w-full rounded-lg border-gray-300 text-sm focus:border-green-500 focus:ring-green-500">
                                    <option value="rendah">Rendah</option>
                                    <option value="sedang" selected>Sedang</option>
                                    <option value="tinggi">Tinggi</option>
                                    <option value="kritis">Kritis</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-0.5">Status Insiden</label>
                                <select name="status_insiden" required class="w-full rounded-lg border-gray-300 text-sm focus:border-green-500 focus:ring-green-500">
                                    <option value="terverifikasi">Terverifikasi</option>
                                    <option value="respon">Respon (Langsung turun)</option>
                                </select>
                            </div>

                            <button type="submit" class="w-full px-3 py-1.5 bg-green-600 text-white text-xs font-medium rounded-lg hover:bg-green-700">
                                ✓ Valid & Buat Insiden
                            </button>
                        </form>
                    </div>

                    <div class="border border-red-200 rounded-lg p-3">
                        <h4 class="text-xs font-semibold text-red-700 mb-2">✗ Tolak Laporan</h4>
                        <form action="{{ route('dashboard.laporan.reject', $laporan) }}" method="POST" class="space-y-2">
                            @csrf
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-0.5">Alasan Penolakan</label>
                                <select name="alasan" class="w-full rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">
                                    <option value="">Tidak Valid (alasan lain)</option>
                                    <option value="hoax">Hoax / Informasi Palsu</option>
                                    <option value="duplikat">Duplikat Laporan</option>
                                </select>
                            </div>
                            <textarea name="catatan" rows="2" required class="w-full rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500" placeholder="Catatan tambahan..."></textarea>
                            <button type="submit" class="w-full px-3 py-1.5 bg-red-600 text-white text-xs font-medium rounded-lg hover:bg-red-700">
                                ✗ Tolak Laporan
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endif

            @if($laporan->is_valid === 'ya')
            <div class="bg-white rounded-xl border border-blue-200 p-4">
                <h3 class="text-sm font-semibold text-blue-700 mb-3">Tindak Lanjut</h3>
                @if($laporan->insiden)
                <a href="{{ route('insiden.show', $laporan->insiden) }}" class="inline-flex items-center gap-2 text-sm text-blue-600 hover:text-blue-800 font-medium">
                    <i class="bi bi-eye"></i> Lihat Insiden: {{ $laporan->insiden->kode_kejadian }}
                </a>
                @else
                <p class="text-sm text-gray-500">Laporan ini sudah valid. Gunakan form validasi untuk membuat insiden.</p>
                @endif
            </div>
            @endif
            @endcan
        </div>
    </div>

    @if($laporan->latitude && $laporan->longitude)
    @push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    @endpush
    @push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const map = L.map('peta-laporan').setView([{{ $laporan->latitude }}, {{ $laporan->longitude }}], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap' }).addTo(map);
        L.marker([{{ $laporan->latitude }}, {{ $laporan->longitude }}]).addTo(map);
    </script>
    @endpush
    @endif
</x-app-layout>
