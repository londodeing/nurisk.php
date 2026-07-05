<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-slate-800">Persetujuan Peran Relawan</h2>
        </div>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="bg-green-100 border border-green-200 text-green-700 px-4 py-3 rounded-xl mb-3">{{ session('success') }}</div>
            @endif

            <div class="bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl">
                <div class="px-6 py-4 border-b border-slate-200 bg-white/50">
                    <h5 class="font-semibold text-slate-800">Daftar Aplikasi Tertunda</h5>
                </div>
                <div class="p-0">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">No</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Waktu Pengajuan</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Nama Relawan</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Nomor HP</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Peran Diminta</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Catatan</th>
                                    <th class="px-4 py-2 text-center text-xs font-medium text-slate-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                @forelse($applications as $index => $app)
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="px-4 py-2">{{ $index + 1 }}</td>
                                        <td class="px-4 py-2">{{ \Carbon\Carbon::parse($app->waktu_pengajuan)->format('d/m/Y H:i') }}</td>
                                        <td class="px-4 py-2">
                                            <strong>{{ $app->pengguna->profil->nama_lengkap ?? 'N/A' }}</strong><br>
                                            <small class="text-slate-500">{{ $app->pengguna->profil->nik ?? '' }}</small>
                                        </td>
                                        <td class="px-4 py-2">{{ $app->pengguna->no_hp }}</td>
                                        <td class="px-4 py-2"><span class="px-2 py-1 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-700">{{ strtoupper(str_replace('_', ' ', $app->peranDiminta->nama_peran)) }}</span></td>
                                        <td class="px-4 py-2">{{ $app->catatan ?? '-' }}</td>
                                        <td class="px-4 py-2 text-center">
                                            <div class="flex gap-2 justify-center">
                                                <form action="{{ route('governance.role-approval.approve', $app->id_application) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="px-3 py-1.5 text-xs bg-green-600 text-white rounded-xl hover:bg-green-700 transition-colors" onclick="return confirm('Setujui aplikasi ini?')">
                                                        <i class="fa-solid fa-check"></i> Setujui
                                                    </button>
                                                </form>
                                                <form action="{{ route('governance.role-approval.reject', $app->id_application) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="px-3 py-1.5 text-xs bg-red-500 text-white rounded-xl hover:bg-red-600 transition-colors" onclick="return confirm('Tolak aplikasi ini?')">
                                                        <i class="fa-solid fa-xmark"></i> Tolak
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-slate-500">Belum ada pengajuan peran baru.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
