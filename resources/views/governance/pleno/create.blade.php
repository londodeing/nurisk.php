<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Buat Pleno Baru — {{ $insiden->kode_kejadian }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('insiden.pleno.store', $insiden) }}" class="space-y-6">
                        @csrf

                        <input type="hidden" name="id_insiden" value="{{ $insiden->id_insiden }}">

                        <div>
                            <label for="nomor_pleno" class="block text-sm font-medium text-gray-700">Nomor Pleno</label>
                            <input type="text" name="nomor_pleno" id="nomor_pleno" value="{{ old('nomor_pleno') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" placeholder="Kosongkan untuk auto-generate">
                            @error('nomor_pleno')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="waktu_pleno" class="block text-sm font-medium text-gray-700">Waktu Pleno *</label>
                            <input type="datetime-local" name="waktu_pleno" id="waktu_pleno" required value="{{ old('waktu_pleno') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            @error('waktu_pleno')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="jenis_pleno" class="block text-sm font-medium text-gray-700">Jenis Pleno *</label>
                            <select name="jenis_pleno" id="jenis_pleno" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">Pilih Jenis Pleno</option>
                                <option value="aktivasi_operasi" {{ old('jenis_pleno') === 'aktivasi_operasi' ? 'selected' : '' }}>Aktivasi Operasi</option>
                                <option value="evaluasi_rutin" {{ old('jenis_pleno', 'evaluasi_rutin') === 'evaluasi_rutin' ? 'selected' : '' }}>Evaluasi Rutin</option>
                                <option value="perpanjangan_operasi" {{ old('jenis_pleno') === 'perpanjangan_operasi' ? 'selected' : '' }}>Perpanjangan Operasi</option>
                                <option value="penutupan_operasi" {{ old('jenis_pleno') === 'penutupan_operasi' ? 'selected' : '' }}>Penutupan Operasi</option>
                                <option value="eskalasi_wilayah" {{ old('jenis_pleno') === 'eskalasi_wilayah' ? 'selected' : '' }}>Eskalasi Wilayah</option>
                                <option value="khusus" {{ old('jenis_pleno') === 'khusus' ? 'selected' : '' }}>Khusus</option>
                            </select>
                            @error('jenis_pleno')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="pimpinan_pleno" class="block text-sm font-medium text-gray-700">Pimpinan Rapat *</label>
                            <select name="pimpinan_pleno" id="pimpinan_pleno" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">Pilih Pimpinan</option>
                                @foreach($authUsers as $user)
                                    <option value="{{ $user->id_pengguna }}" {{ old('pimpinan_pleno') == $user->id_pengguna ? 'selected' : '' }}>{{ $user->profil?->nama_lengkap ?? $user->no_hp }}</option>
                                @endforeach
                            </select>
                            @error('pimpinan_pleno')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="notulis_pleno" class="block text-sm font-medium text-gray-700">Notulis *</label>
                            <select name="notulis_pleno" id="notulis_pleno" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">Pilih Notulis</option>
                                @foreach($authUsers as $user)
                                    <option value="{{ $user->id_pengguna }}" {{ old('notulis_pleno') == $user->id_pengguna ? 'selected' : '' }}>{{ $user->profil?->nama_lengkap ?? $user->no_hp }}</option>
                                @endforeach
                            </select>
                            @error('notulis_pleno')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="lokasi_pleno" class="block text-sm font-medium text-gray-700">Lokasi</label>
                            <input type="text" name="lokasi_pleno" id="lokasi_pleno" value="{{ old('lokasi_pleno', 'Posko Utama') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            @error('lokasi_pleno')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="hasil_umum" class="block text-sm font-medium text-gray-700">Hasil Umum (Opsional)</label>
                            <textarea name="hasil_umum" id="hasil_umum" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('hasil_umum') }}</textarea>
                            @error('hasil_umum')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center justify-end space-x-3">
                            <a href="{{ route('insiden.pleno.index', $insiden) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Batal
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 transition ease-in-out duration-150">
                                Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
