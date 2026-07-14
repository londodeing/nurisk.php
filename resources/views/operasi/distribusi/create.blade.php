<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-slate-800">Buat Distribusi Bantuan Baru</h2>
            <a href="{{ route('insiden.posaju.distribusi.index', [$insiden, $posaju]) }}"
                class="px-3 py-1.5 text-xs border border-slate-300 text-slate-600 rounded-xl hover:bg-slate-100 transition-colors flex items-center gap-2">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </x-slot>
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl">
                <form method="POST" action="{{ route('insiden.posaju.distribusi.store', [$insiden, $posaju]) }}" class="p-6 space-y-4">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Pos Aju <span class="text-danger">*</span></label>
                        <input type="text" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm bg-slate-50" value="{{ $posaju->nama_posaju }}" readonly>
                        <input type="hidden" name="id_posaju" value="{{ $posaju->id_posaju }}">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Klaster <span class="text-danger">*</span></label>
                        <select name="id_klaster_operasi" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300" required>
                            <option value="">— Pilih Klaster —</option>
                            @foreach($klasterList as $k)
                            <option value="{{ $k->id_klaster_operasi }}">{{ $k->masterKlaster?->nama_klaster ?? '—' }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Petugas Penugasan</label>
                        <select name="id_penugasan" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300">
                            <option value="">— Pilih Petugas (opsional) —</option>
                            @foreach($penugasanList as $p)
                            <option value="{{ $p->id_penugasan }}">{{ $p->pengguna?->profil?->nama_lengkap ?? '—' }} ({{ $p->peran_otoritas }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Barang dari Katalog</label>
                        <select name="id_barang_katalog" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300" id="barangKatalogSelect">
                            <option value="">— Pilih dari Katalog (opsional) —</option>
                            @foreach($barangKatalog as $b)
                            <option value="{{ $b->id_katalog }}" data-satuan="{{ $b->satuan ?? 'unit' }}">{{ $b->nama_barang_standar }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-slate-500">Jika barang tidak ada di katalog, isi nama manual di bawah.</p>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Nama Barang <span class="text-danger">*</span></label>
                        <input type="text" name="nama_barang" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300" required>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Jumlah <span class="text-danger">*</span></label>
                            <input type="number" name="jumlah" step="0.01" min="0.01" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Satuan <span class="text-danger">*</span></label>
                            <input type="text" name="satuan" id="satuanInput" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300" required placeholder="cth: unit, kg, liter, pcs">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Lokasi Tujuan</label>
                        <textarea name="lokasi_tujuan" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300" rows="2"></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Penerima</label>
                        <input type="text" name="penerima" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300" placeholder="Nama kelompok/individu penerima">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Waktu Distribusi <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="waktu_distribusi" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300" required>
                    </div>

                    <div class="pt-4 border-t border-slate-200 flex justify-end gap-2">
                        <a href="{{ route('insiden.posaju.distribusi.index', [$insiden, $posaju]) }}" class="px-4 py-2 border border-slate-300 text-slate-600 rounded-xl text-sm font-semibold hover:bg-slate-100 transition-colors">Batal</a>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-semibold hover:bg-indigo-700 transition-colors flex items-center gap-2">
                            <i class="bi bi-save"></i> Simpan Rencana
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const katalogSelect = document.getElementById('barangKatalogSelect');
    const namaBarangInput = document.querySelector('input[name="nama_barang"]');
    const satuanInput = document.getElementById('satuanInput');

    if (katalogSelect && namaBarangInput && satuanInput) {
        katalogSelect.addEventListener('change', function() {
            const selected = this.options[this.selectedIndex];
            if (selected.value) {
                namaBarangInput.value = selected.text;
                if (selected.dataset.satuan) {
                    satuanInput.value = selected.dataset.satuan;
                }
            } else {
                namaBarangInput.value = '';
                // satuanInput.value = ''; // keep manual
            }
        });
    }
});
</script>