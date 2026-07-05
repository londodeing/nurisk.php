<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Penugasan — {{ $penugasan->pengguna?->profil?->nama_lengkap ?? 'Personel' }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <form method="POST" action="{{ route('insiden.penugasan.update', [$insiden, $penugasan]) }}">
                    @csrf @method('PUT')

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Personel</label>
                        <p class="text-sm text-gray-800 font-medium">
                            {{ $penugasan->pengguna?->profil?->nama_lengkap ?? $penugasan->pengguna?->no_hp ?? '-' }}
                        </p>
                        <p class="text-xs text-gray-400">Personel tidak bisa diubah. Hapus dan buat ulang jika perlu.</p>
                    </div>

                    <div class="mb-6">
                        <label for="peran_otoritas" class="block text-sm font-medium text-gray-700 mb-1">Peran <span class="text-red-500">*</span></label>
                        <select name="peran_otoritas" id="peran_otoritas" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="komandan_insiden" {{ $penugasan->peran_otoritas == 'komandan_insiden' ? 'selected' : '' }}>Komandan Insiden</option>
                            <option value="trc" {{ $penugasan->peran_otoritas == 'trc' ? 'selected' : '' }}>TRC</option>
                            <option value="relawan" {{ $penugasan->peran_otoritas == 'relawan' ? 'selected' : '' }}>Relawan</option>
                            <option value="medis" {{ $penugasan->peran_otoritas == 'medis' ? 'selected' : '' }}>Medis</option>
                            <option value="logistik" {{ $penugasan->peran_otoritas == 'logistik' ? 'selected' : '' }}>Logistik</option>
                            <option value="operator" {{ $penugasan->peran_otoritas == 'operator' ? 'selected' : '' }}>Operator</option>
                        </select>
                        @error('peran_otoritas') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-6">
                        <label for="catatan" class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                        <textarea name="catatan" id="catatan" rows="3" maxlength="500"
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('catatan', $penugasan->catatan) }}</textarea>
                        @error('catatan') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                        <a href="{{ route('insiden.penugasan.show', [$insiden, $penugasan]) }}" class="text-sm text-gray-500 hover:text-gray-700">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                        <button type="submit" class="px-6 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <i class="bi bi-check-lg"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
