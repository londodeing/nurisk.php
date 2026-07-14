<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-slate-800">Distribusi Bantuan</h2>
            <a href="{{ route('posaju.index') }}"
                class="px-3 py-1.5 text-xs border border-slate-300 text-slate-600 rounded-xl hover:bg-slate-100 transition-colors flex items-center gap-2">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl">
                <div class="p-6">
                    <div class="text-center py-8">
                        <div class="w-24 h-24 mx-auto mb-4 bg-indigo-100 rounded-full flex items-center justify-center">
                            <i class="bi bi-box-seam text-indigo-600 text-3xl"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-slate-800 mb-2">Belum Ada Distribusi</h3>
                        <p class="text-slate-500 mb-6">Mulai rencanakan distribusi bantuan dari pos aju ini</p>
                        <a href="{{ route('insiden.posaju.distribusi.create', [$insiden, $posaju]) }}"
                            class="px-6 py-3 bg-indigo-600 text-white rounded-xl font-semibold hover:bg-indigo-700 transition-colors inline-flex items-center gap-2">
                            <i class="bi bi-plus-lg"></i> Buat Distribusi Baru
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>