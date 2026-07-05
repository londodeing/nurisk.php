<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Surat: {{ $surat->nomor_surat_resmi }}
                </h2>
                @php
                    $badgeMap = [
                        'draft' => 'bg-gray-100 text-gray-700',
                        'review_paraf' => 'bg-yellow-100 text-yellow-700',
                        'siap_tanda_tangan' => 'bg-blue-100 text-blue-700',
                        'ditandatangani' => 'bg-green-100 text-green-700',
                    ];
                @endphp
                <span class="px-2.5 py-1 text-xs font-semibold rounded-full {{ $badgeMap[$surat->status_surat] ?? 'bg-gray-100 text-gray-600' }}">
                    {{ str_replace('_', ' ', ucfirst($surat->status_surat)) }}
                </span>
            </div>
            <a href="{{ route('surat.index') }}" class="text-sm text-gray-600 hover:text-gray-900">&larr; Kembali</a>
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

            @if($surat->status_surat === 'ditandatangani')
                <div class="bg-emerald-50 border-l-4 border-emerald-400 p-4">
                    <div class="flex">
                        <div class="ml-3">
                            <p class="text-sm text-emerald-700 font-bold">SURAT TELAH DITANDATANGANI</p>
                            <p class="text-xs text-emerald-600">Surat ini sudah final dan tidak dapat diubah.</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Detail Surat -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Surat</h3>
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-6">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Nomor Surat</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-medium">{{ $surat->nomor_surat_resmi }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Jenis Surat</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $surat->jenisSurat?->nama_jenis ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Perihal</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $surat->perihal }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Tanggal Terbit</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $surat->tgl_terbit?->format('d/m/Y') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Penandatangan</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $surat->penandatangan?->profil?->nama_lengkap ?? $surat->penandatangan?->no_hp }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Jabatan TTD</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $surat->jabatanTtd?->nama_jabatan ?? '-' }}</dd>
                        </div>
                        @if($surat->insiden)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Insiden Terkait</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $surat->insiden->kode_kejadian }} — {{ $surat->insiden->nama_kejadian }}</dd>
                            </div>
                        @endif
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Status</dt>
                            <dd class="mt-1">
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full {{ $badgeMap[$surat->status_surat] ?? 'bg-gray-100 text-gray-600' }}">
                                    {{ str_replace('_', ' ', ucfirst($surat->status_surat)) }}
                                </span>
                            </dd>
                        </div>
                    </dl>

                    <div class="mt-6 flex flex-wrap gap-2">
                        @can('update', $surat)
                            @if($surat->isDraft())
                                <a href="{{ route('surat.edit', $surat) }}" class="inline-flex items-center px-3 py-1.5 bg-indigo-100 text-indigo-700 rounded-md text-sm font-medium hover:bg-indigo-200">Edit</a>

                                <form action="{{ route('surat.kirim-review', $surat) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-yellow-100 text-yellow-700 rounded-md text-sm font-medium hover:bg-yellow-200"
                                            onclick="return confirm('Kirim surat ke review paraf?')">Kirim ke Review</button>
                                </form>
                            @endif
                        @endcan

                        @can('finalisasi', $surat)
                            <form action="{{ route('surat.finalisasi', $surat) }}" method="POST" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-emerald-100 text-emerald-700 rounded-md text-sm font-medium hover:bg-emerald-200"
                                        onclick="return confirm('Finalisasi surat? Tindakan ini tidak dapat dibatalkan.')">Finalisasi &amp; TTD</button>
                            </form>
                        @endcan

                        @if($surat->file_pdf_path)
                            <a href="{{ route('surat.pdf', $surat) }}" class="inline-flex items-center px-3 py-1.5 bg-red-100 text-red-700 rounded-md text-sm font-medium hover:bg-red-200">Download PDF</a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Daftar Paraf -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Alur Paraf</h3>

                    <div class="overflow-x-auto mb-6">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Urutan</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pengguna</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Catatan</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Waktu</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($surat->paraf as $paraf)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-900 font-medium">{{ $paraf->urutan }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-500">
                                            {{ $paraf->pengguna?->profil?->nama_lengkap ?? $paraf->pengguna?->no_hp }}
                                        </td>
                                        <td class="px-4 py-3 text-sm">
                                            @php
                                                $statusParafMap = [
                                                    'menunggu' => 'bg-gray-100 text-gray-600',
                                                    'disetujui' => 'bg-green-100 text-green-700',
                                                    'ditolak' => 'bg-red-100 text-red-700',
                                                ];
                                            @endphp
                                            <span class="px-2 py-0.5 text-xs font-medium rounded {{ $statusParafMap[$paraf->status_paraf] ?? 'bg-gray-100 text-gray-600' }}">
                                                {{ ucfirst($paraf->status_paraf) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-500">{{ $paraf->catatan ?? '-' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-500">{{ $paraf->waktu_paraf?->format('d/m/Y H:i') ?? '-' }}</td>
                                        <td class="px-4 py-3 text-sm">
                                            @can('paraf', $paraf)
                                                @if($paraf->status_paraf === 'menunggu')
                                                    <form method="POST" action="{{ route('surat.paraf.update', [$surat, $paraf]) }}" class="flex space-x-1">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="status_paraf" value="">
                                                        <button type="submit" name="status_paraf" value="disetujui" class="px-2 py-1 text-xs font-medium rounded bg-green-100 text-green-700 hover:bg-green-200">Setujui</button>
                                                        <button type="submit" name="status_paraf" value="ditolak" class="px-2 py-1 text-xs font-medium rounded bg-red-100 text-red-700 hover:bg-red-200">Tolak</button>
                                                    </form>
                                                @endif
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-3 text-sm text-gray-500 text-center">Belum ada paraf.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @can('update', $surat)
                        @if($surat->isDraft())
                            <div class="border-t border-gray-200 pt-6">
                                <h4 class="text-md font-semibold text-gray-900 mb-3">Tambah Paraf</h4>
                                <form method="POST" action="{{ route('surat.paraf.store', $surat) }}" class="space-y-4">
                                    @csrf
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label for="id_pengguna" class="block text-sm font-medium text-gray-700">Pengguna</label>
                                            <select name="id_pengguna" id="id_pengguna" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                                <option value="">Pilih Pengguna</option>
                                                @foreach($pengguna as $user)
                                                    <option value="{{ $user->id_pengguna }}">{{ $user->profil?->nama_lengkap ?? $user->no_hp }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label for="urutan" class="block text-sm font-medium text-gray-700">Urutan</label>
                                            <input type="number" name="urutan" id="urutan" required min="1" max="10" value="{{ old('urutan', ($surat->paraf->max('urutan') ?? 0) + 1) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        </div>
                                    </div>
                                    <div class="flex justify-end">
                                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">Tambah Paraf</button>
                                    </div>
                                </form>
                            </div>
                        @endif
                    @endcan
                </div>
            </div>

            <!-- Tembusan -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Tembusan</h3>
                    @if($surat->tembusan->count())
                        <ul class="list-disc list-inside text-sm text-gray-700 space-y-1">
                            @foreach($surat->tembusan as $tembusan)
                                <li>{{ $tembusan->nama_pihak }}</li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-sm text-gray-500 italic">Tidak ada tembusan.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
