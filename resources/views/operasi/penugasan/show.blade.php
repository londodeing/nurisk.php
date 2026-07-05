<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Detail Penugasan — {{ $penugasan->pengguna?->profil?->nama_lengkap ?? 'Personel' }}
            </h2>
            <div class="flex gap-2">
                @if($penugasan->status_penugasan === 'draft')
                    @can('update', $penugasan)
                    <a href="{{ route('insiden.penugasan.edit', [$insiden, $penugasan]) }}" class="px-4 py-2 bg-yellow-500 text-white text-sm font-medium rounded-lg hover:bg-yellow-600">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                    @endcan
                    @can('delete', $penugasan)
                    <form method="POST" action="{{ route('insiden.penugasan.destroy', [$insiden, $penugasan]) }}" class="inline" onsubmit="return confirm('Hapus penugasan ini?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700">
                            <i class="bi bi-trash"></i> Hapus
                        </button>
                    </form>
                    @endcan
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Info Card --}}
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">Informasi Penugasan</h3>
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-xs text-gray-400 uppercase">Personel</dt>
                        <dd class="text-sm text-gray-800 font-medium">{{ $penugasan->pengguna?->profil?->nama_lengkap ?? $penugasan->pengguna?->no_hp ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-400 uppercase">Peran</dt>
                        <dd><span class="px-2 py-0.5 text-xs font-medium rounded-full bg-blue-100 text-blue-700">{{ ucfirst(str_replace('_', ' ', $penugasan->peran_otoritas)) }}</span></dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-400 uppercase">Status</dt>
                        <dd>
                            @php
                                $sc = ['draft'=>'bg-gray-100 text-gray-600','assigned'=>'bg-yellow-100 text-yellow-700','notified'=>'bg-blue-100 text-blue-700','accepted'=>'bg-teal-100 text-teal-700','on_route'=>'bg-indigo-100 text-indigo-700','on_site'=>'bg-purple-100 text-purple-700','completed'=>'bg-green-100 text-green-700','cancelled'=>'bg-red-100 text-red-700','rejected'=>'bg-orange-100 text-orange-700','aktif'=>'bg-green-100 text-green-700','selesai'=>'bg-gray-100 text-gray-600','dibatalkan'=>'bg-red-100 text-red-700'];
                            @endphp
                            <span class="px-2 py-0.5 text-xs font-medium rounded-full {{ $sc[$penugasan->status_penugasan] ?? 'bg-gray-100 text-gray-600' }}">{{ ucfirst(str_replace('_', ' ', $penugasan->status_penugasan)) }}</span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-400 uppercase">Waktu Mulai</dt>
                        <dd class="text-sm text-gray-800">{{ $penugasan->waktu_mulai ? Carbon\Carbon::parse($penugasan->waktu_mulai)->locale('id')->isoFormat('D MMM YYYY, HH:mm') : '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-400 uppercase">Waktu Selesai</dt>
                        <dd class="text-sm text-gray-800">{{ $penugasan->waktu_selesai ? Carbon\Carbon::parse($penugasan->waktu_selesai)->locale('id')->isoFormat('D MMM YYYY, HH:mm') : '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-400 uppercase">Ditugaskan Oleh</dt>
                        <dd class="text-sm text-gray-800">{{ $penugasan->pemberiTugas?->profil?->nama_lengkap ?? '-' }}</dd>
                    </div>
                    @if($penugasan->catatan)
                    <div class="md:col-span-2">
                        <dt class="text-xs text-gray-400 uppercase">Catatan</dt>
                        <dd class="text-sm text-gray-700">{{ $penugasan->catatan }}</dd>
                    </div>
                    @endif
                </dl>
            </div>

            {{-- Surat Tugas Card --}}
            @if($penugasan->suratTugas)
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">Surat Tugas</h3>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Nomor: <strong>{{ $penugasan->suratTugas->nomor_surat_resmi }}</strong></p>
                        <p class="text-xs text-gray-400">Diterbitkan: {{ $penugasan->suratTugas->tgl_terbit ? Carbon\Carbon::parse($penugasan->suratTugas->tgl_terbit)->locale('id')->isoFormat('D MMM YYYY') : '-' }}</p>
                    </div>
                    @if($penugasan->suratTugas->file_pdf_path)
                    <a href="{{ Storage::disk('public')->url($penugasan->suratTugas->file_pdf_path) }}" target="_blank"
                       class="px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 flex items-center gap-2">
                        <i class="bi bi-download"></i> Download PDF
                    </a>
                    @endif
                </div>
            </div>
            @endif

            {{-- Status Transition Card --}}
            @if(!in_array($penugasan->status_penugasan, ['completed', 'cancelled', 'rejected', 'selesai', 'dibatalkan']))
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">Perbarui Status</h3>
                <div class="flex flex-wrap gap-2">
                    @php
                        $current = $penugasan->status_penugasan;
                        $transitions = [
                            'draft' => ['cancelled' => 'Batalkan'],
                            'assigned' => ['notified' => 'Tandai Notified', 'cancelled' => 'Batalkan'],
                            'notified' => ['accepted' => 'Terima', 'rejected' => 'Tolak', 'cancelled' => 'Batalkan'],
                            'accepted' => ['on_route' => 'Berangkat', 'cancelled' => 'Batalkan'],
                            'on_route' => ['on_site' => 'Tiba di Lokasi', 'cancelled' => 'Batalkan'],
                            'on_site' => ['completed' => 'Selesai'],
                            'aktif' => ['completed' => 'Selesai', 'cancelled' => 'Batalkan'],
                        ];
                        $possible = $transitions[$current] ?? [];
                    @endphp

                    @if($current === 'draft' && $penugasan->suratTugas)
                    <form method="POST" action="{{ route('insiden.penugasan.status', [$insiden, $penugasan]) }}" class="inline">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="accepted">
                        <button type="submit"
                                class="px-4 py-2 bg-teal-600 text-white text-sm font-medium rounded-lg hover:bg-teal-700 flex items-center gap-2">
                            <i class="bi bi-check-circle"></i> Setujui & Buka Rekrutmen
                        </button>
                    </form>
                    @elseif($current === 'draft' && !$penugasan->suratTugas)
                    <form method="POST" action="{{ route('insiden.penugasan.terbitkan-surat-tugas', [$insiden, $penugasan]) }}" class="inline">
                        @csrf
                        <button type="submit"
                                class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 flex items-center gap-2">
                            <i class="bi bi-file-earmark-pdf"></i> Terbitkan Surat Tugas
                        </button>
                    </form>
                    @endif

                    @foreach($possible as $statusKey => $label)
                    <form method="POST" action="{{ route('insiden.penugasan.status', [$insiden, $penugasan]) }}" class="inline">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="{{ $statusKey }}">
                        @if(in_array($statusKey, ['cancelled', 'rejected']))
                        <button type="button" onclick="showReasonForm(this, '{{ $statusKey }}')"
                                class="px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg hover:bg-gray-50">
                            {{ $label }}
                        </button>
                        @else
                        <button type="submit"
                                class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
                            {{ $label }}
                        </button>
                        @endif
                    </form>
                    @endforeach
                </div>

                {{-- Hidden reason form --}}
                <div id="reasonForm" class="hidden mt-4 p-4 bg-gray-50 rounded-lg">
                    <form method="POST" action="{{ route('insiden.penugasan.status', [$insiden, $penugasan]) }}">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" id="reasonStatus">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Alasan</label>
                        <textarea name="catatan" rows="2" required maxlength="500"
                                  class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"></textarea>
                        <div class="flex gap-2 mt-2">
                            <button type="submit" class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700">Konfirmasi</button>
                            <button type="button" onclick="document.getElementById('reasonForm').classList.add('hidden')" class="px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg hover:bg-gray-50">Batal</button>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            {{-- History Card --}}
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">Riwayat Status</h3>
                @if($history->count())
                <div class="space-y-3">
                    @foreach($history as $h)
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 mt-1">
                            <div class="w-2 h-2 rounded-full bg-indigo-400"></div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-700">
                                <span class="font-medium">{{ $h->status_sebelumnya ?? '-' }}</span>
                                <i class="bi bi-arrow-right text-gray-400 text-xs"></i>
                                <span class="font-medium">{{ $h->status_baru }}</span>
                            </p>
                            <p class="text-xs text-gray-400">
                                {{ $h->waktu_perubahan ? Carbon\Carbon::parse($h->waktu_perubahan)->locale('id')->isoFormat('D MMM YYYY, HH:mm') : '-' }}
                            </p>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-sm text-gray-400">Belum ada riwayat.</p>
                @endif
            </div>

            {{-- Back Link --}}
            <div class="text-center">
                <a href="{{ route('insiden.penugasan.index', $insiden) }}" class="text-sm text-gray-500 hover:text-gray-700">
                    <i class="bi bi-arrow-left"></i> Kembali ke daftar penugasan
                </a>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function showReasonForm(btn, status) {
            const form = document.getElementById('reasonForm');
            document.getElementById('reasonStatus').value = status;
            form.classList.remove('hidden');
        }
    </script>
    @endpush
</x-app-layout>
