<x-app-layout>
    <x-slot name="header">Approval Registrasi Akun</x-slot>
    <x-slot name="breadcrumb">Home / Administrasi / Approval Registrasi</x-slot>

    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
            <div>
                <h3 class="text-sm font-semibold text-gray-800">Menunggu Persetujuan</h3>
                <p class="text-xs text-gray-500 mt-1">Daftar pengguna yang baru mendaftar dan menunggu verifikasi akses.</p>
            </div>
            @if(count($menunggu) > 0)
                <span class="inline-flex items-center justify-center w-6 h-6 bg-red-100 text-red-600 rounded-full text-xs font-bold">{{ count($menunggu) }}</span>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50 border-b border-gray-100 text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-6 py-3 font-semibold">Pengguna</th>
                        <th class="px-6 py-3 font-semibold">Kontak</th>
                        <th class="px-6 py-3 font-semibold">Peran Diajukan</th>
                        <th class="px-6 py-3 font-semibold">Tanggal Daftar</th>
                        <th class="px-6 py-3 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($menunggu as $calon)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900">{{ $calon->profil->nama_lengkap ?? 'Tanpa Nama' }}</div>
                                <div class="text-xs text-gray-500 mt-0.5">NIK: {{ $calon->profil->nik ?? '-' }}</div>
                                @if(isset($calon->profil) && $calon->profil->desa)
                                <div class="text-xs text-gray-400 mt-0.5">
                                    {{ $calon->profil->desa->nama_desa }}, {{ $calon->profil->desa->kecamatan->nama_kec }}
                                </div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div>{{ $calon->no_hp }}</div>
                                <div class="text-xs text-gray-500">{{ $calon->profil->email ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $jabatanList = $calon->jabatanPosisi->map(fn($j) => $j->jabatan->nama_jabatan)->implode(', ');
                                @endphp
                                <x-badge-status :status="optional($calon->peran)->nama_peran ?? 'unknown'" map="akun" />
                                <div class="text-xs text-gray-500 mt-1">{{ $jabatanList ?: 'Menunggu Role' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div>{{ optional($calon->dibuat_pada)->format('d M Y') ?? '-' }}</div>
                                <div class="text-xs text-gray-400">{{ optional($calon->dibuat_pada)->format('H:i') ?? '-' }} WIB</div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <!-- Button Approve -->
                                    <button type="button"
                                            x-data
                                            @click="$dispatch('open-confirm', {
                                                title: 'Setujui Pengguna',
                                                message: 'Anda yakin ingin memberikan akses aktif untuk pengguna ini?',
                                                action: '{{ route('admin.approval.setujui', $calon->id_pengguna) }}',
                                                method: 'PATCH',
                                                confirmText: 'Ya, Setujui',
                                                confirmColor: 'bg-green-600 hover:bg-green-700 focus:ring-green-500'
                                            })"
                                            class="inline-flex items-center justify-center px-3 py-1.5 rounded-lg bg-green-50 text-green-600 hover:bg-green-100 transition-colors text-xs font-medium"
                                            title="Terima">
                                        <i class="bi bi-check-lg mr-1"></i> Terima
                                    </button>
                                    
                                    <!-- Button Reject -->
                                    <button type="button"
                                            onclick="openTolakModal('{{ $calon->id_pengguna }}', '{{ addslashes($calon->profil?->nama_lengkap ?? 'Pengguna') }}')"
                                            class="inline-flex items-center justify-center px-3 py-1.5 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition-colors text-xs font-medium"
                                            title="Tolak">
                                        <i class="bi bi-x-lg mr-1"></i> Tolak
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center">
                                <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-100 mb-3">
                                    <i class="bi bi-shield-check text-xl text-gray-400"></i>
                                </div>
                                <h3 class="text-sm font-medium text-gray-900">Tidak ada pengajuan</h3>
                                <p class="text-xs text-gray-500 mt-1">Saat ini tidak ada pendaftaran akun baru yang perlu diproses.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($menunggu->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $menunggu->links() }}
            </div>
        @endif
    </div>

    {{-- Modal Tolak --}}
    <div id="tolakModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md mx-4">
            <h3 class="font-semibold text-gray-800 mb-1">Tolak Pendaftaran</h3>
            <p class="text-sm text-gray-500 mb-4" id="tolakNama"></p>
            <form method="POST" action="" id="tolakForm">
                @csrf
                @method('PATCH')
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Alasan Penolakan *</label>
                    <textarea name="alasan" rows="3" required minlength="10" maxlength="500"
                        placeholder="Jelaskan alasan penolakan (min. 10 karakter)"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500"></textarea>
                </div>
                <div class="flex gap-3">
                    <button type="button" onclick="closeTolakModal()"
                        class="flex-1 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50">Batal</button>
                    <button type="submit"
                        class="flex-1 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700">Tolak</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function openTolakModal(id, nama) {
        document.getElementById('tolakNama').textContent = 'Menolak pendaftaran: ' + nama;
        document.getElementById('tolakForm').action = '{{ url('admin/approval') }}/' + id + '/tolak';
        document.getElementById('tolakModal').classList.remove('hidden');
    }

    function closeTolakModal() {
        document.getElementById('tolakModal').classList.add('hidden');
    }

    document.getElementById('tolakModal').addEventListener('click', function(e) {
        if (e.target === this) closeTolakModal();
    });
    </script>
</x-app-layout>
