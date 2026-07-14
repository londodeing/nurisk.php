<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-slate-800">{{ $posaju->nama_posaju }}</h2>
            <a href="{{ route('posaju.index') }}" class="px-3 py-1.5 text-xs border border-slate-300 text-slate-600 rounded-xl hover:bg-slate-100 transition-colors flex items-center gap-2"><i class="bi bi-arrow-left"></i> Kembali</a>
        </div>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
<div class="flex border-b border-slate-200 mb-3">
                <a class="px-4 py-2 text-sm font-medium text-indigo-600 border-b-2 border-indigo-600" href="#info" data-bs-toggle="tab">Info</a>
                <a class="px-4 py-2 text-sm font-medium text-slate-500 hover:text-slate-700" href="#stok" data-bs-toggle="tab">Stok</a>
                <a class="px-4 py-2 text-sm font-medium text-slate-500 hover:text-slate-700" href="#personel" data-bs-toggle="tab">Personel</a>
                <a class="px-4 py-2 text-sm font-medium text-slate-500 hover:text-slate-700" href="#distribusi" data-bs-toggle="tab">Distribusi</a>
                <a class="px-4 py-2 text-sm font-medium text-slate-500 hover:text-slate-700" href="#feedback" data-bs-toggle="tab">Feedback</a>
                <a class="px-4 py-2 text-sm font-medium text-slate-500 hover:text-slate-700" href="#logistik" data-bs-toggle="tab">Logistik</a>
            </div>

            <div class="tab-content">
                <div class="tab-pane active" id="info">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl">
                            <div class="px-6 py-4 border-b border-slate-200"><strong>Informasi Umum</strong></div>
                            <div class="p-6">
                                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm">
                                    <dt class="text-slate-500">Insiden</dt><dd>{{ $posaju->insiden?->kode_kejadian }}</dd>
                                    <dt class="text-slate-500">Alamat</dt><dd>{{ $posaju->alamat_lokasi ?? '—' }}</dd>
                                    <dt class="text-slate-500">Status</dt><dd><x-operasi.posaju-status-badge :status="$posaju->status_alur" /></dd>
                                    <dt class="text-slate-500">PJ</dt><dd>{{ $posaju->pj?->profil?->nama_lengkap ?? '—' }}</dd>
                                    <dt class="text-slate-500">Diaktifkan</dt><dd>{{ $posaju->waktu_diaktifkan?->format('d/m/Y H:i') ?? '—' }}</dd>
                                    <dt class="text-slate-500">Ditutup</dt><dd>{{ $posaju->waktu_ditutup?->format('d/m/Y H:i') ?? '—' }}</dd>
                                </dl>
                            </div>
                        </div>
                        <div class="bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl">
                            <div class="px-6 py-4 border-b border-slate-200"><strong>Peta</strong></div>
                            <div class="p-6">
                                <x-operasi.posaju-map :posaju="$posaju" height="250px" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane" id="stok">
                    <div class="bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl">
                        <div class="p-6 border-b border-slate-200 flex items-center justify-between">
                            <strong>Stok Logistik</strong>
                            <a href="{{ route('insiden.posaju.stok.create', [$insiden, $posaju]) }}"
                                class="px-3 py-1.5 text-xs bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition-colors flex items-center gap-1">
                                <i class="bi bi-plus-lg"></i> Tambah Stok
                            </a>
                        </div>
                        <div class="p-6">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-slate-200 text-sm">
                                    <thead class="bg-slate-50">
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Barang</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Gudang</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Jumlah</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Satuan</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Diperbarui</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-200">
                                        @forelse($posaju->stok->load('katalog', 'gudang') as $s)
                                        <tr class="hover:bg-slate-50 transition-colors">
                                            <td class="px-4 py-2">
                                                <div class="font-medium">{{ $s->katalog?->nama_barang_standar ?? '—' }}</div>
                                                @if($s->katalog?->deskripsi)
                                                <div class="text-xs text-slate-500">{{ Str::limit($s->katalog->deskripsi, 40) }}</div>
                                                @endif
                                            </td>
                                            <td class="px-4 py-2">{{ $s->gudang?->nama_gudang ?? '—' }}</td>
                                            <td class="px-4 py-2 font-medium">{{ number_format($s->jumlah_tersedia, 2) }}</td>
                                            <td class="px-4 py-2">{{ $s->katalog?->satuan ?? 'unit' }}</td>
                                            <td class="px-4 py-2 text-slate-500">{{ $s->diperbarui_pada?->format('d/m/Y H:i') ?? '—' }}</td>
                                            <td class="px-4 py-2">
                                                <div class="flex items-center gap-2">
                                                    <a href="{{ route('logistik.mutasi.index', ['stok' => $s->id_stok]) }}"
                                                        class="px-2 py-1 text-xs border border-slate-300 text-slate-600 rounded-xl hover:bg-slate-100 transition-colors">
                                                        <i class="bi bi-arrow-repeat"></i> Mutasi
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="6" class="text-center py-4 text-slate-500">Belum ada stok logistik di pos aju ini</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane" id="permintaan">
                    <div class="bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl">
                        <div class="p-6 border-b border-slate-200 flex items-center justify-between">
                            <strong>Permintaan Logistik</strong>
                            <a href="{{ route('logistik.permintaan.create', ['posaju' => $posaju->id_posaju]) }}"
                                class="px-3 py-1.5 text-xs bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition-colors flex items-center gap-1">
                                <i class="bi bi-plus-lg"></i> Ajukan Permintaan
                            </a>
                        </div>
                        <div class="p-6">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-slate-200 text-sm">
                                    <thead class="bg-slate-50">
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Barang</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Jumlah</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Satuan</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Prioritas</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Status</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Diajukan</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-200">
                                        @forelse($posaju->permintaanLogistik as $p)
                                        <tr class="hover:bg-slate-50 transition-colors">
                                            <td class="px-4 py-2">
                                                <div class="font-medium">{{ $p->barangKatalog?->nama_barang_standar ?? '—' }}</div>
                                                @if($p->catatan)
                                                <div class="text-xs text-slate-500">{{ Str::limit($p->catatan, 40) }}</div>
                                                @endif
                                            </td>
                                            <td class="px-4 py-2 font-medium">{{ number_format($p->jumlah, 2) }}</td>
                                            <td class="px-4 py-2">{{ $p->barangKatalog?->satuan ?? 'unit' }}</td>
                                            <td class="px-4 py-2">
                                                <span class="px-2 py-1 rounded-full text-xs font-semibold
                                                    @switch($p->prioritas)
                                                        @case('darurat') bg-red-100 text-red-700 @break
                                                        @case('mendesak') bg-orange-100 text-orange-700 @break
                                                        @default bg-blue-100 text-blue-700
                                                    @endswitch
                                                ">
                                                    {{ ucfirst($p->prioritas) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-2">
                                                <span class="px-2 py-1 rounded-full text-xs font-semibold
                                                    @switch($p->status_permintaan)
                                                        @case('diajukan') bg-blue-100 text-blue-700 @break
                                                        @case('disetujui') bg-green-100 text-green-700 @break
                                                        @case('dikirim') bg-purple-100 text-purple-700 @break
                                                        @case('selesai') bg-slate-100 text-slate-700 @break
                                                        @default bg-yellow-100 text-yellow-700
                                                    @endswitch
                                                ">
                                                    {{ ucfirst($p->status_permintaan) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-2 text-slate-500">{{ $p->dibuat_pada?->format('d/m/Y H:i') }}</td>
                                            <td class="px-4 py-2">
                                                <a href="{{ route('logistik.permintaan.show', $p) }}"
                                                    class="px-2 py-1 text-xs border border-indigo-300 text-indigo-600 rounded-xl hover:bg-indigo-50 transition-colors">
                                                    Detail
                                                </a>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="7" class="text-center py-4 text-slate-500">Belum ada permintaan logistik</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane" id="personel">
                    <div class="bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl">
                        <div class="p-6 border-b border-slate-200 flex items-center justify-between">
                            <strong>Personel di Pos Aju</strong>
                            <a href="{{ route('insiden.posaju.penugasan.create', [$insiden, $posaju]) }}"
                                class="px-3 py-1.5 text-xs bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition-colors flex items-center gap-1">
                                <i class="bi bi-person-plus"></i> Tambah Personel
                            </a>
                        </div>
                        <div class="p-6">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-slate-200 text-sm">
                                    <thead class="bg-slate-50">
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Nama</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Peran</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Klaster</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Status</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Masa Tugas</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Check-in/out</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-200">
                                        @forelse($posaju->penugasan->load('klasterOperasi.masterKlaster') as $p)
                                        <tr class="hover:bg-slate-50 transition-colors">
                                            <td class="px-4 py-2">{{ $p->pengguna?->profil?->nama_lengkap ?? '—' }}</td>
                                            <td class="px-4 py-2">{{ $p->peran_otoritas }}</td>
                                            <td class="px-4 py-2">{{ $p->klasterOperasi?->masterKlaster?->nama_klaster ?? '—' }}</td>
                                            <td class="px-4 py-2">
                                                <span class="px-2 py-1 rounded-full text-xs font-semibold
                                                    @switch($p->status_penugasan)
                                                        @case('aktif') bg-green-100 text-green-700 @break
                                                        @case('selesai') bg-slate-100 text-slate-700 @break
                                                        @case('draft') bg-yellow-100 text-yellow-700 @break
                                                        @default bg-slate-100 text-slate-700
                                                    @endswitch
                                                ">
                                                    {{ ucfirst($p->status_penugasan) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-2">
                                                {{ $p->waktu_mulai?->format('d/m/Y') }} –
                                                {{ $p->waktu_selesai?->format('d/m/Y') ?? 'Sekarang' }}
                                            </td>
                                            <td class="px-4 py-2 text-slate-500">
                                                @if($p->waktu_checkin || $p->waktu_checkout)
                                                <div class="text-xs">In: {{ $p->waktu_checkin?->format('H:i') ?? '—' }}</div>
                                                <div class="text-xs">Out: {{ $p->waktu_checkout?->format('H:i') ?? '—' }}</div>
                                                @else
                                                —
                                                @endif
                                            </td>
                                            <td class="px-4 py-2">
                                                <a href="{{ route('operasi.penugasan.show', $p) }}"
                                                    class="px-2 py-1 text-xs border border-indigo-300 text-indigo-600 rounded-xl hover:bg-indigo-50 transition-colors">
                                                    Detail
                                                </a>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="7" class="text-center py-4 text-slate-500">Belum ada personel ditugaskan ke pos aju ini</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane" id="distribusi">
                    <div class="bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-slate-800">Distribusi Bantuan</h3>
                                <a href="{{ route('insiden.posaju.distribusi.create', [$insiden, $posaju]) }}"
                                    class="px-3 py-1.5 text-xs bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition-colors flex items-center gap-1">
                                    <i class="bi bi-plus-lg"></i> Baru
                                </a>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-slate-200 text-sm">
                                    <thead class="bg-slate-50">
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Barang</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Klaster</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Jumlah</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Status</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Waktu</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-200">
                                        @forelse($posaju->distribusi as $d)
                                        <tr class="hover:bg-slate-50">
                                            <td class="px-4 py-2">{{ $d->nama_barang }}</td>
                                            <td class="px-4 py-2">{{ $d->klasterOperasi?->masterKlaster?->nama_klaster ?? '—' }}</td>
                                            <td class="px-4 py-2">{{ number_format($d->jumlah, 2) }} {{ $d->satuan }}</td>
                                            <td class="px-4 py-2">
                                                <span class="px-2 py-1 rounded-full text-xs font-semibold
                                                    @switch($d->status_distribusi)
                                                        @case('direncanakan')     bg-yellow-100 text-yellow-700 @break
                                                        @case('didistribusikan')  bg-blue-100 text-blue-700 @break
                                                        @case('diterima')         bg-purple-100 text-purple-700 @break
                                                        @case('direview')         bg-green-100 text-green-700 @break
                                                        @default                  bg-slate-100 text-slate-700
                                                    @endswitch
                                                ">
                                                    {{ ucfirst($d->status_distribusi) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-2">{{ $d->dibuat_pada?->format('d/m/Y H:i') }}</td>
                                            <td class="px-4 py-2">
                                                <a href="{{ route('insiden.posaju.distribusi.show', [$insiden, $posaju, $d]) }}"
                                                    class="px-2 py-1 text-xs border border-indigo-300 text-indigo-600 rounded-xl hover:bg-indigo-50">Detail</a>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="6" class="text-center py-3 text-slate-500">Belum ada distribusi bantuan</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane" id="feedback">
                    <div class="bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-slate-800 mb-4">Feedback Distribusi</h3>
                            @forelse($posaju->distribusi->where('feedback')->where('status_feedback', 'final') as $d)
                            <div class="border border-slate-200 rounded-xl p-4 mb-3">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="font-medium">{{ $d->nama_barang }}</span>
                                    <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-700">Feedback Terkunci</span>
                                </div>
                                <div class="grid grid-cols-2 gap-2 text-sm mb-2">
                                    <div>
                                        <span class="text-slate-500">Kecukupan:</span>
                                        <span class="font-medium
                                            @switch($d->feedback->kecukupan)
                                                @case('cukup')     text-green-600 @break
                                                @case('kurang')    text-orange-600 @break
                                                @case('berlebih')  text-blue-600 @break
                                            @endswitch
                                        ">
                                            {{ ucfirst($d->feedback->kecukupan) }}
                                        </span>
                                    </div>
                                    <div>
                                        <span class="text-slate-500">Kualitas:</span>
                                        <span class="font-medium
                                            @switch($d->feedback->kualitas)
                                                @case('baik')      text-green-600 @break
                                                @case('sedang')    text-yellow-600 @break
                                                @case('buruk')     text-red-600 @break
                                            @endswitch
                                        ">
                                            {{ ucfirst($d->feedback->kualitas) }}
                                        </span>
                                    </div>
                                    <div>
                                        <span class="text-slate-500">Tepat Waktu:</span>
                                        <span class="font-medium {{ $d->feedback->tepat_waktu ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $d->feedback->tepat_waktu ? 'Ya' : 'Tidak' }}
                                        </span>
                                    </div>
                                    <div>
                                        <span class="text-slate-500">Tepat Sasaran:</span>
                                        <span class="font-medium {{ $d->feedback->tepat_sasaran ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $d->feedback->tepat_sasaran ? 'Ya' : 'Tidak' }}
                                        </span>
</div>
                </div>
                <div class="tab-pane" id="klaster">
                    <div class="bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl">
                        <div class="p-6 border-b border-slate-200 flex items-center justify-between">
                            <strong>Klaster Operasional</strong>
                            @php
                                $currentInsiden = $insiden ?? $posaju->insiden;
                            @endphp
                            <a href="{{ route('insiden.klaster.create', $currentInsiden) }}"
                                class="px-3 py-1.5 text-xs bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition-colors flex items-center gap-1">
                                <i class="bi bi-plus-lg"></i> Aktifkan Klaster
                            </a>
                        </div>
                        <div class="p-6">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-slate-200 text-sm">
                                    <thead class="bg-slate-50">
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Klaster</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Status</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Prioritas</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Progres</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Koordinator</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Waktu Aktivasi</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-200">
                                        @php
                                            $currentInsiden = $insiden ?? $posaju->insiden;
                                        @endphp
                                        @forelse($currentInsiden->klaster->load('masterKlaster') as $k)
                                        <tr class="hover:bg-slate-50 transition-colors">
                                            <td class="px-4 py-2">{{ $k->masterKlaster?->nama_klaster ?? '—' }}</td>
                                            <td class="px-4 py-2">
                                                <span class="px-2 py-1 rounded-full text-xs font-semibold
                                                    @switch($k->status_klaster)
                                                        @case('aktif') bg-green-100 text-green-700 @break
                                                        @case('selesai') bg-slate-100 text-slate-700 @break
                                                        @case('ditutup') bg-red-100 text-red-700 @break
                                                        @default bg-yellow-100 text-yellow-700
                                                    @endswitch
                                                ">
                                                    {{ ucfirst($k->status_klaster) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-2">{{ ucfirst($k->prioritas ?? 'sedang') }}</td>
                                            <td class="px-4 py-2">
                                                <div class="w-full bg-slate-200 rounded-full h-2">
                                                    <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ $k->progres_persen ?? 0 }}%"></div>
                                                </div>
                                                <div class="text-xs text-slate-500 mt-1">{{ number_format($k->progres_persen ?? 0, 2) }}%</div>
                                            </td>
                                            <td class="px-4 py-2">{{ $k->pembuat?->profil?->nama_lengkap ?? '—' }}</td>
                                            <td class="px-4 py-2">{{ $k->waktu_aktivasi?->format('d/m/Y H:i') }}</td>
                                            <td class="px-4 py-2">
                                                <a href="{{ route('insiden.klaster.show', [$currentInsiden, $k]) }}"
                                                    class="px-2 py-1 text-xs border border-indigo-300 text-indigo-600 rounded-xl hover:bg-indigo-50 transition-colors">
                                                    Detail
                                                </a>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="7" class="text-center py-3 text-slate-500">Belum ada klaster aktif untuk insiden ini</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                                @if($d->feedback->kendala)
                                <div class="text-sm text-slate-600 mb-1"><strong>Kendala:</strong> {{ $d->feedback->kendala }}</div>
                                @endif
                                @if($d->feedback->rekomendasi)
                                <div class="text-sm text-slate-600"><strong>Rekomendasi:</strong> {{ $d->feedback->rekomendasi }}</div>
                                @endif
                                <div class="text-xs text-slate-400 mt-2">
                                    Oleh: {{ $d->feedback->pengguna?->profil?->nama_lengkap ?? '—' }} |
                                    {{ $d->feedback->dikunci_pada?->format('d/m/Y H:i') }}
                                </div>
                            </div>
                            @empty
                            <p class="text-center text-slate-500 py-4">Belum ada feedback yang terkunci</p>
                            @endforelse
                        </div>
                    </div>
                </div>
                <div class="tab-pane" id="logistik">
                    <div class="bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl">
                        <div class="p-6 border-b border-slate-200 flex items-center justify-between">
                            <strong>Permintaan Logistik</strong>
                            <a href="{{ route('logistik.permintaan.create') }}" class="px-3 py-1.5 text-xs bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition-colors flex items-center gap-1">
                                <i class="bi bi-plus-lg"></i> Ajukan Permintaan
                            </a>
                        </div>
                        <div class="p-6">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-slate-200 text-sm">
                                    <thead class="bg-slate-50">
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Prioritas</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Status</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Klaster</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Keterangan</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Diajukan</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-200">
                                        @forelse($posaju->permintaanLogistik as $p)
                                        <tr class="hover:bg-slate-50 transition-colors">
                                            <td class="px-4 py-2">
                                                <span class="px-2 py-1 rounded-full text-xs font-semibold
                                                    @switch($p->prioritas)
                                                        @case('biasa')     bg-slate-100 text-slate-700 @break
                                                        @case('mendesak')  bg-yellow-100 text-yellow-700 @break
                                                        @case('darurat')   bg-red-100 text-red-700 @break
                                                        @default         bg-slate-100 text-slate-700
                                                    @endswitch
                                                ">
                                                    {{ ucfirst($p->prioritas) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-2">
<span class="px-2 py-1 rounded-full text-xs font-semibold
                                                     @switch($p->status_permintaan)
                                                         @case('draft')     bg-yellow-100 text-yellow-700 @break
                                                         @case('diajukan')   bg-blue-100 text-blue-700 @break
                                                         @case('disetujui')  bg-green-100 text-green-700 @break
                                                         @case('ditolak')    bg-red-100 text-red-700 @break
                                                         @case('dikirim')    bg-purple-100 text-purple-700 @break
                                                         @case('selesai')    bg-slate-100 text-slate-700 @break
                                                         @default          bg-slate-100 text-slate-700
                                                     @endswitch
                                                 ">
                                                    {{ ucfirst($p->status_permintaan) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-2">{{ $p->klaster?->masterKlaster?->nama_klaster ?? '—' }}</td>
                                            <td class="px-4 py-2">{{ $p->keterangan ?? '—' }}</td>
                                            <td class="px-4 py-2">{{ $p->dibuat_pada?->format('d/m/Y H:i') }}</td>
                                            <td class="px-4 py-2">
                                                <a href="{{ route('logistik.permintaan.show', $p) }}"
                                                    class="px-2 py-1 text-xs border border-indigo-300 text-indigo-600 rounded-xl hover:bg-indigo-50 transition-colors">
                                                    Detail
                                                </a>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="6" class="text-center py-3 text-slate-500">Belum ada permintaan logistik</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var lat = {{ $posaju->latitude ?? 'null' }};
    var lng = {{ $posaju->longitude ?? 'null' }};
    var mapEl = document.getElementById('posajuMap');
    if (lat && lng) {
        var map = L.map('posajuMap').setView([lat, lng], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);
        L.marker([lat, lng]).addTo(map)
            .bindPopup('<b>{{ $posaju->nama_posaju }}</b>');
    } else {
        mapEl.innerHTML = '<p class="text-center pt-5 text-slate-500">Koordinat belum diatur</p>';
    }
});
</script>
</x-app-layout>
