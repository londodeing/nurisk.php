<x-app-layout>
    <x-slot name="header">Revisi Laporan</x-slot>
    <x-slot name="breadcrumb">
        <a href="{{ route('dashboard.pwnu') }}" class="text-gray-500 hover:text-gray-700">Home</a>
        <span class="text-gray-400 mx-1">/</span>
        <a href="{{ route('dashboard.laporan.index') }}" class="text-gray-500 hover:text-gray-700">Laporan</a>
        <span class="text-gray-400 mx-1">/</span>
        <a href="{{ route('dashboard.laporan.show', $laporan) }}" class="text-gray-500 hover:text-gray-700">{{ $laporan->kode_kejadian }}</a>
        <span class="text-gray-400 mx-1">/</span>
        Revisi
    </x-slot>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <form action="{{ route('dashboard.laporan.update', $laporan) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Jenis Bencana -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Kejadian</label>
                    <select name="id_jenis_bencana" required class="w-full rounded-lg border-gray-300">
                        @foreach($jenisBencanaList as $jb)
                        <option value="{{ $jb->id_jenis }}" {{ old('id_jenis_bencana', $laporan->id_jenis_bencana) == $jb->id_jenis ? 'selected' : '' }}>
                            {{ $jb->nama_bencana }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Keterangan Situasi -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan Situasi</label>
                    <textarea name="keterangan_situasi" rows="4" required class="w-full rounded-lg border-gray-300">{{ old('keterangan_situasi', $laporan->keterangan_situasi) }}</textarea>
                </div>

                <!-- Titik Kenal -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Titik Kenal (Lokasi)</label>
                    <input type="text" name="titik_kenal" value="{{ old('titik_kenal', $laporan->titik_kenal) }}" class="w-full rounded-lg border-gray-300">
                </div>

                <!-- Kabupaten -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kabupaten/Kota</label>
                    <select name="id_kab" id="id_kab" class="w-full rounded-lg border-gray-300" onchange="loadKecamatan()">
                        <option value="">-- Pilih Kabupaten --</option>
                        @foreach($kabupatenList as $kab)
                        <option value="{{ $kab->id_kab }}" {{ old('id_kab', $laporan->id_kab) == $kab->id_kab ? 'selected' : '' }}>
                            {{ $kab->nama_kab }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Kecamatan -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kecamatan</label>
                    <select name="id_kec" id="id_kec" class="w-full rounded-lg border-gray-300" onchange="loadDesa()" data-selected="{{ old('id_kec', $laporan->id_kec) }}">
                        <option value="">-- Pilih Kecamatan --</option>
                    </select>
                </div>

                <!-- Desa -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Desa/Kelurahan</label>
                    <select name="id_desa" id="id_desa" class="w-full rounded-lg border-gray-300" data-selected="{{ old('id_desa', $laporan->id_desa) }}">
                        <option value="">-- Pilih Desa --</option>
                    </select>
                </div>

                <!-- Koordinat -->
                <div class="md:col-span-2 grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Latitude</label>
                        <input type="text" name="latitude" id="latitude" value="{{ old('latitude', $laporan->latitude) }}" required class="w-full rounded-lg border-gray-300">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Longitude</label>
                        <input type="text" name="longitude" id="longitude" value="{{ old('longitude', $laporan->longitude) }}" required class="w-full rounded-lg border-gray-300">
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <a href="{{ route('dashboard.laporan.show', $laporan) }}" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">Batal</a>
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">Simpan Perubahan</button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        function loadKecamatan(initial = false) {
            var idKab = document.getElementById('id_kab').value;
            var selectKec = document.getElementById('id_kec');
            var selectDesa = document.getElementById('id_desa');
            var selectedKec = selectKec.getAttribute('data-selected');
            
            if (!initial) {
                selectKec.innerHTML = '<option value="">-- Memuat Kecamatan... --</option>';
                selectDesa.innerHTML = '<option value="">-- Pilih Desa --</option>';
            }

            if (!idKab) {
                selectKec.innerHTML = '<option value="">-- Pilih Kecamatan --</option>';
                return;
            }

            fetch('/api/wilayah/kecamatan?id_kab=' + idKab)
                .then(r => r.json())
                .then(data => {
                    selectKec.innerHTML = '<option value="">-- Pilih Kecamatan --</option>';
                    data.forEach(item => {
                        var option = document.createElement('option');
                        option.value = item.id_kec;
                        option.text = item.nama_kec;
                        if (item.id_kec == selectedKec) option.selected = true;
                        selectKec.appendChild(option);
                    });
                    if (initial) loadDesa(true);
                });
        }

        function loadDesa(initial = false) {
            var idKec = document.getElementById('id_kec').value;
            var selectDesa = document.getElementById('id_desa');
            var selectedDesa = selectDesa.getAttribute('data-selected');
            
            if (!initial) {
                selectDesa.innerHTML = '<option value="">-- Memuat Desa... --</option>';
            }

            if (!idKec) {
                selectDesa.innerHTML = '<option value="">-- Pilih Desa --</option>';
                return;
            }

            fetch('/api/wilayah/desa?id_kec=' + idKec)
                .then(r => r.json())
                .then(data => {
                    selectDesa.innerHTML = '<option value="">-- Pilih Desa --</option>';
                    data.forEach(item => {
                        var option = document.createElement('option');
                        option.value = item.id_desa;
                        option.text = item.nama_desa;
                        if (item.id_desa == selectedDesa) option.selected = true;
                        selectDesa.appendChild(option);
                    });
                });
        }

        document.addEventListener('DOMContentLoaded', function() {
            if (document.getElementById('id_kab').value) {
                loadKecamatan(true);
            }
        });
    </script>
    @endpush
</x-app-layout>
