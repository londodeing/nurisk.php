<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Penugasan Personel — {{ $insiden->kode_kejadian }}
            </h2>
            @can('create', [App\Models\OperasiPenugasan::class, $insiden])
                @if(!$insiden->is_locked)
                <a href="{{ route('insiden.penugasan.create', $insiden) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
                    <i class="bi bi-plus-lg"></i> Tugaskan Personel
                </a>
                @endif
            @endcan
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-4">
                    <a href="{{ route('insiden.show', $insiden) }}" class="text-sm text-gray-500 hover:text-gray-700">
                        <i class="bi bi-arrow-left"></i> Kembali ke Insiden
                    </a>
                </div>

                @if($penugasans->count())
                <x-data-table>
                    <x-slot name="head">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Nama Personel</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Peran</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Waktu Mulai</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Waktu Selesai</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Aksi</th>
                    </x-slot>
                    <x-slot name="body">
                        @foreach($penugasans as $p)
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-700 font-medium">
                                {{ $p->pengguna?->profil?->nama_lengkap ?? $p->pengguna?->no_hp ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-blue-100 text-blue-700">
                                    {{ ucfirst(str_replace('_', ' ', $p->peran_otoritas)) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                @php
                                    $statusColors = [
                                        'draft' => 'bg-gray-100 text-gray-600',
                                        'assigned' => 'bg-yellow-100 text-yellow-700',
                                        'notified' => 'bg-blue-100 text-blue-700',
                                        'accepted' => 'bg-teal-100 text-teal-700',
                                        'on_route' => 'bg-indigo-100 text-indigo-700',
                                        'on_site' => 'bg-purple-100 text-purple-700',
                                        'completed' => 'bg-green-100 text-green-700',
                                        'cancelled' => 'bg-red-100 text-red-700',
                                        'rejected' => 'bg-orange-100 text-orange-700',
                                        'aktif' => 'bg-green-100 text-green-700',
                                        'selesai' => 'bg-gray-100 text-gray-600',
                                        'dibatalkan' => 'bg-red-100 text-red-700',
                                    ];
                                    $color = $statusColors[$p->status_penugasan] ?? 'bg-gray-100 text-gray-600';
                                @endphp
                                <span class="px-2 py-0.5 text-xs font-medium rounded-full {{ $color }}">
                                    {{ ucfirst(str_replace('_', ' ', $p->status_penugasan)) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                {{ $p->waktu_mulai ? Carbon\Carbon::parse($p->waktu_mulai)->locale('id')->isoFormat('D MMM YYYY, HH:mm') : '-' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                {{ $p->waktu_selesai ? Carbon\Carbon::parse($p->waktu_selesai)->locale('id')->isoFormat('D MMM YYYY, HH:mm') : '-' }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('insiden.penugasan.show', [$insiden, $p]) }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                                    Detail
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </x-slot>
                </x-data-table>
                <div class="px-5 py-3">
                    {{ $penugasans->links() }}
                </div>
                @else
                <div class="p-12 text-center">
                    <p class="text-gray-400 text-lg mb-2">Belum ada penugasan personel.</p>
                    @can('create', [App\Models\OperasiPenugasan::class, $insiden])
                        @if(!$insiden->is_locked)
                        <a href="{{ route('insiden.penugasan.create', $insiden) }}" class="text-indigo-600 hover:text-indigo-800 font-medium">
                            Tugaskan personel sekarang
                        </a>
                        @endif
                    @endcan
                </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
