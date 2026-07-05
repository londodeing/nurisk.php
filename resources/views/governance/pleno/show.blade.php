<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Pleno: {{ $pleno->nomor_pleno }}
                </h2>
                <span class="px-2.5 py-1 text-xs font-semibold rounded-full {{ $pleno->warnaBadgeStatus() }}">
                    {{ $pleno->labelStatus() }}
                </span>
            </div>
            <a href="{{ route('insiden.pleno.index', $insiden) }}" class="text-sm text-gray-600 hover:text-gray-900">&larr; Kembali ke daftar pleno</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            @if($pleno->isFinal())
                <div class="bg-emerald-50 border-l-4 border-emerald-400 p-4">
                    <div class="flex">
                        <div class="ml-3">
                            <p class="text-sm text-emerald-700 font-bold">PLENO TELAH DIFINALISASI</p>
                            <p class="text-xs text-emerald-600">Keputusan dan voting tidak dapat diubah lagi.</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Tabs -->
            @php
                $tab = request('tab', 'detail');
            @endphp

            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8">
                    <a href="{{ route('insiden.pleno.show', [$insiden, $pleno, 'tab' => 'detail']) }}" class="{{ $tab === 'detail' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm">
                        Detail
                    </a>
                    <a href="{{ route('insiden.pleno.show', [$insiden, $pleno, 'tab' => 'keputusan']) }}" class="{{ $tab === 'keputusan' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm">
                        Keputusan
                    </a>
                    <a href="{{ route('insiden.pleno.show', [$insiden, $pleno, 'tab' => 'peserta']) }}" class="{{ $tab === 'peserta' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm">
                        Peserta &amp; Voting
                    </a>
                    <a href="{{ route('insiden.pleno.show', [$insiden, $pleno, 'tab' => 'eskalasi']) }}" class="{{ $tab === 'eskalasi' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm">
                        Eskalasi
                    </a>
                </nav>
            </div>

            @if($tab === 'detail')
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Pleno</h3>
                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-6">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Nomor Pleno</dt>
                                <dd class="mt-1 text-sm text-gray-900 font-medium">{{ $pleno->nomor_pleno }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Waktu Pleno</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $pleno->waktu_pleno?->format('d F Y H:i') }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Jenis Pleno</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ str_replace('_', ' ', ucfirst($pleno->jenis_pleno)) }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Lokasi</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $pleno->lokasi_pleno }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Pimpinan Rapat</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $pleno->pimpinan?->profil?->nama_lengkap ?? $pleno->pimpinan?->no_hp }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Notulis</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $pleno->notulis?->profil?->nama_lengkap ?? $pleno->notulis?->no_hp }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Status</dt>
                                <dd class="mt-1">
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full {{ $pleno->warnaBadgeStatus() }}">
                                        {{ $pleno->labelStatus() }}
                                    </span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Dibuat Pada</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $pleno->dibuat_pada?->format('d/m/Y H:i') }}</dd>
                            </div>
                            @if($pleno->hasil_umum)
                                <div class="md:col-span-2">
                                    <dt class="text-sm font-medium text-gray-500">Hasil Umum</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $pleno->hasil_umum }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                </div>

            @elseif($tab === 'keputusan')
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Daftar Keputusan</h3>

                        <div class="overflow-x-auto mb-6">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kategori</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jenis</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Deskripsi</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($pleno->keputusan as $keputusan)
                                        <tr>
                                            <td class="px-4 py-3 text-sm text-gray-900">{{ str_replace('_', ' ', ucfirst($keputusan->kategori_objek)) }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-500">{{ str_replace('_', ' ', ucfirst($keputusan->jenis_keputusan)) }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-500">{{ $keputusan->deskripsi_keputusan }}</td>
                                            <td class="px-4 py-3 text-sm">
                                                <span class="px-2 py-0.5 text-xs font-medium rounded {{ $keputusan->status_pelaksanaan === 'selesai' ? 'bg-green-100 text-green-700' : ($keputusan->status_pelaksanaan === 'berjalan' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600') }}">
                                                    {{ ucfirst($keputusan->status_pelaksanaan) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-4 py-3 text-sm text-gray-500 text-center">Belum ada keputusan.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @can('tambahKeputusan', $pleno)
                            @if(!$pleno->isFinal())
                                <div class="border-t border-gray-200 pt-6">
                                    <h4 class="text-md font-semibold text-gray-900 mb-3">Tambah Keputusan</h4>
                                    <form method="POST" action="{{ route('insiden.pleno.keputusan.store', [$insiden, $pleno]) }}" class="space-y-4">
                                        @csrf
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                            <div>
                                                <label for="kategori_objek" class="block text-sm font-medium text-gray-700">Kategori Objek</label>
                                                <select name="kategori_objek" id="kategori_objek" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                                    <option value="">Pilih</option>
                                                    <option value="insiden">Insiden</option>
                                                    <option value="posaju">Pos Aju</option>
                                                    <option value="klaster">Klaster</option>
                                                    <option value="personil">Personil</option>
                                                    <option value="logistik">Logistik</option>
                                                    <option value="anggaran">Anggaran</option>
                                                </select>
                                                @error('kategori_objek')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                            </div>
                                            <div>
                                                <label for="jenis_keputusan" class="block text-sm font-medium text-gray-700">Jenis Keputusan</label>
                                                <select name="jenis_keputusan" id="jenis_keputusan" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                                    <option value="">Pilih</option>
                                                    <option value="penunjukan_personil">Penunjukan Personil</option>
                                                    <option value="aktivasi_pos">Aktivasi Pos</option>
                                                    <option value="perubahan_status_insiden">Perubahan Status Insiden</option>
                                                    <option value="alokasi_sumberdaya">Alokasi Sumberdaya</option>
                                                    <option value="lainnya">Lainnya</option>
                                                </select>
                                                @error('jenis_keputusan')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                            </div>
                                            <div>
                                                <label for="tipe_target_keputusan" class="block text-sm font-medium text-gray-700">Tipe Target</label>
                                                <select name="tipe_target_keputusan" id="tipe_target_keputusan" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                                    <option value="">Pilih</option>
                                                    <option value="pos_aju">Pos Aju</option>
                                                    <option value="personil">Personil</option>
                                                    <option value="logistik">Logistik</option>
                                                    <option value="insiden">Insiden</option>
                                                    <option value="klaster">Klaster</option>
                                                    <option value="perpanjangan_operasi">Perpanjangan Operasi</option>
                                                </select>
                                                @error('tipe_target_keputusan')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                            </div>
                                        </div>
                                        <div>
                                            <label for="deskripsi_keputusan" class="block text-sm font-medium text-gray-700">Deskripsi Keputusan</label>
                                            <textarea name="deskripsi_keputusan" id="deskripsi_keputusan" rows="3" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" placeholder="Minimal 10 karakter..."></textarea>
                                            @error('deskripsi_keputusan')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                        </div>
                                        <div class="flex justify-end">
                                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                                                Simpan Keputusan
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            @endif
                        @endcan
                    </div>
                </div>

            @elseif($tab === 'peserta')
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Daftar Peserta</h3>

                        <div class="overflow-x-auto mb-6">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Peran</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Hak Suara</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Persetujuan</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Catatan</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($pleno->peserta as $peserta)
                                        <tr>
                                            <td class="px-4 py-3 text-sm text-gray-900">{{ $peserta->pengguna?->profil?->nama_lengkap ?? $peserta->pengguna?->no_hp }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-500">{{ $peserta->peran_dalam_rapat }}</td>
                                            <td class="px-4 py-3 text-sm">
                                                @if($peserta->hak_suara)
                                                    <span class="text-green-600 font-medium">Ya</span>
                                                @else
                                                    <span class="text-gray-400">Tidak</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-sm">
                                                @if($peserta->sudahVoting())
                                                    <span class="px-2 py-0.5 text-xs font-medium rounded {{ $peserta->status_persetujuan === 'setuju' ? 'bg-green-100 text-green-700' : ($peserta->status_persetujuan === 'tolak' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600') }}">
                                                        {{ ucfirst($peserta->status_persetujuan) }}
                                                    </span>
                                                @else
                                                    <span class="text-gray-400 italic">Belum voting</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-500">{{ $peserta->catatan_peserta ?? '-' }}</td>
                                            <td class="px-4 py-3 text-sm">
                                                @if($peserta->hak_suara && !$pleno->isFinal())
                                                    <form method="POST" action="{{ route('insiden.pleno.peserta.vote', [$insiden, $pleno, $peserta]) }}" class="flex space-x-1">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="status_persetujuan" value="">
                                                        <button type="submit" name="status_persetujuan" value="setuju" class="px-2 py-1 text-xs font-medium rounded bg-green-100 text-green-700 hover:bg-green-200">Setuju</button>
                                                        <button type="submit" name="status_persetujuan" value="tolak" class="px-2 py-1 text-xs font-medium rounded bg-red-100 text-red-700 hover:bg-red-200">Tolak</button>
                                                        <button type="submit" name="status_persetujuan" value="abstain" class="px-2 py-1 text-xs font-medium rounded bg-gray-100 text-gray-600 hover:bg-gray-200">Abstain</button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-4 py-3 text-sm text-gray-500 text-center">Belum ada peserta.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @can('tambahPeserta', $pleno)
                            @if($pleno->isDraft())
                                <div class="border-t border-gray-200 pt-6">
                                    <h4 class="text-md font-semibold text-gray-900 mb-3">Tambah Peserta</h4>
                                    <form method="POST" action="{{ route('insiden.pleno.peserta.store', [$insiden, $pleno]) }}" class="space-y-4">
                                        @csrf
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                            <div>
                                                <label for="id_pengguna" class="block text-sm font-medium text-gray-700">Pengguna</label>
                                                <select name="id_pengguna" id="id_pengguna" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                                    <option value="">Pilih Pengguna</option>
                                                    @foreach($authUsers as $user)
                                                        <option value="{{ $user->id_pengguna }}">{{ $user->profil?->nama_lengkap ?? $user->no_hp }}</option>
                                                    @endforeach
                                                </select>
                                                @error('id_pengguna')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                            </div>
                                            <div>
                                                <label for="peran_dalam_rapat" class="block text-sm font-medium text-gray-700">Peran</label>
                                                <input type="text" name="peran_dalam_rapat" id="peran_dalam_rapat" value="Peserta" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                            </div>
                                            <div class="flex items-center pt-6">
                                                <label class="inline-flex items-center">
                                                    <input type="checkbox" name="hak_suara" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                                    <span class="ml-2 text-sm text-gray-700">Hak Suara</span>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="flex justify-end">
                                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                                                Tambah Peserta
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            @endif
                        @endcan
                    </div>
                </div>

            @elseif($tab === 'eskalasi')
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    @can('create', [App\Models\OperasiEskalasi::class, $insiden])
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6 bg-white border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Buat Eskalasi Baru</h3>
                                <form method="POST" action="{{ route('insiden.eskalasi.store', $insiden) }}" class="space-y-4">
                                    @csrf
                                    <input type="hidden" name="id_pleno" value="{{ $pleno->id_pleno }}">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label for="level_sebelumnya" class="block text-sm font-medium text-gray-700">Level Sebelumnya</label>
                                            <select name="level_sebelumnya" id="level_sebelumnya" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                                <option value="">Pilih</option>
                                                <option value="lokal">Lokal</option>
                                                <option value="pcnu">PCNU</option>
                                                <option value="pwnu">PWNU</option>
                                                <option value="nasional">Nasional</option>
                                            </select>
                                            @error('level_sebelumnya')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                        </div>
                                        <div>
                                            <label for="level_baru" class="block text-sm font-medium text-gray-700">Level Baru</label>
                                            <select name="level_baru" id="level_baru" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                                <option value="">Pilih</option>
                                                <option value="lokal">Lokal</option>
                                                <option value="pcnu">PCNU</option>
                                                <option value="pwnu">PWNU</option>
                                                <option value="nasional">Nasional</option>
                                            </select>
                                            @error('level_baru')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                        </div>
                                    </div>
                                    <div>
                                        <label for="alasan_eskalasi" class="block text-sm font-medium text-gray-700">Alasan Eskalasi</label>
                                        <textarea name="alasan_eskalasi" id="alasan_eskalasi" rows="3" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" placeholder="Minimal 10 karakter..."></textarea>
                                        @error('alasan_eskalasi')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                    </div>
                                    <div class="flex justify-end">
                                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-orange-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-700">
                                            Buat Eskalasi
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endcan

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Riwayat Eskalasi</h3>
                            @if($pleno->eskalasi)
                                <dl class="space-y-4">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Dari Level</dt>
                                        <dd class="mt-1 text-sm text-gray-900 font-medium">{{ ucfirst($pleno->eskalasi->level_sebelumnya) }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Ke Level</dt>
                                        <dd class="mt-1 text-sm text-gray-900 font-medium">{{ ucfirst($pleno->eskalasi->level_baru) }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Alasan</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $pleno->eskalasi->alasan_eskalasi }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Waktu Eskalasi</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $pleno->eskalasi->waktu_eskalasi?->format('d/m/Y H:i') }}</dd>
                                    </div>
                                </dl>
                            @else
                                <p class="text-sm text-gray-500 italic">Belum ada eskalasi untuk pleno ini.</p>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
