<x-app-layout>
    <x-slot name="header">Manajemen Pengguna</x-slot>
    <x-slot name="breadcrumb"><a href="{{ route('dashboard.pwnu') }}" class="text-gray-500 hover:text-gray-700">Home</a> <span class="text-gray-400 mx-1">/</span> Pengguna</x-slot>
    <x-slot name="actions">
        <a href="{{ route('admin.approval.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-yellow-600 text-white text-sm font-medium rounded-lg hover:bg-yellow-700">
            <i class="bi bi-clock-history"></i> Antrian Approval
        </a>
    </x-slot>

    <div class="space-y-4">
        <form method="GET" class="bg-white rounded-xl border border-gray-200 p-4 flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Cari</label>
                <input type="text" name="search" value="{{ request('search') }}" class="rounded-lg border-gray-300 text-sm focus:border-green-500 focus:ring-primary-500" placeholder="Nama / No HP...">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                <select name="status_akun" class="rounded-lg border-gray-300 text-sm focus:border-green-500 focus:ring-primary-500">
                    <option value="">Semua Status</option>
                    <option value="aktif" {{ request('status_akun') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="menunggu" {{ request('status_akun') === 'menunggu' ? 'selected' : '' }}>Menunggu</option>
                    <option value="nonaktif" {{ request('status_akun') === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                    <option value="suspend" {{ request('status_akun') === 'suspend' ? 'selected' : '' }}>Suspend</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Role</label>
                <select name="id_peran" class="rounded-lg border-gray-300 text-sm focus:border-green-500 focus:ring-primary-500">
                    <option value="">Semua Role</option>
                    @foreach($rolesList ?? [] as $role)
                    <option value="{{ $role->id_peran }}" {{ request('id_peran') == $role->id_peran ? 'selected' : '' }}>{{ $role->nama_peran }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-gray-800 text-white text-sm rounded-lg hover:bg-gray-700">Filter</button>
            <a href="{{ route('admin.pengguna.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Reset</a>
        </form>

        <x-data-table :empty="$penggunas->isEmpty()" emptyMessage="Belum ada pengguna." emptyIcon="👥">
            <x-slot name="head">
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Nama</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">No HP</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Role</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Jabatan</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Status</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Terdaftar</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Aksi</th>
            </x-slot>
            <x-slot name="body">
                @foreach($penggunas as $user)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-3 text-sm font-medium text-gray-900">
                        <a href="{{ route('admin.pengguna.show', $user) }}" class="text-green-600 hover:text-green-800 hover:underline">
                            {{ $user->profil->nama_lengkap ?? '(belum isi profil)' }}
                        </a>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $user->no_hp }}</td>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ $user->peran?->nama_peran ?? '-' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">
                        @forelse($user->jabatanPosisi as $jp)
                            <div class="flex items-center gap-1.5 mb-1 flex-wrap">
                                <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-blue-50 text-blue-700">
                                    {{ $jp->jabatan?->nama_jabatan ?? '-' }}
                                </span>
                                @if(!$jp->status_aktif)
                                    <span class="text-[10px] bg-yellow-100 text-yellow-800 px-1.5 py-0.5 rounded font-medium">Menunggu</span>
                                @else
                                    <span class="text-[10px] bg-green-100 text-green-800 px-1.5 py-0.5 rounded font-medium">Aktif</span>
                                @endif
                            </div>
                        @empty
                            -
                        @endforelse
                    </td>
                    <td class="px-4 py-3 text-center">
                        <x-badge-status :status="$user->status_akun" map="akun" />
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">
                        {{ $user->dibuat_pada ? \Carbon\Carbon::parse($user->dibuat_pada)->locale('id')->isoFormat('D MMM YYYY') : '-' }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center gap-2">
                            @can('update', $user)
                                <a href="{{ route('admin.pengguna.show', $user) }}" class="text-xs px-2 py-1 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 font-medium">Detail</a>
                                <a href="{{ route('admin.pengguna.edit', $user) }}" class="text-xs px-2 py-1 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-medium">Edit</a>
                                @if($user->status_akun === 'menunggu')
                                <form action="{{ route('admin.pengguna.setujui', $user) }}" method="POST" class="inline">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="text-xs px-2 py-1 bg-green-100 text-green-700 rounded-lg hover:bg-green-200 font-medium">Setujui</button>
                                </form>
                                <form action="{{ route('admin.pengguna.tolak', $user) }}" method="POST" class="inline">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="text-xs px-2 py-1 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 font-medium">Tolak</button>
                                </form>
                                @elseif($user->status_akun === 'aktif')
                                <form action="{{ route('admin.pengguna.tolak', $user) }}" method="POST" class="inline">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="text-xs px-2 py-1 bg-yellow-100 text-yellow-700 rounded-lg hover:bg-yellow-200 font-medium">Suspend</button>
                                </form>
                                @elseif($user->status_akun === 'suspend')
                                <form action="{{ route('admin.pengguna.setujui', $user) }}" method="POST" class="inline">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="text-xs px-2 py-1 bg-green-100 text-green-700 rounded-lg hover:bg-green-200 font-medium">Aktifkan</button>
                                </form>
                                @endif
                            @endcan
                        </div>
                    </td>
                </tr>
                @endforeach
            </x-slot>
            <x-slot name="footer">
                {{ $penggunas->links() }}
            </x-slot>
        </x-data-table>
    </div>
</x-app-layout>
