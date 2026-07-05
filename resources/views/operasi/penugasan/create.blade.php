<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Tugaskan Personel — {{ $insiden->kode_kejadian }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <form method="POST" action="{{ route('insiden.penugasan.store', $insiden) }}">
                    @csrf

                    <div class="mb-6">
                        <label for="id_pengguna" class="block text-sm font-medium text-gray-700 mb-1">Personel <span class="text-red-500">*</span></label>
                        <select name="id_pengguna" id="id_pengguna" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="">— Pilih Personel —</option>
                            @foreach($authUsers as $user)
                            <option value="{{ $user->id_pengguna }}" {{ old('id_pengguna') == $user->id_pengguna ? 'selected' : '' }}>
                                {{ $user->profil?->nama_lengkap ?? 'Tanpa Nama' }} ({{ $user->no_hp ?? 'No HP -' }})
                                @if($user->roles->isNotEmpty())
                                    @foreach($user->roles as $role)
                                        - {{ $role->nama_peran }}
                                    @endforeach
                                @endif
                            </option>
                            @endforeach
                        </select>
                        @error('id_pengguna') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-6">
                        <label for="peran_otoritas" class="block text-sm font-medium text-gray-700 mb-1">Peran <span class="text-red-500">*</span></label>
                        <select name="peran_otoritas" id="peran_otoritas" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="">— Pilih Peran —</option>
                            <option value="komandan_insiden" {{ old('peran_otoritas') == 'komandan_insiden' ? 'selected' : '' }}>Komandan Insiden</option>
                            <option value="trc" {{ old('peran_otoritas') == 'trc' ? 'selected' : '' }}>TRC</option>
                            <option value="relawan" {{ old('peran_otoritas') == 'relawan' ? 'selected' : '' }}>Relawan</option>
                            <option value="medis" {{ old('peran_otoritas') == 'medis' ? 'selected' : '' }}>Medis</option>
                            <option value="logistik" {{ old('peran_otoritas') == 'logistik' ? 'selected' : '' }}>Logistik</option>
                            <option value="operator" {{ old('peran_otoritas') == 'operator' ? 'selected' : '' }}>Operator</option>
                        </select>
                        @error('peran_otoritas') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-6">
                        <label for="catatan" class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                        <textarea name="catatan" id="catatan" rows="3" maxlength="500"
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('catatan') }}</textarea>
                        @error('catatan') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                        <a href="{{ route('insiden.penugasan.index', $insiden) }}" class="text-sm text-gray-500 hover:text-gray-700">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                        <button type="submit" class="px-6 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <i class="bi bi-check-lg"></i> Simpan Penugasan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
