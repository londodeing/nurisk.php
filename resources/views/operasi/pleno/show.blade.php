<x-app-layout>
    <x-slot name="header">Detail Rapat Pleno</x-slot>
    <x-slot name="breadcrumb">
        <a href="{{ route('dashboard.pwnu') }}" class="text-gray-500 hover:text-gray-700">Home</a>
        <span class="text-gray-400 mx-1">/</span>
        <a href="{{ route('insiden.index') }}" class="text-gray-500 hover:text-gray-700">Operasi Insiden</a>
        <span class="text-gray-400 mx-1">/</span>
        <a href="{{ route('insiden.show', $insiden) }}" class="text-gray-500 hover:text-gray-700">{{ $insiden->kode_kejadian }}</a>
        <span class="text-gray-400 mx-1">/</span>
        <a href="{{ route('insiden.pleno.index', $insiden) }}" class="text-gray-500 hover:text-gray-700">Pleno</a>
        <span class="text-gray-400 mx-1">/</span>
        Detail
    </x-slot>

    <!-- Header Status Pleno -->
    <div class="mb-6 p-4 rounded-xl flex items-center justify-between border {{ $pleno->status_pleno === 'final' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-gray-50 border-gray-200 text-gray-800' }}">
        <div class="flex items-center gap-3">
            @if($pleno->status_pleno === 'final')
                <i class="bi bi-lock-fill text-xl"></i>
                <div>
                    <div class="font-semibold text-lg">Pleno Telah Difinalisasi</div>
                    <div class="text-sm">Dokumen ini telah dikunci dan keputusannya sudah efektif.</div>
                </div>
            @else
                <i class="bi bi-unlock text-xl text-gray-500"></i>
                <div>
                    <div class="font-semibold text-lg">Status: {{ $pleno->labelStatus() }}</div>
                    <div class="text-sm text-gray-500">Notulis dapat menambahkan peserta dan keputusan.</div>
                </div>
            @endif
        </div>
        
        @can('finalize', $pleno)
        @if(!$pleno->isFinal())
        <form action="{{ route('insiden.pleno.finalize', [$insiden, $pleno]) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin? Pleno yang difinalisasi tidak dapat diubah lagi.')">
            @csrf
            <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-lg font-semibold hover:bg-primary-700 shadow-sm flex items-center gap-2">
                <i class="bi bi-check-circle"></i> Sahkan & Kunci Pleno
            </button>
        </form>
        @endif
        @endcan
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Kolom Kiri: Metadata & Peserta -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Card Informasi Dasar -->
            <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
                <h3 class="font-semibold text-gray-800 mb-4 border-b pb-2">Informasi Rapat</h3>
                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-gray-500 font-medium">Jenis Pleno</dt>
                        <dd class="text-gray-900">{{ str_replace('_', ' ', Str::title($pleno->jenis_pleno)) }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 font-medium">Waktu</dt>
                        <dd class="text-gray-900">{{ $pleno->waktu_pleno->format('d F Y, H:i') }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 font-medium">Lokasi</dt>
                        <dd class="text-gray-900">{{ $pleno->lokasi_pleno ?? '-' }}</dd>
                    </div>
                    <div class="pt-2 border-t">
                        <dt class="text-gray-500 font-medium">Pimpinan</dt>
                        <dd class="text-gray-900 font-semibold">{{ $pleno->pimpinan?->profil?->nama_lengkap ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 font-medium">Notulis</dt>
                        <dd class="text-gray-900">{{ $pleno->notulis?->profil?->nama_lengkap ?? '-' }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Card Daftar Peserta -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="p-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                    <h3 class="font-semibold text-gray-800">Daftar Kehadiran</h3>
                </div>
                
                @if(!$pleno->isFinal())
                @can('update', $pleno)
                <div class="p-4 border-b border-gray-200 bg-white">
                    <form action="{{ route('insiden.pleno.peserta.store', [$insiden, $pleno]) }}" method="POST" class="space-y-3">
                        @csrf
                        <div>
                            <select name="id_pengguna" required class="w-full text-sm rounded-lg border-gray-300">
                                <option value="">-- Pilih Peserta --</option>
                                @foreach($penggunaList as $pengguna)
                                <option value="{{ $pengguna->id_pengguna }}">{{ $pengguna->profil?->nama_lengkap ?? $pengguna->no_hp }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <select name="peran_dalam_rapat" class="text-sm rounded-lg border-gray-300">
                                <option value="anggota">Anggota</option>
                                <option value="peninjau">Peninjau</option>
                                <option value="narasumber">Narasumber</option>
                            </select>
                            <select name="status_kehadiran" class="text-sm rounded-lg border-gray-300">
                                <option value="hadir">Hadir</option>
                                <option value="absen">Absen</option>
                                <option value="izin">Izin</option>
                            </select>
                        </div>
                        <div class="flex items-center justify-between">
                            <label class="flex items-center gap-2 text-sm text-gray-600">
                                <input type="checkbox" name="hak_suara" class="rounded text-green-600 focus:ring-primary-500" value="1" checked>
                                Hak Suara
                            </label>
                            <button type="submit" class="px-3 py-1.5 bg-blue-600 text-white text-xs rounded hover:bg-blue-700">Tambah</button>
                        </div>
                    </form>
                </div>
                @endcan
                @endif

                <ul class="divide-y divide-gray-100 max-h-96 overflow-y-auto">
                    @forelse($pleno->peserta as $peserta)
                    <li class="p-4 flex items-center justify-between">
                        <div>
                            <div class="font-medium text-sm text-gray-900">{{ $peserta->pengguna?->profil?->nama_lengkap ?? 'Unknown' }}</div>
                            <div class="text-xs text-gray-500 flex gap-2 mt-1">
                                <span class="capitalize">{{ $peserta->peran_dalam_rapat }}</span>
                                •
                                <span class="{{ $peserta->status_kehadiran === 'hadir' ? 'text-green-600' : 'text-red-500' }} capitalize">{{ $peserta->status_kehadiran }}</span>
                                @if($peserta->hak_suara)
                                <span title="Memiliki Hak Suara">🗳️</span>
                                @endif
                            </div>
                        </div>
                        @if(!$pleno->isFinal() && $peserta->peran_dalam_rapat !== 'pimpinan')
                        @can('update', $pleno)
                        <form action="{{ route('insiden.pleno.peserta.destroy', [$insiden, $pleno, $peserta]) }}" method="POST">
                            @csrf @method('DELETE')
                            <button class="text-red-500 hover:text-red-700 p-1"><i class="bi bi-trash"></i></button>
                        </form>
                        @endcan
                        @endif
                    </li>
                    @empty
                    <li class="p-4 text-center text-sm text-gray-500">Belum ada peserta.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <!-- Kolom Kanan: Keputusan -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-gray-200 bg-gray-50">
                    <h3 class="font-semibold text-gray-800 text-lg">Butir Keputusan Operasional</h3>
                    <p class="text-sm text-gray-500 mt-1">Daftar keputusan yang disepakati untuk ditindaklanjuti.</p>
                </div>

                @if(!$pleno->isFinal())
                @can('update', $pleno)
                <div class="p-5 border-b border-gray-200 bg-blue-50/30" x-data="{ kategori: 'status_insiden' }">
                    <form action="{{ route('insiden.pleno.keputusan.store', [$insiden, $pleno]) }}" method="POST" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Kategori Target</label>
                                <select name="kategori_objek" required x-model="kategori" class="w-full text-sm rounded-lg border-gray-300">
                                    <option value="status_insiden">Perubahan Status Insiden</option>
                                    <option value="aktivasi_posko">Aktivasi Posko / Pos Aju</option>
                                    <option value="aktivasi_klaster">Aktivasi Klaster Bantuan</option>
                                    <option value="mobilisasi_relawan">Mobilisasi Relawan</option>
                                    <option value="eskalasi_wilayah">Eskalasi Wilayah</option>
                                    <option value="logistik">Distribusi Logistik</option>
                                    <option value="lainnya">Lainnya</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Jenis Keputusan</label>
                                <select name="jenis_keputusan" required class="w-full text-sm rounded-lg border-gray-300">
                                    <option value="">-- Pilih Jenis --</option>
                                    <option value="penunjukan_personil">Penunjukan Personil</option>
                                    <option value="aktivasi_pos">Aktivasi Pos</option>
                                    <option value="perubahan_status_insiden">Perubahan Status Insiden</option>
                                    <option value="alokasi_sumberdaya">Alokasi Sumberdaya</option>
                                    <option value="lainnya">Lainnya</option>
                                </select>
                            </div>
                        </div>

                        <!-- Dynamic Fields for Aktivasi Posko -->
                        <template x-if="kategori === 'aktivasi_posko'">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 bg-white rounded border border-gray-200 mt-2">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Nama Pos Aju (Opsional)</label>
                                    <input type="text" name="payload[nama_posaju]" class="w-full text-sm rounded-lg border-gray-300" placeholder="Pos Aju Utama">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Lokasi (Opsional)</label>
                                    <input type="text" name="payload[lokasi_posaju]" class="w-full text-sm rounded-lg border-gray-300" placeholder="Otomatis ikut lokasi pleno jika kosong">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Koordinator Pos</label>
                                    <select name="payload[id_koordinator]" required class="w-full text-sm rounded-lg border-gray-300">
                                        <option value="">-- Pilih Koordinator --</option>
                                        @foreach($penggunaList as $pengguna)
                                        <option value="{{ $pengguna->id_pengguna }}">{{ $pengguna->profil?->nama_lengkap ?? $pengguna->no_hp }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </template>

                        <!-- Dynamic Fields for Aktivasi Klaster -->
                        <template x-if="kategori === 'aktivasi_klaster'">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 bg-white rounded border border-gray-200 mt-2">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-2">Pilih Jenis Klaster</label>
                                    <div class="space-y-2 max-h-40 overflow-y-auto">
                                        @foreach($masterKlasterList ?? [] as $klaster)
                                        <label class="flex items-center gap-2 text-sm">
                                            <input type="checkbox" name="payload[jenis_klaster][]" value="{{ $klaster->id_master_klaster }}" class="rounded text-blue-600 focus:ring-blue-500">
                                            <span>{{ $klaster->nama_klaster }}</span>
                                        </label>
                                        @endforeach
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Koordinator Utama Klaster (Opsional)</label>
                                    <select name="payload[id_koordinator]" class="w-full text-sm rounded-lg border-gray-300">
                                        <option value="">-- Kosongkan Jika Menyusul --</option>
                                        @foreach($penggunaList as $pengguna)
                                        <option value="{{ $pengguna->id_pengguna }}">{{ $pengguna->profil?->nama_lengkap ?? $pengguna->no_hp }}</option>
                                        @endforeach
                                    </select>
                                    <p class="text-xs text-gray-500 mt-2">Jika Anda memilih beberapa klaster sekaligus, koordinator ini akan di-assign untuk semuanya. Anda bisa mengatur koordinator secara terpisah nanti di menu Personil.</p>
                                </div>
                            </div>
                        </template>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Deskripsi Lengkap</label>
                            <textarea name="deskripsi_keputusan" rows="3" required class="w-full text-sm rounded-lg border-gray-300" placeholder="Detail keputusan operasional..."></textarea>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700">
                                <i class="bi bi-plus"></i> Tambah Keputusan
                            </button>
                        </div>
                    </form>
                </div>
                @endcan
                @endif

                <div class="p-5">
                    @forelse($pleno->keputusan as $index => $keputusan)
                    <div class="mb-4 last:mb-0 p-4 border border-gray-200 rounded-lg relative">
                        @if(!$pleno->isFinal())
                        @can('update', $pleno)
                        <form action="{{ route('insiden.pleno.keputusan.destroy', [$insiden, $pleno, $keputusan]) }}" method="POST" class="absolute top-4 right-4">
                            @csrf @method('DELETE')
                            <button class="text-red-400 hover:text-red-600"><i class="bi bi-x-lg"></i></button>
                        </form>
                        @endcan
                        @endif
                        
                        <div class="flex items-center gap-2 mb-2">
                            <span class="bg-gray-800 text-white text-xs font-bold px-2 py-1 rounded">#{{ $index + 1 }}</span>
                            <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2 py-1 rounded capitalize">{{ str_replace('_', ' ', $keputusan->kategori_objek) }}</span>
                        </div>
                        <h4 class="font-semibold text-gray-900 text-base mb-1">{{ $keputusan->jenis_keputusan }}</h4>
                        <p class="text-sm text-gray-600 whitespace-pre-wrap">{{ $keputusan->deskripsi_keputusan }}</p>
                        
                        <div class="mt-3 pt-3 border-t border-gray-100 text-xs text-gray-500 flex items-center justify-between">
                            <div>
                                Status Pelaksanaan: <span class="font-medium {{ in_array($keputusan->status_pelaksanaan, ['selesai', 'dieksekusi']) ? 'text-green-600' : 'text-amber-600' }}">{{ ucfirst($keputusan->status_pelaksanaan) }}</span>
                            </div>
                            @if($keputusan->status_pelaksanaan === 'selesai' && $keputusan->referensi_tabel === 'operasi_posaju' && $keputusan->referensi_id)
                                @php $posaju = \App\Models\OperasiPosaju::find($keputusan->referensi_id); @endphp
                                @if($posaju)
                                <a href="{{ route('insiden.posaju.show', [$insiden, $posaju]) }}" class="text-xs text-blue-600 hover:text-blue-800 underline">
                                    Pos Aju: {{ $posaju->nama_posaju }}
                                </a>
                                @endif
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-8 text-gray-500 text-sm">
                        Belum ada butir keputusan operasional yang dicatat.
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
