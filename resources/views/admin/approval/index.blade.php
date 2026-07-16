<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Persetujuan Pendaftaran — NURISK</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">

<div class="max-w-5xl mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Persetujuan Pendaftaran</h1>
            <p class="text-sm text-gray-500 mt-1">Daftar pengguna yang menunggu verifikasi akun</p>
        </div>
        <a href="{{ route('dashboard') }}" class="text-sm text-green-700 hover:underline">← Dashboard</a>
    </div>

    @if (session('success'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if ($menunggu->isEmpty())
        <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
            <div class="text-4xl mb-3">✅</div>
            <h3 class="text-lg font-semibold text-gray-700">Tidak Ada Pendaftaran Menunggu</h3>
            <p class="text-sm text-gray-500 mt-1">Semua pendaftar sudah diproses.</p>
        </div>
    @else
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-left text-gray-600 font-medium">
                        <th class="px-4 py-3">Nama</th>
                        <th class="px-4 py-3">No. HP</th>
                        <th class="px-4 py-3">Jenis Akun</th>
                        <th class="px-4 py-3">PCNU</th>
                        <th class="px-4 py-3">Tanggal Daftar</th>
                        <th class="px-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($menunggu as $calon)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">
                            {{ $calon->profil?->nama_lengkap ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $calon->no_hp }}</td>
                        <td class="px-4 py-3">
                            @php
                                $slugJabatan = $calon->jabatanPosisi->first()?->jabatan?->slug ?? '';
                            @endphp
                            <span @class([
                                'inline-block text-xs font-medium px-2 py-0.5 rounded-full',
                                'bg-blue-100 text-blue-700' => str_contains($slugJabatan, 'trc-pcnu'),
                                'bg-orange-100 text-orange-700' => str_contains($slugJabatan, 'trc-pwnu'),
                                'bg-purple-100 text-purple-700' => $slugJabatan === 'admin-pcnu',
                                'bg-red-100 text-red-700' => $slugJabatan === 'admin-pwnu',
                            ])>
                                {{ $calon->jabatanPosisi->first()?->jabatan?->nama_jabatan ?? $calon->peran?->nama_peran }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-600">
                            {{ $calon->default_scope_type === 'pcnu' ? 'PCNU #' . $calon->default_scope_id : ($calon->default_scope_type ?? '—') }}
                        </td>
                        <td class="px-4 py-3 text-gray-500 text-xs">
                            {{ $calon->dibuat_pada ? $calon->dibuat_pada->format('d M Y H:i') : '—' }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex gap-2">
                                <form method="POST" action="{{ route('admin.approval.setujui', $calon) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                        class="px-3 py-1.5 bg-primary-600 text-white text-xs font-medium rounded-lg hover:bg-primary-700 transition-colors">
                                        ✓ Setujui
                                    </button>
                                </form>
                                <button type="button"
                                    onclick="openTolakModal('{{ $calon->id_pengguna }}', '{{ $calon->profil?->nama_lengkap ?? 'Pengguna' }}')"
                                    class="px-3 py-1.5 border border-red-300 text-red-600 text-xs font-medium rounded-lg hover:bg-red-50 transition-colors">
                                    ✗ Tolak
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
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
                    class="flex-1 py-2 border border-gray-300 rounded-lg text-sm">Batal</button>
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
</body>
</html>
