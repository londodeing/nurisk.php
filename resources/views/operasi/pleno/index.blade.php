<x-app-layout>
    <x-slot name="header">Daftar Rapat Pleno</x-slot>
    <x-slot name="breadcrumb">
        <a href="{{ route('dashboard.pwnu') }}" class="text-gray-500 hover:text-gray-700">Home</a>
        <span class="text-gray-400 mx-1">/</span>
        <a href="{{ route('insiden.index') }}" class="text-gray-500 hover:text-gray-700">Operasi Insiden</a>
        <span class="text-gray-400 mx-1">/</span>
        <a href="{{ route('insiden.show', $insiden) }}" class="text-gray-500 hover:text-gray-700">{{ $insiden->kode_kejadian }}</a>
        <span class="text-gray-400 mx-1">/</span>
        Pleno
    </x-slot>

    <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h2 class="text-lg font-semibold text-gray-800">Daftar Pleno - {{ $insiden->kode_kejadian }}</h2>
            <p class="text-sm text-gray-500">Semua riwayat rapat pleno terkait operasi ini.</p>
        </div>
        @can('create', [App\Models\OperasiPleno::class, $insiden])
        <a href="{{ route('insiden.pleno.create', $insiden) }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-lg font-semibold text-xs text-white hover:bg-green-700">
            <i class="bi bi-plus-lg mr-2"></i> Buat Pleno Baru
        </a>
        @endcan
    </div>

    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. Pleno</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pimpinan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($plenoList as $pleno)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $pleno->nomor_pleno ?? 'Belum ada nomor' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $pleno->waktu_pleno->format('d M Y, H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ str_replace('_', ' ', Str::title($pleno->jenis_pleno)) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $pleno->pimpinan?->profil?->nama_lengkap ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $pleno->warnaBadgeStatus() }}">
                                {{ $pleno->labelStatus() }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('insiden.pleno.show', [$insiden, $pleno]) }}" class="text-blue-600 hover:text-blue-900">Lihat Detail</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500 text-sm">
                            Belum ada rapat pleno yang tercatat untuk insiden ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
