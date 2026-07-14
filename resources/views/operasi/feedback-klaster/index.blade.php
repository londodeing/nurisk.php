<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-slate-800">Feedback Klaster - {{ $insiden->kode_kejadian }}</h2>
            <a href="{{ route('insiden.feedback-klaster.create', $insiden) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-semibold hover:bg-indigo-700 transition-colors flex items-center gap-2"><i class="bi bi-plus-lg"></i> Feedback Baru</a>
        </div>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl">
                <div class="p-6 border-b border-slate-200">
                    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-2 mb-3">
                        <div>
                            <select name="status" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300">
                                <option value="">Semua Status</option>
                                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="final" {{ request('status') === 'final' ? 'selected' : '' }}>Terkunci</option>
                            </select>
                        </div>
                        <div>
                            <select name="klaster" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300">
                                <option value="">Semua Klaster</option>
                                @foreach($klasterList as $k)
                                <option value="{{ $k->id_klaster_operasi }}" {{ request('klaster') == $k->id_klaster_operasi ? 'selected' : '' }}>
                                    {{ $k->masterKlaster?->nama_klaster ?? '—' }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <button class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-semibold hover:bg-indigo-700 transition-colors" type="submit">Filter</button>
                        </div>
                        <div>
                            <a href="{{ route('insiden.feedback-klaster.index', $insiden) }}" class="px-4 py-2 bg-slate-100 text-slate-600 rounded-xl text-sm font-semibold hover:bg-slate-200 transition-colors w-full text-center">Reset</a>
                        </div>
                    </form>
                </div>

                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Klaster</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Pengisi</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Sumberdaya</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Kualitas</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Tepat Waktu</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Tepat Sasaran</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Gap</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Status</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Waktu</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                @forelse($feedbacks as $f)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-4 py-2">{{ $f->klasterOperasi?->masterKlaster?->nama_klaster ?? '—' }}</td>
                                    <td class="px-4 py-2">{{ $f->pengguna?->profil?->nama_lengkap ?? '—' }}</td>
                                    <td class="px-4 py-2">
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold
                                            @switch($f->kecukupan_sumberdaya)
                                                @case('cukup')     bg-green-100 text-green-700 @break
                                                @case('kurang')    bg-orange-100 text-orange-700 @break
                                                @case('berlebih')  bg-blue-100 text-blue-700 @break
                                                @default           bg-slate-100 text-slate-700
                                            @endswitch
                                        ">
                                            {{ ucfirst($f->kecukupan_sumberdaya) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2">
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold
                                            @switch($f->kualitas_layanan)
                                                @case('baik')      bg-green-100 text-green-700 @break
                                                @case('sedang')    bg-yellow-100 text-yellow-700 @break
                                                @case('buruk')     bg-red-100 text-red-700 @break
                                                @default           bg-slate-100 text-slate-700
                                            @endswitch
                                        ">
                                            {{ ucfirst($f->kualitas_layanan) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2">
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $f->tepat_waktu ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                            {{ $f->tepat_waktu ? 'Ya' : 'Tidak' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2">
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $f->tepat_sasaran ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                            {{ $f->tepat_sasaran ? 'Ya' : 'Tidak' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2">
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold
                                            @if($f->gapKebutuhan->count() > 0)
                                                bg-orange-100 text-orange-700
                                            @else
                                                bg-green-100 text-green-700
                                            @endif
                                        ">
                                            {{ $f->gapKebutuhan->count() }} Gap
                                        </span>
                                    </td>
                                    <td class="px-4 py-2">
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold
                                            @switch($f->status_feedback)
                                                @case('draft')     bg-yellow-100 text-yellow-700 @break
                                                @case('final')     bg-green-100 text-green-700 @break
                                                @default           bg-slate-100 text-slate-700
                                            @endswitch
                                        ">
                                            {{ ucfirst($f->status_feedback) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2">{{ $f->dikunci_pada?->format('d/m/Y H:i') ?? $f->dibuat_pada?->format('d/m/Y H:i') }}</td>
                                    <td class="px-4 py-2">
                                        <a href="{{ route('insiden.feedback-klaster.show', [$insiden, $f]) }}"
                                           class="px-2 py-1 text-xs border border-indigo-300 text-indigo-600 rounded-xl hover:bg-indigo-50 transition-colors">
                                            Detail
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="10" class="text-center py-4 text-slate-500">Belum ada feedback klaster</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $feedbacks->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>