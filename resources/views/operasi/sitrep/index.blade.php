<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-slate-800">Situation Report (Sitrep)</h2>
            <a href="{{ route('sitrep.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-semibold hover:bg-indigo-700 transition-colors flex items-center gap-2">
                <i class="bi bi-plus-lg"></i> Sitrep Baru
            </a>
        </div>
    </x-slot>

    <div class="bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">No. Sitrep</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Insiden</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Waktu</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Pembuat</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($sitreps as $s)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-4 py-3 text-sm font-medium text-slate-800">{{ $s->nomor_sitrep ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600">{{ $s->insiden?->kode_kejadian }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600">{{ $s->waktu_sitrep?->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600">{{ $s->pembuat?->profil?->nama_lengkap ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <a href="{{ route('sitrep.show', $s) }}" class="inline-flex items-center px-3 py-1.5 bg-slate-100 text-slate-700 rounded-lg text-xs font-semibold hover:bg-slate-200 transition-colors">
                                <i class="bi bi-eye mr-1"></i> Lihat
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-sm text-slate-500">Belum ada sitrep</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-slate-100">
            {{ $sitreps->links() }}
        </div>
    </div>
</x-app-layout>
