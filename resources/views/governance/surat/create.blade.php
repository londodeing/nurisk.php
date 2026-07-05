<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Buat Surat Baru</h2>
            <a href="{{ route('surat.index') }}" class="text-sm text-gray-600 hover:text-gray-900">&larr; Kembali</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('surat.store') }}" class="space-y-6">
                        @csrf

                        <div>
                            <label for="id_jenis_surat" class="block text-sm font-medium text-gray-700">Jenis Surat *</label>
                            <select name="id_jenis_surat" id="id_jenis_surat" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">Pilih Jenis Surat</option>
                                @foreach($jenisSurat as $jns)
                                    <option value="{{ $jns->id_jenis_surat }}" {{ old('id_jenis_surat') == $jns->id_jenis_surat ? 'selected' : '' }}>
                                        {{ $jns->nama_jenis }} ({{ $jns->kode_jenis }})
                                    </option>
                                @endforeach
                            </select>
                            @error('id_jenis_surat')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="perihal" class="block text-sm font-medium text-gray-700">Perihal *</label>
                            <input type="text" name="perihal" id="perihal" required maxlength="255" value="{{ old('perihal') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            @error('perihal')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="tgl_terbit" class="block text-sm font-medium text-gray-700">Tanggal Terbit *</label>
                            <input type="date" name="tgl_terbit" id="tgl_terbit" required value="{{ old('tgl_terbit', date('Y-m-d')) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            @error('tgl_terbit')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="id_pengguna_ttd" class="block text-sm font-medium text-gray-700">Penandatangan *</label>
                            <select name="id_pengguna_ttd" id="id_pengguna_ttd" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">Pilih Penandatangan</option>
                                @foreach($pengguna as $user)
                                    <option value="{{ $user->id_pengguna }}" {{ old('id_pengguna_ttd') == $user->id_pengguna ? 'selected' : '' }}>
                                        {{ $user->profil?->nama_lengkap ?? $user->no_hp }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_pengguna_ttd')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="id_jabatan_ttd" class="block text-sm font-medium text-gray-700">Jabatan Penandatangan (Opsional)</label>
                            <select name="id_jabatan_ttd" id="id_jabatan_ttd" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">Pilih Jabatan</option>
                                @foreach($jabatan as $jbt)
                                    <option value="{{ $jbt->id_jabatan }}" {{ old('id_jabatan_ttd') == $jbt->id_jabatan ? 'selected' : '' }}>
                                        {{ $jbt->nama_jabatan }} (Hierarki {{ $jbt->urutan_hierarki }})
                                    </option>
                                @endforeach
                            </select>
                            @error('id_jabatan_ttd')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="id_insiden" class="block text-sm font-medium text-gray-700">Insiden Terkait (Opsional)</label>
                            <select name="id_insiden" id="id_insiden" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">Pilih Insiden</option>
                                @foreach($insidenList as $ins)
                                    <option value="{{ $ins->id_insiden }}" {{ old('id_insiden') == $ins->id_insiden ? 'selected' : '' }}>
                                        {{ $ins->kode_kejadian }} — {{ $ins->nama_kejadian }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_insiden')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div class="flex items-center justify-end space-x-3">
                            <a href="{{ route('surat.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
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
