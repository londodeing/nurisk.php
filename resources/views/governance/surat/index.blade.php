<x-app-layout>
    <x-slot name="header">Surat Menyurat</x-slot>
    <x-slot name="breadcrumb"><a href="{{ route('dashboard.pwnu') }}" class="text-gray-500 hover:text-gray-700">Home</a> <span class="text-gray-400 mx-1">/</span> Surat</x-slot>
    <x-slot name="actions">
        @can('create', App\Models\DokumenSuratUtama::class)
        <a href="{{ route('surat.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700">
            <i class="bi bi-plus-lg"></i> Buat Surat
        </a>
        @endcan
    </x-slot>

    <x-data-table :empty="$suratList->isEmpty()" emptyMessage="Belum ada surat." emptyIcon="📄">
        <x-slot name="head">
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Nomor Surat</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Jenis</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Perihal</th>
            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Status</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Tanggal</th>
            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Aksi</th>
        </x-slot>
        <x-slot name="body">
            @foreach($suratList as $surat)
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-4 py-3 text-sm font-mono font-medium text-gray-900">{{ $surat->nomor_surat ?? '-' }}</td>
                <td class="px-4 py-3 text-sm text-gray-700">{{ $surat->jenisSurat?->nama_jenis ?? '-' }}</td>
                <td class="px-4 py-3 text-sm text-gray-700 max-w-[200px] truncate">{{ $surat->perihal ?? '-' }}</td>
                <td class="px-4 py-3 text-center">
                    <x-badge-status :status="$surat->status_surat" map="surat" />
                </td>
                <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">
                    {{ $surat->dibuat_pada ? \Carbon\Carbon::parse($surat->dibuat_pada)->locale('id')->isoFormat('D MMM YYYY') : '-' }}
                </td>
                <td class="px-4 py-3 text-center">
                    <div class="flex items-center justify-center gap-2">
                        <a href="{{ route('surat.show', $surat) }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium">Lihat</a>
                        @can('update', $surat)
                            @if($surat->status_surat === 'draft')
                            <a href="{{ route('surat.edit', $surat) }}" class="text-sm text-yellow-600 hover:text-yellow-800 font-medium">Edit</a>
                            @endif
                        @endcan
                        @if($surat->file_pdf_path)
                        <a href="{{ route('surat.pdf', $surat) }}" class="text-sm text-gray-600 hover:text-gray-800 font-medium">
                            <i class="bi bi-download"></i> PDF
                        </a>
                        @endif
                    </div>
                </td>
            </tr>
            @endforeach
        </x-slot>
        <x-slot name="footer">
            {{ $suratList->links() }}
        </x-slot>
    </x-data-table>
</x-app-layout>
