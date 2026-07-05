<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-slate-800">Aktifkan Klaster Baru</h2>
            <a href="{{ route('klaster.index') }}" class="px-3 py-1.5 text-xs border border-slate-300 text-slate-600 rounded-xl hover:bg-slate-100 transition-colors flex items-center gap-2"><i class="bi bi-arrow-left"></i> Kembali</a>
        </div>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl">
                <div class="p-6">
                    <form method="POST" action="{{ route('klaster.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Insiden</label>
                            <select name="id_insiden" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300" required>
                                <option value="">— Pilih Insiden —</option>
                                @foreach($insidenList as $i)
                                <option value="{{ $i->id_insiden }}">{{ $i->kode_kejadian }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Jenis Klaster</label>
                            <select name="id_master_klaster" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300" required>
                                <option value="">— Pilih Klaster —</option>
                                @foreach($masterKlasters as $mk)
                                <option value="{{ $mk->id_master_klaster }}">{{ $mk->nama_klaster }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Target Cakupan</label>
                            <textarea name="target_cakupan" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300" rows="2" placeholder="Deskripsi target cakupan klaster ini..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Catatan</label>
                            <textarea name="catatan" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300" rows="2"></textarea>
                        </div>
                        <div class="text-right">
                            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-semibold hover:bg-indigo-700 transition-colors flex items-center gap-2"><i class="bi bi-check-lg"></i> Aktifkan Klaster</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
