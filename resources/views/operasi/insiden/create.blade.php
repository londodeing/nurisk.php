<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Catat Insiden Baru') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('insiden.store') }}" class="space-y-6">
                        @csrf

                        <!-- Kode Kejadian (Optional) -->
                        <div>
                            <label for="kode_kejadian" class="block text-sm font-medium text-gray-700">Kode Kejadian</label>
                            <input type="text" name="kode_kejadian" id="kode_kejadian" value="{{ old('kode_kejadian') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" placeholder="Kosongkan untuk auto-generate">
                            @error('kode_kejadian')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Jenis Bencana -->
                        <div>
                            <label for="id_jenis_bencana" class="block text-sm font-medium text-gray-700">Jenis Bencana *</label>
                            <select name="id_jenis_bencana" id="id_jenis_bencana" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">Pilih Jenis Bencana</option>
                                @foreach($jenisBencana as $jenis)
                                    <option value="{{ $jenis->id_jenis }}" {{ old('id_jenis_bencana') == $jenis->id_jenis ? 'selected' : '' }}>{{ $jenis->nama_bencana }}</option>
                                @endforeach
                            </select>
                            @error('id_jenis_bencana')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- PCNU -->
                        @php
                            $authCtx = app(\App\Services\Auth\AuthorizationContextService::class);
                            $isPcnu = $authCtx->hasRole('pcnu');
                            $userScopeId = $authCtx->getScopeId();
                        @endphp

                        @if($isPcnu && $userScopeId)
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Organisasi PCNU *</label>
                                @php
                                    $currentPcnuName = $pcnuList->firstWhere('id_pcnu', $userScopeId)?->nama_pcnu ?? 'PCNU Anda';
                                @endphp
                                <input type="text" readonly value="{{ $currentPcnuName }}" class="mt-1 block w-full bg-gray-100 rounded-md border-gray-300 shadow-sm focus:outline-none">
                                <input type="hidden" name="id_pcnu" value="{{ $userScopeId }}">
                            </div>
                        @else
                            <div>
                                <label for="id_pcnu" class="block text-sm font-medium text-gray-700">Organisasi PCNU *</label>
                                <select name="id_pcnu" id="id_pcnu" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option value="">Pilih PCNU</option>
                                    @foreach($pcnuList as $pcnu)
                                        <option value="{{ $pcnu->id_pcnu }}" {{ old('id_pcnu') == $pcnu->id_pcnu ? 'selected' : '' }}>{{ $pcnu->nama_pcnu }}</option>
                                    @endforeach
                                </select>
                                @error('id_pcnu')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif

                        <!-- Prioritas -->
                        <div>
                            <label for="prioritas" class="block text-sm font-medium text-gray-700">Prioritas</label>
                            <select name="prioritas" id="prioritas" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="rendah" {{ old('prioritas') === 'rendah' ? 'selected' : '' }}>Rendah</option>
                                <option value="sedang" {{ old('prioritas', 'sedang') === 'sedang' ? 'selected' : '' }}>Sedang</option>
                                <option value="tinggi" {{ old('prioritas') === 'tinggi' ? 'selected' : '' }}>Tinggi</option>
                                <option value="kritis" {{ old('prioritas') === 'kritis' ? 'selected' : '' }}>Kritis</option>
                            </select>
                            @error('prioritas')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Waktu Mulai -->
                        <div>
                            <label for="waktu_mulai" class="block text-sm font-medium text-gray-700">Waktu Mulai *</label>
                            <input type="datetime-local" name="waktu_mulai" id="waktu_mulai" required value="{{ old('waktu_mulai') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            @error('waktu_mulai')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Waktu Selesai -->
                        <div>
                            <label for="waktu_selesai" class="block text-sm font-medium text-gray-700">Waktu Selesai (Opsional)</label>
                            <input type="datetime-local" name="waktu_selesai" id="waktu_selesai" value="{{ old('waktu_selesai') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            @error('waktu_selesai')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center justify-end space-x-3">
                            <a href="{{ route('insiden.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                Batal
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
