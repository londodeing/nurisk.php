@extends('layouts.app')

@section('title', 'Daftar Aset Baru')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-lg">
    <div class="bg-white rounded-xl shadow-lg border border-emerald-100 overflow-hidden">
        <!-- HEADER -->
        <div class="bg-emerald-600 px-6 py-4">
            <h2 class="text-xl font-bold text-white flex items-center gap-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                Registrasi Aset NU
            </h2>
            <p class="text-emerald-100 text-sm mt-1">Hanya butuh 1 Menit untuk mendata harta organisasi.</p>
        </div>

        <form action="{{ route('assets.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
            @csrf

            <!-- 1. KATEGORI -->
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">1. Pilih Jenis Aset *</label>
                <div class="grid grid-cols-3 gap-3">
                    <label class="cursor-pointer">
                        <input type="radio" name="category" value="FACILITY" class="peer sr-only" required>
                        <div class="text-center p-3 border-2 rounded-lg peer-checked:border-emerald-500 peer-checked:bg-emerald-50 hover:bg-gray-50 transition">
                            <span class="block text-2xl mb-1">🏢</span>
                            <span class="text-xs font-semibold text-gray-700">Fasilitas<br>(Gedung/Tanah)</span>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="category" value="FLEET" class="peer sr-only">
                        <div class="text-center p-3 border-2 rounded-lg peer-checked:border-emerald-500 peer-checked:bg-emerald-50 hover:bg-gray-50 transition">
                            <span class="block text-2xl mb-1">🚑</span>
                            <span class="text-xs font-semibold text-gray-700">Armada<br>(Mobil/Perahu)</span>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="category" value="EQUIPMENT" class="peer sr-only">
                        <div class="text-center p-3 border-2 rounded-lg peer-checked:border-emerald-500 peer-checked:bg-emerald-50 hover:bg-gray-50 transition">
                            <span class="block text-2xl mb-1">⚙️</span>
                            <span class="text-xs font-semibold text-gray-700">Perlengkapan<br>(Genset/HT)</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- 2. NAMA ASET -->
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">2. Nama Aset *</label>
                <input type="text" name="name" required placeholder="Contoh: Ambulans PCNU Jepara / Aula Ponpes Al-Fatah" 
                    class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition shadow-sm text-gray-800">
            </div>

            <!-- 3. PEMILIK -->
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">3. Pemilik Aset (De Facto) *</label>
                <select name="owner_node_id" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 shadow-sm text-gray-800">
                    <option value="">-- Pilih Organisasi --</option>
                    @foreach($nodes ?? [] as $node)
                        <option value="{{ $node->id }}">{{ $node->name }}</option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-500 mt-1">Siapa yang secara organisasi diakui memiliki aset ini?</p>
            </div>

            <!-- 4. FOTO (OPSIONAL TAPI MENARIK) -->
            <div class="pt-2 border-t border-gray-100">
                <label class="block text-sm font-bold text-gray-700 mb-2">4. Foto Aset (Sangat Disarankan)</label>
                <div class="flex items-center justify-center w-full">
                    <label class="flex flex-col w-full h-32 border-2 border-dashed border-gray-300 hover:border-emerald-500 hover:bg-emerald-50 rounded-lg cursor-pointer transition">
                        <div class="flex flex-col items-center justify-center pt-7">
                            <svg class="w-8 h-8 text-gray-400 group-hover:text-emerald-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            <p class="text-sm text-gray-500 font-medium">Ketuk untuk Buka Kamera</p>
                        </div>
                        <input type="file" name="foto" accept="image/*" capture="environment" class="opacity-0" />
                    </label>
                </div>
            </div>

            <!-- HIDDEN FIELDS (For Minimum Dataset bypass) -->
            <input type="hidden" name="home_territory_code" value="33.20"> <!-- Default to Jepara logic for now -->

            <!-- SUBMIT -->
            <div class="pt-4">
                <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-4 rounded-xl shadow-lg transition transform hover:-translate-y-0.5 flex items-center justify-center gap-2 text-lg">
                    <span>Simpan Aset</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </button>
            </div>
        </form>
    </div>

    <!-- IMPORT MASSAL SECTION -->
    <div class="mt-8 bg-white rounded-xl shadow border border-gray-200 overflow-hidden">
        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                Import Massal (Excel/CSV)
            </h3>
            <p class="text-xs text-gray-500 mt-1">Upload ribuan aset sekaligus untuk 1 organisasi (Format: nama_aset, kategori, nama_bpkb).</p>
        </div>
        <form action="{{ route('admin.assets.import') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Pemilik Aset (Untuk Semua Baris Excel) *</label>
                <select name="owner_node_id" required class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 shadow-sm text-gray-800 text-sm">
                    <option value="">-- Pilih Organisasi --</option>
                    @foreach($nodes ?? [] as $node)
                        <option value="{{ $node->id }}">{{ $node->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">File CSV *</label>
                <input type="file" name="csv_file" accept=".csv" required class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
            </div>
            <input type="hidden" name="home_territory_code" value="33">
            <button type="submit" class="w-full bg-gray-800 hover:bg-gray-900 text-white font-bold py-3 rounded-lg shadow transition flex items-center justify-center gap-2 text-sm mt-2">
                <span>Mulai Import</span>
            </button>
        </form>
    </div>

</div>
@endsection
