<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-slate-800">Jurnal Operasi</h2>
        </div>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl">
                <div class="p-6">
                    <div class="space-y-4">
                        @forelse($jurnals as $j)
                        <div class="flex gap-3">
                            <div class="text-center shrink-0" style="min-width:60px;">
                                <div class="text-xs text-slate-500">{{ $j->dibuat_pada?->format('H:i') }}</div>
                                <div class="text-xs text-slate-500">{{ $j->dibuat_pada?->format('d/m') }}</div>
                            </div>
                            <div class="flex-1">
                                <strong>{{ $j->judul_event }}</strong>
                                <p class="mb-0 text-slate-500 text-sm">{{ $j->deskripsi_event }}</p>
                                <small class="text-slate-500">— {{ $j->pengguna?->profil?->nama_lengkap ?? 'System' }}
                                    @if($j->insiden) | {{ $j->insiden?->kode_kejadian }} @endif
                                    | <span class="px-2 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">{{ $j->kategori_event }}</span>
                                </small>
                            </div>
                        </div>
                        @empty
                        <p class="text-center text-slate-500 py-3">Belum ada jurnal</p>
                        @endforelse
                    </div>
                    {{ $jurnals->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
