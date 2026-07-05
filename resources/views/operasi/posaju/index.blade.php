<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-slate-800">Pos Aju</h2>
            @can('create', App\Models\OperasiPosaju::class)
            <a href="{{ route('posaju.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-semibold hover:bg-indigo-700 transition-colors flex items-center gap-2"><i class="bi bi-plus-lg"></i> Pos Aju Baru</a>
            @endcan
        </div>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl">
                <div class="p-6">
                    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-2 mb-3">
                        <div>
                            <select name="status_alur" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300">
                                <option value="">Semua Status</option>
                                <option value="direncanakan" {{ request('status_alur') === 'direncanakan' ? 'selected' : '' }}>Direncanakan</option>
                                <option value="aktif" {{ request('status_alur') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                                <option value="ditutup" {{ request('status_alur') === 'ditutup' ? 'selected' : '' }}>Ditutup</option>
                            </select>
                        </div>
                        <div>
                            <button class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-semibold hover:bg-indigo-700 transition-colors" type="submit">Filter</button>
                        </div>
                    </form>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Nama Pos</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Insiden</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">PJ</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Status</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Aktif</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Ditutup</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                @forelse($posajus as $p)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-4 py-2">{{ $p->nama_posaju }}</td>
                                    <td class="px-4 py-2">{{ $p->insiden?->kode_kejadian }}</td>
                                    <td class="px-4 py-2">{{ $p->pj?->profil?->nama_lengkap ?? '—' }}</td>
                                    <td class="px-4 py-2"><span class="px-2 py-1 rounded-full text-xs font-semibold bg-{{ $p->status_alur === 'aktif' ? 'green-100 text-green-700' : ($p->status_alur === 'ditutup' ? 'slate-100 text-slate-700' : 'yellow-100 text-yellow-700') }}">{{ $p->labelStatus() }}</span></td>
                                    <td class="px-4 py-2">{{ $p->waktu_diaktifkan?->format('d/m/Y') ?? '—' }}</td>
                                    <td class="px-4 py-2">{{ $p->waktu_ditutup?->format('d/m/Y') ?? '—' }}</td>
                                    <td class="px-4 py-2">
                                        <a href="{{ route('posaju.show', $p) }}" class="px-3 py-1.5 text-xs border border-cyan-300 text-cyan-600 rounded-xl hover:bg-cyan-50 transition-colors"><i class="bi bi-eye"></i></a>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="7" class="text-center py-3 text-slate-500">Belum ada pos aju</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $posajus->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
