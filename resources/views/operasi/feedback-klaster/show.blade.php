<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-slate-800">Detail Feedback Klaster</h2>
            <a href="{{ route('insiden.feedback-klaster.index', $insiden) }}" class="px-3 py-1.5 text-xs border border-slate-300 text-slate-600 rounded-xl hover:bg-slate-100 transition-colors flex items-center gap-2"><i class="bi bi-arrow-left"></i> Kembali</a>
        </div>
    </x-slot>
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl">
                <div class="p-6 border-b border-slate-200 flex items-center justify-between">
                    <strong>Feedback Klaster</strong>
                    <span class="px-2 py-1 rounded-full text-xs font-semibold
                        @switch($feedback->status_feedback)
                            @case('draft')     bg-yellow-100 text-yellow-700 @break
                            @case('final')     bg-green-100 text-green-700 @break
                            @default         bg-slate-100 text-slate-700
                        @endswitch
                    ">
                        {{ ucfirst($feedback->status_feedback) }}
                    </span>
                </div>

                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">Klaster</label>
                            <div class="text-sm font-medium">{{ $feedback->klasterOperasi?->masterKlaster?->nama_klaster ?? '—' }}</div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">Insiden</label>
                            <div class="text-sm font-medium">{{ $insiden->kode_kejadian }}</div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">Koordinator</label>
                            <div class="text-sm">{{ $feedback->pengguna?->profil?->nama_lengkap ?? '—' }}</div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">Dikunci Pada</label>
                            <div class="text-sm">{{ $feedback->dikunci_pada?->format('d/m/Y H:i') ?? '—' }}</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 bg-slate-50 rounded-xl">
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">Kecukupan Sumberdaya</label>
                            @php
                                $colors = [
                                    'kurang' => 'bg-orange-100 text-orange-700',
                                    'cukup' => 'bg-green-100 text-green-700',
                                    'berlebih' => 'bg-blue-100 text-blue-700',
                                ];
                            @endphp
                            <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $colors[$feedback->kecukupan_sumberdaya] ?? 'bg-slate-100 text-slate-700' }}">
                                {{ ucfirst($feedback->kecukupan_sumberdaya) }}
                            </span>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">Kualitas Layanan</label>
                            @php
                                $qColors = [
                                    'baik' => 'bg-green-100 text-green-700',
                                    'sedang' => 'bg-yellow-100 text-yellow-700',
                                    'buruk' => 'bg-red-100 text-red-700',
                                ];
                            @endphp
                            <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $qColors[$feedback->kualitas_layanan] ?? 'bg-slate-100 text-slate-700' }}">
                                {{ ucfirst($feedback->kualitas_layanan) }}
                            </span>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">Tepat Waktu</label>
                            <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $feedback->tepat_waktu ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ $feedback->tepat_waktu ? 'Ya' : 'Tidak' }}
                            </span>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">Tepat Sasaran</label>
                            <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $feedback->tepat_sasaran ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ $feedback->tepat_sasaran ? 'Ya' : 'Tidak' }}
                            </span>
                        </div>
                    </div>

                    @if($feedback->kendala)
                    <div class="border-t border-slate-200 pt-4">
                        <h4 class="text-sm font-semibold text-slate-800 mb-2">Kendala</h4>
                        <div class="text-sm text-slate-600 whitespace-pre-wrap">{{ $feedback->kendala }}</div>
                    </div>
                    @endif

                    @if($feedback->rekomendasi)
                    <div class="border-t border-slate-200 pt-4">
                        <h4 class="text-sm font-semibold text-slate-800 mb-2">Rekomendasi</h4>
                        <div class="text-sm text-slate-600 whitespace-pre-wrap">{{ $feedback->rekomendasi }}</div>
                    </div>
                    @endif

                    @if($feedback->gap_terdeteksi && count($feedback->gap_terdeteksi) > 0)
                    <div class="border-t border-slate-200 pt-4">
                        <h4 class="text-sm font-semibold text-slate-800 mb-3">Gap Terdeteksi ({{ count($feedback->gap_terdeteksi) }})</h4>
                        <div class="space-y-3">
                            @foreach($feedback->gap_terdeteksi as $index => $gap)
                            <div class="border border-slate-200 rounded-xl p-4 bg-slate-50">
                                <div class="flex items-start justify-between mb-2">
                                    <div class="flex items-center gap-2">
                                        @php
                                            $gapColors = [
                                                'sumberdaya' => 'bg-green-100 text-green-700',
                                                'personel' => 'bg-blue-100 text-blue-700',
                                                'logistik' => 'bg-orange-100 text-orange-700',
                                                'informasi' => 'bg-purple-100 text-purple-700',
                                                'koordinasi' => 'bg-pink-100 text-pink-700',
                                            ];
                                        @endphp
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $gapColors[$gap['jenis_gap']] ?? 'bg-slate-100 text-slate-700' }}">
                                            {{ ucfirst($gap['jenis_gap']) }}
                                        </span>
                                        @php
                                            $pColors = [
                                                'rendah' => 'bg-slate-100 text-slate-700',
                                                'sedang' => 'bg-blue-100 text-blue-700',
                                                'tinggi' => 'bg-orange-100 text-orange-700',
                                                'kritis' => 'bg-red-100 text-red-700',
                                            ];
                                        @endphp
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $pColors[$gap['prioritas']] ?? 'bg-slate-100 text-slate-700' }}">
                                            {{ ucfirst($gap['prioritas']) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="text-sm text-slate-600 mb-2">{{ $gap['deskripsi'] }}</div>
                                <div class="grid grid-cols-2 gap-2 text-xs text-slate-500">
                                    <div>
                                        <span class="font-medium text-slate-700">Selisih:</span>
                                        {{ $gap['selisih_jumlah'] ?? '-' }} {{ $gap['satuan'] ?? '' }}
                                    </div>
                                    <div>
                                        <span class="font-medium text-slate-700">Prioritas:</span>
                                        <span class="px-2 py-0.5 rounded text-xs font-semibold
                                            @switch($gap['prioritas'])
                                                @case('rendah') bg-slate-100 text-slate-700 @break
                                                @case('sedang') bg-blue-100 text-blue-700 @break
                                                @case('tinggi') bg-orange-100 text-orange-700 @break
                                                @case('kritis') bg-red-100 text-red-700 @break
                                                @default bg-slate-100 text-slate-700
                                            @endswitch
                                        ">
                                            {{ ucfirst($gap['prioritas']) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if($feedback->gapKebutuhan && $feedback->gapKebutuhan->count() > 0)
                    <div class="border-t border-slate-200 pt-4">
                        <h4 class="text-sm font-semibold text-slate-800 mb-3">Gap Kebutuhan Terbuat ({{ $feedback->gapKebutuhan->count() }})</h4>
                        <div class="space-y-2">
                            @foreach($feedback->gapKebutuhan as $gap)
                            <div class="border border-slate-200 rounded-xl p-3 bg-white">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        @php
                                            $gColors = [
                                                'sumberdaya' => 'bg-green-100 text-green-700',
                                                'personel' => 'bg-blue-100 text-blue-700',
                                                'logistik' => 'bg-orange-100 text-orange-700',
                                                'informasi' => 'bg-purple-100 text-purple-700',
                                                'koordinasi' => 'bg-pink-100 text-pink-700',
                                            ];
                                        @endphp
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $gColors[$gap->jenis_gap] ?? 'bg-slate-100 text-slate-700' }}">
                                            {{ ucfirst($gap->jenis_gap) }}
                                        </span>
                                        @php
                                            $pColors = [
                                                'rendah' => 'bg-slate-100 text-slate-700',
                                                'sedang' => 'bg-blue-100 text-blue-700',
                                                'tinggi' => 'bg-orange-100 text-orange-700',
                                                'kritis' => 'bg-red-100 text-red-700',
                                            ];
                                        @endphp
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $pColors[$gap->prioritas] ?? 'bg-slate-100 text-slate-700' }}">
                                            {{ ucfirst($gap->prioritas) }}
                                        </span>
                                    </div>
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold
                                        @switch($gap->status_gap)
                                            @case('dibuka') bg-yellow-100 text-yellow-700 @break
                                            @case('diprioritaskan') bg-blue-100 text-blue-700 @break
                                            @case('dikerjakan') bg-purple-100 text-purple-700 @break
                                            @case('terselesaikan') bg-green-100 text-green-700 @break
                                            @case('ditutup') bg-slate-100 text-slate-700 @break
                                            @default bg-slate-100 text-slate-700
                                        @endswitch
                                    ">
                                        {{ ucfirst($gap->status_gap) }}
                                    </span>
                                </div>
                                <div class="mt-2 text-sm text-slate-600">{{ $gap->deskripsi_gap }}</div>
                                @if($gap->selisih_jumlah)
                                <div class="mt-1 text-xs text-slate-500">Selisih: {{ $gap->selisih_jumlah }} {{ $gap->satuan ?? '' }}</div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <div class="text-xs text-slate-400 mt-4">
                        Dikunci pada: {{ $feedback->dikunci_pada?->format('d/m/Y H:i') }} | Oleh: {{ $feedback->pengguna?->profil?->nama_lengkap ?? '—' }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>