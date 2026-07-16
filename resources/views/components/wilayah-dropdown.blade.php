@props(['kabupatenList' => [], 'selectedKab' => null, 'selectedKec' => null, 'selectedDesa' => null])

<div class="space-y-3">
    <div>
        <label class="block text-xs font-medium text-gray-700 mb-1">Kabupaten/Kota</label>
        <select name="id_kabupaten" id="kabupaten-select" class="w-full rounded-lg border-gray-300 text-sm focus:border-green-500 focus:ring-primary-500">
            <option value="">Pilih Kabupaten</option>
            @foreach($kabupatenList as $kab)
            <option value="{{ $kab->id_kab }}" {{ $selectedKab == $kab->id_kab ? 'selected' : '' }}>
                {{ $kab->nama_kab }}
            </option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-700 mb-1">Kecamatan</label>
        <select name="id_kecamatan" id="kecamatan-select" class="w-full rounded-lg border-gray-300 text-sm focus:border-green-500 focus:ring-primary-500" {{ !$selectedKab ? 'disabled' : '' }}>
            <option value="">Pilih Kecamatan</option>
        </select>
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-700 mb-1">Desa/Kelurahan</label>
        <select name="id_desa" id="desa-select" class="w-full rounded-lg border-gray-300 text-sm focus:border-green-500 focus:ring-primary-500" {{ !$selectedKec ? 'disabled' : '' }}>
            <option value="">Pilih Desa</option>
        </select>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const kabSelect = document.getElementById('kabupaten-select');
    const kecSelect = document.getElementById('kecamatan-select');
    const desaSelect = document.getElementById('desa-select');

    if (kabSelect) {
        kabSelect.addEventListener('change', function() {
            const kabId = this.value;
            kecSelect.innerHTML = '<option value="">Pilih Kecamatan</option>';
            desaSelect.innerHTML = '<option value="">Pilih Desa</option>';
            kecSelect.disabled = !kabId;
            desaSelect.disabled = true;
            if (kabId) {
                fetch(`/api/wilayah/kecamatan?id_kab=${kabId}`)
                    .then(r => r.json())
                    .then(data => {
                        data.forEach(k => {
                            kecSelect.innerHTML += `<option value="${k.id}">${k.nama}</option>`;
                        });
                        @if($selectedKec)
                        kecSelect.value = '{{ $selectedKec }}';
                        kecSelect.dispatchEvent(new Event('change'));
                        @endif
                    });
            }
        });
    }

    if (kecSelect) {
        kecSelect.addEventListener('change', function() {
            const kecId = this.value;
            desaSelect.innerHTML = '<option value="">Pilih Desa</option>';
            desaSelect.disabled = !kecId;
            if (kecId) {
                fetch(`/api/wilayah/desa?id_kec=${kecId}`)
                    .then(r => r.json())
                    .then(data => {
                        data.forEach(d => {
                            desaSelect.innerHTML += `<option value="${d.id_desa}">${d.nama_desa}</option>`;
                        });
                        @if($selectedDesa)
                        desaSelect.value = '{{ $selectedDesa }}';
                        @endif
                    });
            }
        });
    }

    @if($selectedKab)
    kabSelect.value = '{{ $selectedKab }}';
    kabSelect.dispatchEvent(new Event('change'));
    @endif
});
</script>
@endpush
