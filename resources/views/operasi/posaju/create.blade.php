<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-slate-800">Buat Pos Aju Baru</h2>
            <a href="{{ route('posaju.index') }}" class="px-3 py-1.5 text-xs border border-slate-300 text-slate-600 rounded-xl hover:bg-slate-100 transition-colors flex items-center gap-2"><i class="bi bi-arrow-left"></i> Kembali</a>
        </div>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl">
                <div class="p-6">
                    <form method="POST" action="{{ $insiden ? route('insiden.posaju.store', $insiden) : route('posaju.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Nama Pos Aju</label>
                            <input type="text" name="nama_posaju" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300" required>
                        </div>
                        <div class="mb-3">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Insiden</label>
                            <select name="id_insiden" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300" @required(!$insiden)>
                                <option value="">— Pilih Insiden —</option>
                                @foreach($insidenList as $i)
                                <option value="{{ $i->id_insiden }}" @selected($insiden && $insiden->id_insiden === $i->id_insiden)>{{ $i->kode_kejadian }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Keputusan Pleno</label>
                            <select name="id_pleno_keputusan" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300" required>
                                <option value="">— Pilih Keputusan Pleno —</option>
                                @foreach($plenoKeputusanList as $pk)
                                <option value="{{ $pk->id_keputusan }}">[#{{ $pk->id_keputusan }}] {{ $pk->nama_keputusan }} ({{ $pk->pleno?->insiden?->kode_kejadian ?? '—' }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Latitude</label>
                            <input type="text" name="latitude" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300" required placeholder="-6.2088">
                        </div>
                        <div class="mb-3">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Longitude</label>
                            <input type="text" name="longitude" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300" required placeholder="106.8456">
                        </div>
                        <div class="mb-3">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Alamat</label>
                            <textarea name="alamat_lokasi" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300" rows="2"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Penanggung Jawab</label>
                            <select name="pj_posaju" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300">
                                <option value="">— Pilih —</option>
                                @foreach($penggunaList as $u)
                                <option value="{{ $u->id_pengguna }}">{{ $u->profil?->nama_lengkap }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="text-right">
                            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-semibold hover:bg-indigo-700 transition-colors flex items-center gap-2"><i class="bi bi-save"></i> Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
