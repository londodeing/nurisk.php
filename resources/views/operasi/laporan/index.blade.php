<x-app-layout>
    <x-slot name="header">Laporan Kejadian</x-slot>
    <x-slot name="breadcrumb"><a href="{{ route('dashboard.pwnu') }}" class="text-gray-500 hover:text-gray-700">Home</a> <span class="text-gray-400 mx-1">/</span> Laporan Kejadian</x-slot>
    <x-slot name="actions">
        @can('create', App\Models\LaporanKejadian::class)
        <a href="{{ route('public.lapor') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700">
            <i class="bi bi-plus-lg"></i> Laporan Baru
        </a>
        @endcan
    </x-slot>

    <div class="space-y-4">
        <form method="GET" action="{{ route('dashboard.laporan.index') }}" class="bg-white rounded-xl border border-gray-200 p-4 flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                <select name="status" class="rounded-lg border-gray-300 text-sm focus:border-green-500 focus:ring-green-500">
                    <option value="">Semua Status</option>
                    <option value="menunggu" {{ request('status') === 'menunggu' ? 'selected' : '' }}>Menunggu</option>
                    <option value="ya" {{ request('status') === 'ya' ? 'selected' : '' }}>Valid</option>
                    <option value="tidak" {{ request('status') === 'tidak' ? 'selected' : '' }}>Tidak Valid</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Jenis Bencana</label>
                <select name="id_jenis_bencana" class="rounded-lg border-gray-300 text-sm focus:border-green-500 focus:ring-green-500">
                    <option value="">Semua Jenis</option>
                    @foreach($jenisBencanaList as $jb)
                    <option value="{{ $jb->id_jenis_bencana }}" {{ request('id_jenis_bencana') == $jb->id_jenis_bencana ? 'selected' : '' }}>{{ $jb->nama_bencana }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Dari Tanggal</label>
                <input type="date" name="dari" value="{{ request('dari') }}" class="rounded-lg border-gray-300 text-sm focus:border-green-500 focus:ring-green-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Sampai Tanggal</label>
                <input type="date" name="sampai" value="{{ request('sampai') }}" class="rounded-lg border-gray-300 text-sm focus:border-green-500 focus:ring-green-500">
            </div>
            <button type="submit" class="px-4 py-2 bg-gray-800 text-white text-sm rounded-lg hover:bg-gray-700">Filter</button>
            <a href="{{ route('dashboard.laporan.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Reset</a>
        </form>

        <x-data-table :empty="$laporanList->isEmpty()" emptyMessage="Belum ada laporan kejadian." emptyIcon="📋">
            <x-slot name="head">
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Tanggal</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Pelapor</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Jenis Bencana</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Lokasi</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Status</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Aksi</th>
            </x-slot>
            <x-slot name="body">
                @foreach($laporanList as $laporan)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">{{ Carbon\Carbon::parse($laporan->waktu_kejadian)->locale('id')->isoFormat('D MMM Y, HH:mm') }}</td>
                    <td class="px-4 py-3 text-sm text-gray-700">
                        <span class="font-medium">{{ $laporan->nama_pelapor }}</span>
                        @if($laporan->hp_pelapor)<br><span class="text-xs text-gray-400">{{ $laporan->hp_pelapor }}</span>@endif
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ $laporan->jenisBencana?->nama_bencana ?? '-' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-700 max-w-[200px] truncate">{{ $laporan->titik_kenal ?? '-' }}</td>
                    <td class="px-4 py-3 text-center">
                        <x-badge-status :status="$laporan->is_valid" map="laporan" />
                        @if($laporan->is_valid === 'tidak' && $laporan->alasan_tolak)
                        <div class="text-xs text-gray-400 mt-0.5">{{ ucfirst($laporan->alasan_tolak) }}</div>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <a href="{{ route('dashboard.laporan.show', $laporan) }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium">Lihat</a>
                            @can('validasi', $laporan)
                                @if($laporan->is_valid === 'menunggu')
                                <a href="{{ route('dashboard.laporan.show', $laporan) }}" class="text-sm text-green-600 hover:text-green-800 font-medium">Validasi</a>
                                @endif
                            @endcan
                        </div>
                    </td>
                </tr>
                @endforeach
            </x-slot>
            <x-slot name="footer">
                {{ $laporanList->links() }}
            </x-slot>
        </x-data-table>
    </div>
</x-app-layout>
