<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-slate-800">Klaster Operasi</h2>
        </div>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl">
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-50">
                                <tr><th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Klaster</th><th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Insiden</th><th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Status</th><th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Progress</th><th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Dibutuhkan</th></tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                @forelse($klasters as $k)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-4 py-2">{{ $k->masterKlaster?->nama_klaster ?? $k->id_master_klaster }}</td>
                                    <td class="px-4 py-2">{{ $k->insiden?->kode_kejadian }}</td>
                                    <td class="px-4 py-2"><span class="px-2 py-1 rounded-full text-xs font-semibold bg-{{ $k->status_klaster === 'aktif' ? 'green-100 text-green-700' : 'slate-100 text-slate-700' }}">{{ $k->status_klaster ?? 'aktif' }}</span></td>
                                    <td class="px-4 py-2">
                                        <div class="w-full bg-slate-200 rounded-full h-2">
                                            <div class="bg-indigo-500 h-2 rounded-full" style="width:{{ $k->progres_persen ?? 0 }}%"></div>
                                        </div>
                                        <small class="text-slate-500">{{ $k->progres_persen ?? 0 }}%</small>
                                    </td>
                                    <td class="px-4 py-2">{!! $k->dibutuhkan ? '<span class="text-green-600">Ya</span>' : '<span class="text-slate-500">Tidak</span>' !!}</td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="text-center py-3 text-slate-500">Belum ada klaster</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $klasters->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
