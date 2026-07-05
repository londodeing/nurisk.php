<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-slate-800">{{ $posaju->nama_posaju }}</h2>
            <a href="{{ route('posaju.index') }}" class="px-3 py-1.5 text-xs border border-slate-300 text-slate-600 rounded-xl hover:bg-slate-100 transition-colors flex items-center gap-2"><i class="bi bi-arrow-left"></i> Kembali</a>
        </div>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex border-b border-slate-200 mb-3">
                <a class="px-4 py-2 text-sm font-medium text-indigo-600 border-b-2 border-indigo-600" href="#info" data-bs-toggle="tab">Info</a>
                <a class="px-4 py-2 text-sm font-medium text-slate-500 hover:text-slate-700" href="#stok" data-bs-toggle="tab">Stok</a>
                <a class="px-4 py-2 text-sm font-medium text-slate-500 hover:text-slate-700" href="#personel" data-bs-toggle="tab">Personel</a>
            </div>

            <div class="tab-content">
                <div class="tab-pane active" id="info">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl">
                            <div class="px-6 py-4 border-b border-slate-200"><strong>Informasi Umum</strong></div>
                            <div class="p-6">
                                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm">
                                    <dt class="text-slate-500">Insiden</dt><dd>{{ $posaju->insiden?->kode_kejadian }}</dd>
                                    <dt class="text-slate-500">Alamat</dt><dd>{{ $posaju->alamat_lokasi ?? '—' }}</dd>
                                    <dt class="text-slate-500">Status</dt><dd><span class="px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">{{ $posaju->labelStatus() }}</span></dd>
                                    <dt class="text-slate-500">PJ</dt><dd>{{ $posaju->pj?->profil?->nama_lengkap ?? '—' }}</dd>
                                    <dt class="text-slate-500">Diaktifkan</dt><dd>{{ $posaju->waktu_diaktifkan?->format('d/m/Y H:i') ?? '—' }}</dd>
                                    <dt class="text-slate-500">Ditutup</dt><dd>{{ $posaju->waktu_ditutup?->format('d/m/Y H:i') ?? '—' }}</dd>
                                </dl>
                            </div>
                        </div>
                        <div class="bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl">
                            <div class="px-6 py-4 border-b border-slate-200"><strong>Peta</strong></div>
                            <div class="p-6">
                                <div id="posajuMap" style="height:200px;background:#eee;" class="rounded">
                                    @if($posaju->latitude && $posaju->longitude)
                                    <p class="text-center pt-5">Lat: {{ $posaju->latitude }}, Lng: {{ $posaju->longitude }}</p>
                                    @else
                                    <p class="text-center pt-5 text-slate-500">Koordinat belum diatur</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane" id="stok">
                    <div class="bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl">
                        <div class="p-6">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-slate-200 text-sm">
                                    <thead><tr><th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Barang</th><th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Jumlah</th><th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Satuan</th></tr></thead>
                                    <tbody class="divide-y divide-slate-200">
                                        @forelse($posaju->stok as $s)
                                        <tr>
                                            <td class="px-4 py-2">{{ $s->katalog?->nama_barang_standar ?? '—' }}</td>
                                            <td class="px-4 py-2">{{ $s->jumlah_tersedia }}</td>
                                            <td class="px-4 py-2">{{ $s->katalog?->satuan ?? 'unit' }}</td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="3" class="text-center text-slate-500 py-4">Belum ada stok</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane" id="personel">
                    <div class="bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl">
                        <div class="p-6">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-slate-200 text-sm">
                                    <thead><tr><th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Nama</th><th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Peran</th></tr></thead>
                                    <tbody class="divide-y divide-slate-200">
                                        @forelse($posaju->personel as $p)
                                        <tr>
                                            <td class="px-4 py-2">{{ $p->pengguna?->profil?->nama_lengkap ?? '—' }}</td>
                                            <td class="px-4 py-2">{{ $p->peran ?? 'Relawan' }}</td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="2" class="text-center text-slate-500 py-4">Belum ada personel</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
