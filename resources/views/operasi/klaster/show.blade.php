<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-slate-800">{{ $klaster->masterKlaster?->nama_klaster ?? 'Klaster' }}</h2>
            <a href="{{ route('klaster.index') }}" class="px-3 py-1.5 text-xs border border-slate-300 text-slate-600 rounded-xl hover:bg-slate-100 transition-colors flex items-center gap-2"><i class="bi bi-arrow-left"></i> Kembali</a>
        </div>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="bg-green-100 border border-green-200 text-green-700 px-4 py-3 rounded-xl mb-3 flex items-center justify-between">
                    <span>{{ session('success') }}</span>
                    <button type="button" class="text-green-500 hover:text-green-700" data-bs-dismiss="alert">&times;</button>
                </div>
            @endif
            @if (session('error'))
                <div class="bg-red-100 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-3 flex items-center justify-between">
                    <span>{{ session('error') }}</span>
                    <button type="button" class="text-red-500 hover:text-red-700" data-bs-dismiss="alert">&times;</button>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl">
                    <div class="px-6 py-4 border-b border-slate-200"><strong>Informasi Klaster</strong></div>
                    <div class="p-6">
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm">
                            <dt class="text-slate-500">Insiden</dt>
                            <dd>{{ $klaster->insiden?->kode_kejadian ?? '—' }}</dd>
                            <dt class="text-slate-500">Jenis Klaster</dt>
                            <dd>{{ $klaster->masterKlaster?->nama_klaster ?? '—' }}</dd>
                            <dt class="text-slate-500">Status</dt>
                            <dd><span class="px-2 py-1 rounded-full text-xs font-semibold bg-{{ $klaster->status_klaster === 'aktif' ? 'green-100 text-green-700' : 'slate-100 text-slate-700' }}">{{ ucfirst($klaster->status_klaster) }}</span></dd>
                            <dt class="text-slate-500">Prioritas</dt>
                            <dd>{{ ucfirst($klaster->prioritas) }}</dd>
                            <dt class="text-slate-500">Pembuat</dt>
                            <dd>{{ $klaster->pembuat?->profil?->nama_lengkap ?? '—' }}</dd>
                            <dt class="text-slate-500">Diaktifkan</dt>
                            <dd>{{ $klaster->waktu_aktivasi?->format('d/m/Y H:i') ?? '—' }}</dd>
                            <dt class="text-slate-500">Progres</dt>
                            <dd>
                                <div class="w-full bg-slate-200 rounded-full h-2">
                                    <div class="bg-primary-500 h-2 rounded-full" style="width: {{ $klaster->progres_persen ?? 0 }}%"></div>
                                </div>
                                <small class="text-slate-500">{{ $klaster->progres_persen ?? 0 }}%</small>
                            </dd>
                        </dl>
                    </div>
                </div>
                <div class="space-y-3">
                    <div class="bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl">
                        <div class="px-6 py-4 border-b border-slate-200"><strong>Target Cakupan</strong></div>
                        <div class="p-6">
                            <p class="text-sm text-slate-600 mb-0">{{ $klaster->target_cakupan ?? 'Tidak ada target cakupan yang ditentukan.' }}</p>
                        </div>
                    </div>
                    <div class="bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl">
                        <div class="px-6 py-4 border-b border-slate-200"><strong>Catatan</strong></div>
                        <div class="p-6">
                            <p class="text-sm text-slate-600 mb-0">{{ $klaster->catatan ?? 'Tidak ada catatan.' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            @if($klaster->status_klaster === 'aktif')
            <div class="bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl mt-3">
                <div class="px-6 py-4 border-b border-slate-200"><strong>Update Progres & Tutup Klaster</strong></div>
                <div class="p-6 space-y-3">
                    <form method="POST" action="{{ route('klaster.progress', $klaster) }}" class="flex gap-2 items-end">
                        @csrf
                        <div>
                            <input type="number" name="progres_persen" min="0" max="100" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300" placeholder="Progres %" required>
                        </div>
                        <div>
                            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-semibold hover:bg-indigo-700 transition-colors">Update Progres</button>
                        </div>
                    </form>
                    <form method="POST" action="{{ route('klaster.complete', $klaster) }}" onsubmit="return confirm('Tutup klaster ini?')" class="flex gap-2 items-end">
                        @csrf
                        <div>
                            <input type="text" name="catatan" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300" placeholder="Catatan penutupan (opsional)">
                        </div>
                        <div>
                            <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded-xl text-sm font-semibold hover:bg-red-600 transition-colors">Tutup Klaster</button>
                        </div>
                    </form>
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
