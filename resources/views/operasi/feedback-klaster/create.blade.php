<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-slate-800">Buat Feedback Klaster</h2>
            <a href="{{ route('insiden.feedback-klaster.index', $insiden) }}" class="px-3 py-1.5 text-xs border border-slate-300 text-slate-600 rounded-xl hover:bg-slate-100 transition-colors flex items-center gap-2"><i class="bi bi-arrow-left"></i> Kembali</a>
        </div>
    </x-slot>
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl">
                <form method="POST" action="{{ route('insiden.feedback-klaster.store', $insiden) }}" class="p-6 space-y-6">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Insiden</label>
                        <input type="text" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm bg-slate-50" value="{{ $insiden->kode_kejadian }}" readonly>
                        <input type="hidden" name="id_insiden" value="{{ $insiden->id_insiden }}">
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

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Kecukupan Sumberdaya <span class="text-danger">*</span></label>
                            <div class="d-flex gap-3">
                                @foreach(['kurang', 'cukup', 'berlebih'] as $opt)
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="kecukupan_sumberdaya" id="kecukupan_{{ $opt }}" value="{{ $opt }}" required>
                                    <label class="form-check-label" for="kecukupan_{{ $opt }}">{{ ucfirst($opt) }}</label>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Kualitas Layanan <span class="text-danger">*</span></label>
                            <div class="d-flex gap-3">
                                @foreach(['baik', 'sedang', 'buruk'] as $opt)
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="kualitas_layanan" id="kualitas_{{ $opt }}" value="{{ $opt }}" required>
                                    <label class="form-check-label" for="kualitas_{{ $opt }}">{{ ucfirst($opt) }}</label>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Tepat Waktu <span class="text-danger">*</span></label>
                            <div class="d-flex gap-3">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="tepat_waktu" value="1" required>
                                    <label class="form-check-label">Ya</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="tepat_waktu" value="0">
                                    <label class="form-check-label">Tidak</label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Tepat Sasaran <span class="text-danger">*</span></label>
                            <div class="d-flex gap-3">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="tepat_sasaran" value="1" required>
                                    <label class="form-check-label">Ya</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="tepat_sasaran" value="0">
                                    <label class="form-check-label">Tidak</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Kendala</label>
                        <textarea name="kendala" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300" rows="2" placeholder="Kendala yang dihadapi saat pelaksanaan..."></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Rekomendasi</label>
                        <textarea name="rekomendasi" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300" rows="2" placeholder="Rekomendasi perbaikan untuk kegiatan mendatang..."></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 mb-2">Gap Terdeteksi <span class="text-slate-500 text-sm font-normal">(Opsional - dapat ditambahkan nanti)</span></label>
                        <div id="gapContainer" class="space-y-3">
                            <button type="button" onclick="addGap()" class="px-3 py-1.5 text-xs border border-indigo-300 text-indigo-600 rounded-xl hover:bg-indigo-50 transition-colors flex items-center gap-1"><i class="bi bi-plus-lg"></i> Tambah Gap</button>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-200 flex justify-end gap-2">
                        <a href="{{ route('insiden.feedback-klaster.index', $insiden) }}" class="px-4 py-2 border border-slate-300 text-slate-600 rounded-xl text-sm font-semibold hover:bg-slate-100 transition-colors">Batal</a>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-semibold hover:bg-indigo-700 transition-colors flex items-center gap-2"><i class="bi bi-lock-fill"></i> Kunci & Simpan Feedback</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
let gapIndex = 0;

function addGap() {
    const container = document.getElementById('gapContainer');
    const index = gapIndex++;
    
    const div = document.createElement('div');
    div.className = 'p-3 border border-slate-200 rounded-lg bg-slate-50 gap-item';
    div.innerHTML = `
        <div class="flex items-center justify-between mb-2">
            <span class="font-medium text-sm">Gap #${index + 1}</span>
            <button type="button" onclick="this.closest('.gap-item').remove()" class="px-2 py-1 text-xs border border-red-300 text-red-600 rounded hover:bg-red-50">Hapus</button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-2 mb-2">
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Jenis Gap *</label>
                <select name="gap_terdeteksi[${index}][jenis_gap]" class="w-full px-2 py-1.5 border border-slate-300 rounded text-xs focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300" required>
                    <option value="">— Pilih —</option>
                    <option value="sumberdaya">Sumberdaya</option>
                    <option value="personel">Personel</option>
                    <option value="logistik">Logistik</option>
                    <option value="informasi">Informasi</option>
                    <option value="koordinasi">Koordinasi</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Prioritas *</label>
                <select name="gap_terdeteksi[${index}][prioritas]" class="w-full px-2 py-1.5 border border-slate-300 rounded text-xs focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300" required>
                    <option value="rendah">Rendah</option>
                    <option value="sedang" selected>Sedang</option>
                    <option value="tinggi">Tinggi</option>
                    <option value="kritis">Kritis</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Selisih Jumlah</label>
                <input type="number" step="0.01" name="gap_terdeteksi[${index}][selisih_jumlah]" class="w-full px-2 py-1.5 border border-slate-300 rounded text-xs focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300" placeholder="0">
            </div>
        </div>
        <div class="mb-2">
            <label class="block text-xs font-medium text-slate-500 mb-1">Deskripsi Gap *</label>
            <textarea name="gap_terdeteksi[${index}][deskripsi]" class="w-full px-2 py-1.5 border border-slate-300 rounded text-xs focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300" rows="2" required placeholder="Deskripsi gap yang terdeteksi..."></textarea>
        </div>
        <div class="grid grid-cols-2 gap-2">
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Satuan</label>
                <input type="text" name="gap_terdeteksi[${index}][satuan]" class="w-full px-2 py-1.5 border border-slate-300 rounded text-xs focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300" placeholder="cth: unit, kg, orang">
            </div>
        </div>
    `;
    container.appendChild(div);
}
</script>