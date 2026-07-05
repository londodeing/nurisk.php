<x-app-layout>
    <x-slot name="header">Manajemen Logistik</x-slot>
    <x-slot name="breadcrumb"><a href="{{ route('dashboard.pwnu') }}" class="text-gray-500 hover:text-gray-700">Home</a> <span class="text-gray-400 mx-1">/</span> Logistik</x-slot>

    <div class="space-y-6">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <x-kpi-card title="Total Stok Tersedia" :value="$totalStok ?? 0" subtext="Semua gudang" icon="bi-box" color="blue" />
            <x-kpi-card title="Stok Kritis" :value="$stokKritis ?? 0" subtext="Perlu perhatian" icon="bi-exclamation-triangle" color="red" />
            <x-kpi-card title="Mutasi Hari Ini" :value="$totalMutasi ?? 0" subtext="Penerimaan & distribusi" icon="bi-arrow-left-right" color="green" />
        </div>

        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-700">Stok per Pos Aju</h3>
                <div class="flex items-center gap-2">
                    @can('create', App\Models\LogistikMutasi::class)
                    <a href="#" class="text-sm text-blue-600 hover:text-blue-800 font-medium">Penerimaan Bantuan</a>
                    <span class="text-gray-300">|</span>
                    <a href="#" class="text-sm text-blue-600 hover:text-blue-800 font-medium">Distribusi</a>
                    @endcan
                </div>
            </div>
            <x-data-table :empty="true" emptyMessage="Data stok per pos aju belum tersedia." emptyIcon="📦">
                <x-slot name="head">
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Pos Aju</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Stok Aman</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Stok Kritis</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Total Item</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Aksi</th>
                </x-slot>
            </x-data-table>
        </div>
    </div>
</x-app-layout>
