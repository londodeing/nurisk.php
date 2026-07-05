<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Jabatan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <!-- Peringatan jika jabatan digunakan oleh pengguna aktif -->
            @php
                $activeUsersCount = $jabatan->penggunaJabatan()->where('status_aktif', 1)->count();
            @endphp
            @if ($activeUsersCount > 0)
                <div class="mb-4 bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative" role="alert">
                    <strong class="font-bold">Perhatian:</strong>
                    <span class="block sm:inline">Jabatan ini saat ini digunakan oleh {{ $activeUsersCount }} pengguna aktif.</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form action="{{ route('admin.jabatan.update', $jabatan) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Nama Jabatan -->
                        <div class="mb-4">
                            <label for="nama_jabatan" class="block text-sm font-medium text-gray-700">Nama Jabatan</label>
                            <input type="text" name="nama_jabatan" id="nama_jabatan" value="{{ old('nama_jabatan', $jabatan->nama_jabatan) }}"
                                class="mt-1 block w-full rounded-md shadow-sm @error('nama_jabatan') border-red-500 @else border-gray-300 @enderror focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                required>
                            @error('nama_jabatan')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Slug -->
                        <div class="mb-4">
                            <label for="slug" class="block text-sm font-medium text-gray-700">Slug</label>
                            <input type="text" name="slug" id="slug" value="{{ old('slug', $jabatan->slug) }}"
                                class="mt-1 block w-full rounded-md shadow-sm @error('slug') border-red-500 @else border-gray-300 @enderror focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                required>
                            @error('slug')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Deskripsi -->
                        <div class="mb-4">
                            <label for="deskripsi" class="block text-sm font-medium text-gray-700">Deskripsi</label>
                            <textarea name="deskripsi" id="deskripsi" rows="4"
                                class="mt-1 block w-full rounded-md shadow-sm @error('deskripsi') border-red-500 @else border-gray-300 @enderror focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('deskripsi', $jabatan->deskripsi) }}</textarea>
                            @error('deskripsi')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center justify-end mt-6 space-x-3">
                            <a href="{{ route('admin.jabatan.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 active:bg-gray-400 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 transition ease-in-out duration-150">
                                Batal
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 transition ease-in-out duration-150">
                                Perbarui
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Inline JS for Auto-slug -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const namaInput = document.getElementById('nama_jabatan');
            const slugInput = document.getElementById('slug');
            
            let slugModifiedManually = false;

            slugInput.addEventListener('input', function() {
                slugModifiedManually = true;
            });

            namaInput.addEventListener('input', function() {
                if (!slugModifiedManually) {
                    slugInput.value = generateSlug(namaInput.value);
                }
            });

            function generateSlug(text) {
                return text.toLowerCase()
                           .replace(/[^a-z0-9\s\-]/g, '')
                           .replace(/\s+/g, '-')
                           .replace(/-+/g, '-')
                           .trim();
            }
        });
    </script>
</x-app-layout>
