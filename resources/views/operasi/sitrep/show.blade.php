<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-slate-800">Sitrep: {{ $sitrep->nomor_sitrep ?? '—' }}</h2>
            <div class="flex gap-2">
                <a href="{{ route('sitrep.pdf', $sitrep) }}" class="px-3 py-1.5 text-xs bg-red-500 text-white rounded-xl hover:bg-red-600 transition-colors flex items-center gap-2" target="_blank"><i class="bi bi-file-pdf"></i> PDF</a>
                <a href="{{ route('sitrep.index') }}" class="px-3 py-1.5 text-xs border border-slate-300 text-slate-600 rounded-xl hover:bg-slate-100 transition-colors flex items-center gap-2"><i class="bi bi-arrow-left"></i> Kembali</a>
            </div>
        </div>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-3">
                <div class="bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl">
                    <div class="px-6 py-4 border-b border-slate-200"><strong>Informasi Sitrep</strong></div>
                    <div class="p-6">
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm">
                            <dt class="text-slate-500">Nomor</dt>
                            <dd>{{ $sitrep->nomor_sitrep ?? '—' }}</dd>
                            <dt class="text-slate-500">Insiden</dt>
                            <dd>{{ $sitrep->insiden?->kode_kejadian ?? '—' }}</dd>
                            <dt class="text-slate-500">Periode</dt>
                            <dd>{{ $sitrep->periode_sitrep ?? '—' }}</dd>
                            <dt class="text-slate-500">Waktu</dt>
                            <dd>{{ $sitrep->waktu_sitrep?->format('d/m/Y H:i') }}</dd>
                            <dt class="text-slate-500">Pembuat</dt>
                            <dd>{{ $sitrep->pembuat?->profil?->nama_lengkap ?? '—' }}</dd>
                            <dt class="text-slate-500">Jml Personel</dt>
                            <dd>{{ $sitrep->jumlah_personel ?? 0 }}</dd>
                            <dt class="text-slate-500">Klaster Aktif</dt>
                            <dd>{{ $sitrep->jumlah_klaster_aktif ?? 0 }}</dd>
                        </dl>
                    </div>
                </div>
                <div class="bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl">
                    <div class="px-6 py-4 border-b border-slate-200"><strong>Catatan Situasi</strong></div>
                    <div class="p-6">
                        <p class="text-sm text-slate-600 mb-0">{{ $sitrep->catatan ?? 'Tidak ada catatan.' }}</p>
                    </div>
                </div>
            </div>

            @if($sitrep->dampak)
            <div class="bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl mb-3">
                <div class="px-6 py-4 border-b border-slate-200"><strong>Dampak Bencana</strong></div>
                <div class="p-6">
                    <div class="grid grid-cols-2 sm:grid-cols-5 gap-2 text-center">
                        <div><span class="px-2 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700">Meninggal: {{ $sitrep->dampak->meninggal ?? 0 }}</span></div>
                        <div><span class="px-2 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700">Hilang: {{ $sitrep->dampak->hilang ?? 0 }}</span></div>
                        <div><span class="px-2 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">Luka Berat: {{ $sitrep->dampak->luka_berat ?? 0 }}</span></div>
                        <div><span class="px-2 py-1 rounded-full text-xs font-semibold bg-cyan-100 text-cyan-700">Luka Ringan: {{ $sitrep->dampak->luka_ringan ?? 0 }}</span></div>
                        <div><span class="px-2 py-1 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-700">Mengungsi: {{ $sitrep->dampak->mengungsi ?? 0 }}</span></div>
                    </div>
                </div>
            </div>
            @endif

            @if($sitrep->kebutuhan && $sitrep->kebutuhan->count() > 0)
            <div class="bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl mb-3">
                <div class="px-6 py-4 border-b border-slate-200"><strong>Kebutuhan Mendesak</strong></div>
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-50">
                                <tr><th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Kebutuhan</th><th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Jumlah</th><th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Satuan</th></tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                @foreach($sitrep->kebutuhan as $k)
                                <tr class="hover:bg-slate-50 transition-colors"><td class="px-4 py-2">{{ $k->nama_kebutuhan }}</td><td class="px-4 py-2">{{ $k->jumlah }}</td><td class="px-4 py-2">{{ $k->satuan }}</td></tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
