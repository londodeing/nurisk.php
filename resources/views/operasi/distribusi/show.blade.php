<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-slate-800">Detail Distribusi: {{ $distribusi->nama_barang }}</h2>
            <a href="{{ route('insiden.posaju.distribusi.index', [$insiden, $posaju]) }}"
                class="px-3 py-1.5 text-xs border border-slate-300 text-slate-600 rounded-xl hover:bg-slate-100 transition-colors flex items-center gap-2">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </x-slot>
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl">
                <div class="p-6 border-b border-slate-200 flex items-center justify-between">
                    <strong>Distribusi Bantuan</strong>
                    <span class="px-2 py-1 rounded-full text-xs font-semibold
                        @switch($distribusi->status_distribusi)
                            @case('direncanakan')     bg-yellow-100 text-yellow-700 @break
                            @case('didistribusikan')  bg-blue-100 text-blue-700 @break
                            @case('diterima')         bg-purple-100 text-purple-700 @break
                            @case('direview')         bg-green-100 text-green-700 @break
                            @default                  bg-slate-100 text-slate-700
                        @endswitch
                    ">
                        {{ ucfirst($distribusi->status_distribusi) }}
                    </span>
                </div>

                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">Barang</label>
                            <div class="text-sm font-medium">{{ $distribusi->nama_barang }}</div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">Katalog</label>
                            <div class="text-sm">{{ $distribusi->barangKatalog?->nama_barang_standar ?? 'Manual Entry' }}</div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">Jumlah</label>
                            <div class="text-sm font-medium">{{ number_format($distribusi->jumlah, 2) }} {{ $distribusi->satuan }}</div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">Klaster</label>
                            <div class="text-sm">{{ $distribusi->klasterOperasi?->masterKlaster?->nama_klaster ?? '—' }}</div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">Lokasi Tujuan</label>
                            <div class="text-sm">{{ $distribusi->lokasi_tujuan ?? '—' }}</div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">Penerima</label>
                            <div class="text-sm">{{ $distribusi->penerima ?? '—' }}</div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">Waktu Distribusi</label>
                            <div class="text-sm">{{ $distribusi->waktu_distribusi?->format('d/m/Y H:i') }}</div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">Dibuat Oleh</label>
                            <div class="text-sm">{{ $distribusi->pembuat?->profil?->nama_lengkap ?? '—' }}</div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">Dibuat Pada</label>
                            <div class="text-sm">{{ $distribusi->dibuat_pada?->format('d/m/Y H:i') }}</div>
                        </div>
                    </div>

                    @if($distribusi->penugasan)
                    <div class="border-t border-slate-200 pt-4">
                        <h4 class="text-sm font-semibold text-slate-700 mb-2">Petugas Pelaksana</h4>
                        <div class="text-sm">
                            <div>{{ $distribusi->penugasan->pengguna?->profil?->nama_lengkap ?? '—' }}</div>
                            <div class="text-slate-500">{{ $distribusi->penugasan->peran_otoritas }}</div>
                        </div>
                    </div>
                    @endif

                    @if($distribusi->feedback)
                    <div class="border-t border-slate-200 pt-4">
                        <h4 class="text-sm font-semibold text-slate-700 mb-2">Feedback (Terkunci)</h4>
                        <div class="grid grid-cols-2 gap-2 text-sm mb-2">
                            <div>
                                <span class="text-slate-500">Kecukupan:</span>
                                <span class="font-medium
                                    @switch($distribusi->feedback->kecukupan)
                                        @case('cukup')     text-green-600 @break
                                        @case('kurang')    text-orange-600 @break
                                        @case('berlebih')  text-blue-600 @break
                                    @endswitch
                                ">
                                    {{ ucfirst($distribusi->feedback->kecukupan) }}
                                </span>
                            </div>
                            <div>
                                <span class="text-slate-500">Kualitas:</span>
                                <span class="font-medium
                                    @switch($distribusi->feedback->kualitas)
                                        @case('baik')      text-green-600 @break
                                        @case('sedang')    text-yellow-600 @break
                                        @case('buruk')     text-red-600 @break
                                    @endswitch
                                ">
                                    {{ ucfirst($distribusi->feedback->kualitas) }}
                                </span>
                            </div>
                            <div>
                                <span class="text-slate-500">Tepat Waktu:</span>
                                <span class="font-medium {{ $distribusi->feedback->tepat_waktu ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $distribusi->feedback->tepat_waktu ? 'Ya' : 'Tidak' }}
                                </span>
                            </div>
                            <div>
                                <span class="text-slate-500">Tepat Sasaran:</span>
                                <span class="font-medium {{ $distribusi->feedback->tepat_sasaran ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $distribusi->feedback->tepat_sasaran ? 'Ya' : 'Tidak' }}
                                </span>
                            </div>
                        </div>
                        @if($distribusi->feedback->kendala)
                        <div class="text-sm text-slate-600 mb-1"><strong>Kendala:</strong> {{ $distribusi->feedback->kendala }}</div>
                        @endif
                        @if($distribusi->feedback->rekomendasi)
                        <div class="text-sm text-slate-600"><strong>Rekomendasi:</strong> {{ $distribusi->feedback->rekomendasi }}</div>
                        @endif
                        <div class="text-xs text-slate-400 mt-2">
                            Oleh: {{ $distribusi->feedback->pengguna?->profil?->nama_lengkap ?? '—' }} |
                            {{ $distribusi->feedback->dikunci_pada?->format('d/m/Y H:i') }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>