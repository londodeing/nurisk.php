<x-app-layout>
    <x-slot name="header">Buat Pleno Baru</x-slot>
    <x-slot name="breadcrumb">
        <a href="{{ route('dashboard.pwnu') }}" class="text-gray-500 hover:text-gray-700">Home</a>
        <span class="text-gray-400 mx-1">/</span>
        <a href="{{ route('insiden.index') }}" class="text-gray-500 hover:text-gray-700">Operasi Insiden</a>
        <span class="text-gray-400 mx-1">/</span>
        <a href="{{ route('insiden.show', $insiden) }}" class="text-gray-500 hover:text-gray-700">{{ $insiden->kode_kejadian }}</a>
        <span class="text-gray-400 mx-1">/</span>
        <a href="{{ route('insiden.pleno.index', $insiden) }}" class="text-gray-500 hover:text-gray-700">Pleno</a>
        <span class="text-gray-400 mx-1">/</span>
        Buat Baru
    </x-slot>

    <div class="bg-white border border-gray-200 rounded-xl p-6">
        <form action="{{ route('insiden.pleno.store', $insiden) }}" method="POST">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Jenis Pleno -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Pleno <span class="text-red-500">*</span></label>
                    <select name="jenis_pleno" required class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-primary-500">
                        <option value="">-- Pilih Jenis Pleno --</option>
                        <option value="aktivasi_operasi">Aktivasi Operasi</option>
                        <option value="evaluasi_rutin">Evaluasi Rutin</option>
                        <option value="perpanjangan_operasi">Perpanjangan Operasi</option>
                        <option value="penutupan_operasi">Penutupan Operasi</option>
                        <option value="eskalasi_wilayah">Eskalasi Wilayah</option>
                        <option value="khusus">Khusus</option>
                    </select>
                </div>

                <!-- Waktu Pleno -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Waktu Pleno <span class="text-red-500">*</span></label>
                    <input type="datetime-local" name="waktu_pleno" required class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-primary-500" value="{{ now()->format('Y-m-d\TH:i') }}">
                </div>

                <!-- Pimpinan Pleno -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pimpinan Pleno <span class="text-red-500">*</span></label>
                    <select name="pimpinan_pleno" required class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-primary-500">
                        <option value="">-- Pilih Pimpinan (Ketua) --</option>
                        @foreach($pimpinanList as $pimpinan)
                        <option value="{{ $pimpinan->id_pengguna }}">{{ $pimpinan->profil?->nama_lengkap ?? $pimpinan->no_hp }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Hanya pengguna yang dipilih yang dapat melakukan finalisasi/pengesahan.</p>
                </div>

                <!-- Lokasi -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi Pleno</label>
                    <input type="text" name="lokasi_pleno" class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-primary-500" placeholder="Contoh: Gedung PCNU">
                </div>
            </div>

            <div class="mt-8 pt-5 border-t border-gray-200 flex justify-end gap-3">
                <a href="{{ route('insiden.pleno.index', $insiden) }}" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">Batal</a>
                <button type="submit" class="px-4 py-2 bg-primary-600 border border-transparent rounded-lg text-sm font-medium text-white hover:bg-primary-700">Simpan sebagai Draft</button>
            </div>
        </form>
    </div>
</x-app-layout>
