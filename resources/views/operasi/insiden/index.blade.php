<x-app-layout>
    <x-slot name="header">Insiden Operasional</x-slot>
    <x-slot name="breadcrumb"><a href="{{ route('dashboard.pwnu') }}" class="text-gray-500 hover:text-gray-700">Home</a> <span class="text-gray-400 mx-1">/</span> Insiden</x-slot>
    <x-slot name="actions">
        @can('create', App\Models\OperasiInsiden::class)
        <a href="{{ route('insiden.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700">
            <i class="bi bi-plus-lg"></i> Catat Insiden
        </a>
        @endcan
    </x-slot>

    <div class="space-y-4">
        <form method="GET" action="{{ route('insiden.index') }}" class="bg-white rounded-xl border border-gray-200 p-4 flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                <select name="status" class="rounded-lg border-gray-300 text-sm focus:border-green-500 focus:ring-primary-500">
                    <option value="">Semua Status</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="terverifikasi" {{ request('status') === 'terverifikasi' ? 'selected' : '' }}>Terverifikasi</option>
                    <option value="respon" {{ request('status') === 'respon' ? 'selected' : '' }}>Respon</option>
                    <option value="pemulihan" {{ request('status') === 'pemulihan' ? 'selected' : '' }}>Pemulihan</option>
                    <option value="selesai" {{ request('status') === 'selesai' ? 'selected' : '' }}>Selesai</option>
                    <option value="dibatalkan" {{ request('status') === 'dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Prioritas</label>
                <select name="prioritas" class="rounded-lg border-gray-300 text-sm focus:border-green-500 focus:ring-primary-500">
                    <option value="">Semua Prioritas</option>
                    <option value="rendah" {{ request('prioritas') === 'rendah' ? 'selected' : '' }}>Rendah</option>
                    <option value="sedang" {{ request('prioritas') === 'sedang' ? 'selected' : '' }}>Sedang</option>
                    <option value="tinggi" {{ request('prioritas') === 'tinggi' ? 'selected' : '' }}>Tinggi</option>
                    <option value="kritis" {{ request('prioritas') === 'kritis' ? 'selected' : '' }}>Kritis</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Jenis Bencana</label>
                <select name="id_jenis_bencana" class="rounded-lg border-gray-300 text-sm focus:border-green-500 focus:ring-primary-500">
                    <option value="">Semua Jenis</option>
                    @foreach($jenisBencana as $jb)
                    <option value="{{ $jb->id_jenis_bencana }}" {{ request('id_jenis_bencana') == $jb->id_jenis_bencana ? 'selected' : '' }}>{{ $jb->nama_bencana }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-gray-800 text-white text-sm rounded-lg hover:bg-gray-700">Filter</button>
            <a href="{{ route('insiden.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Reset</a>
        </form>

        <x-data-table :empty="$insideni->isEmpty()" emptyMessage="Belum ada insiden tercatat." emptyIcon="📋">
            <x-slot name="head">
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Kode</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Status</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Prioritas</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Jenis Bencana</th>
                @can('viewAny', App\Models\OrganisasiPcnu::class)
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">PCNU</th>
                @endcan
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Waktu Mulai</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Aksi</th>
            </x-slot>
            <x-slot name="body">
                @foreach($insideni as $insiden)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-3 text-sm font-mono font-semibold text-gray-900">
                        {{ $insiden->kode_kejadian }}
                        @if($insiden->is_locked)
                        <span title="Terkunci" class="ml-1">🔒</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        <x-badge-status :status="$insiden->status_insiden" map="insiden" />
                    </td>
                    <td class="px-4 py-3 text-center">
                        <x-badge-status :status="$insiden->prioritas" map="prioritas" />
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ $insiden->jenisBencana?->nama_bencana ?? '-' }}</td>
                    @can('viewAny', App\Models\OrganisasiPcnu::class)
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $insiden->pcnu?->nama_pcnu ?? '-' }}</td>
                    @endcan
                    <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">
                        {{ $insiden->waktu_mulai ? Carbon\Carbon::parse($insiden->waktu_mulai)->locale('id')->isoFormat('D MMM Y') : '-' }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        <a href="{{ route('insiden.show', $insiden) }}" class="inline-flex items-center gap-1 text-sm text-blue-600 hover:text-blue-800 font-medium">
                            <i class="bi bi-eye"></i> Lihat Detail
                        </a>
                    </td>
                </tr>
                @endforeach
            </x-slot>
            <x-slot name="footer">
                {{ $insideni->links() }}
            </x-slot>
        </x-data-table>
    </div>
</x-app-layout>
