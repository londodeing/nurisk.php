<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Insiden: {{ $insiden->kode_kejadian }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    
                    @if($insiden->isTerkunci())
                        <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <span class="text-red-400 font-bold">⚠️</span>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-red-700 font-medium">
                                        Data Terkunci: Insiden ini sudah Closed dan tidak boleh diubah lagi.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('insiden.update', $insiden) }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <!-- Status info (Read-only) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Status Saat Ini</label>
                            <div class="mt-1 flex items-center space-x-2">
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full {{ $insiden->warnaBadgeStatus() }}">
                                    {{ $insiden->labelStatus() }}
                                </span>
                                <span class="text-xs text-gray-500">(Status hanya dapat diubah melalui panel detail status)</span>
                            </div>
                        </div>

                        <!-- Kode Kejadian (Read-only or Pre-filled) -->
                        <div>
                            <label for="kode_kejadian" class="block text-sm font-medium text-gray-700">Kode Kejadian *</label>
                            <input type="text" name="kode_kejadian" id="kode_kejadian" required value="{{ old('kode_kejadian', $insiden->kode_kejadian) }}" @if($insiden->isTerkunci()) disabled @endif class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            @error('kode_kejadian')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Jenis Bencana -->
                        <div>
                            <label for="id_jenis_bencana" class="block text-sm font-medium text-gray-700">Jenis Bencana *</label>
                            <select name="id_jenis_bencana" id="id_jenis_bencana" required @if($insiden->isTerkunci()) disabled @endif class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                @foreach($jenisBencana as $jenis)
                                    <option value="{{ $jenis->id_jenis }}" {{ old('id_jenis_bencana', $insiden->id_jenis_bencana) == $jenis->id_jenis ? 'selected' : '' }}>{{ $jenis->nama_bencana }}</option>
                                @endforeach
                            </select>
                            @error('id_jenis_bencana')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Organisasi PCNU (Read-only) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Organisasi PCNU</label>
                            <input type="text" readonly value="{{ $insiden->pcnu?->nama_pcnu }}" class="mt-1 block w-full bg-gray-100 rounded-md border-gray-300 shadow-sm">
                        </div>

                        <!-- Prioritas -->
                        <div>
                            <label for="prioritas" class="block text-sm font-medium text-gray-700">Prioritas</label>
                            <select name="prioritas" id="prioritas" @if($insiden->isTerkunci()) disabled @endif class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="rendah" {{ old('prioritas', $insiden->prioritas) === 'rendah' ? 'selected' : '' }}>Rendah</option>
                                <option value="sedang" {{ old('prioritas', $insiden->prioritas) === 'sedang' ? 'selected' : '' }}>Sedang</option>
                                <option value="tinggi" {{ old('prioritas', $insiden->prioritas) === 'tinggi' ? 'selected' : '' }}>Tinggi</option>
                                <option value="kritis" {{ old('prioritas', $insiden->prioritas) === 'kritis' ? 'selected' : '' }}>Kritis</option>
                            </select>
                            @error('prioritas')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Waktu Mulai -->
                        <div>
                            <label for="waktu_mulai" class="block text-sm font-medium text-gray-700">Waktu Mulai *</label>
                            <input type="datetime-local" name="waktu_mulai" id="waktu_mulai" required value="{{ old('waktu_mulai', $insiden->waktu_mulai?->format('Y-m-d\TH:i')) }}" @if($insiden->isTerkunci()) disabled @endif class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            @error('waktu_mulai')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Waktu Selesai -->
                        <div>
                            <label for="waktu_selesai" class="block text-sm font-medium text-gray-700">Waktu Selesai (Opsional)</label>
                            <input type="datetime-local" name="waktu_selesai" id="waktu_selesai" value="{{ old('waktu_selesai', $insiden->waktu_selesai?->format('Y-m-d\TH:i')) }}" @if($insiden->isTerkunci()) disabled @endif class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            @error('waktu_selesai')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center justify-end space-x-3">
                            <a href="{{ route('insiden.show', $insiden) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                Batal
                            </a>
                            @if(!$insiden->isTerkunci())
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                    Simpan Perubahan
                                </button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
