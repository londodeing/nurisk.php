@extends('layouts.app')
@section('title', 'Pendaftaran Relawan — NURISK')
@section('content')
<h4 class="mb-3">Pendaftaran Relawan</h4>
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm table-hover">
                <thead class="table-light">
                    <tr><th>Relawan</th><th>Posisi</th><th>Status</th><th>Daftar</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    @forelse($pendaftarans as $p)
                    <tr>
                        <td>{{ $p->pengguna?->profil?->nama_lengkap ?? '—' }}</td>
                        <td>{{ $p->kebutuhan?->judul_posisi ?? '—' }}</td>
                        <td><span class="badge bg-{{ $p->status_pendaftaran === 'diterima' ? 'success' : ($p->status_pendaftaran === 'ditolak' ? 'danger' : 'warning') }}">{{ $p->status_pendaftaran }}</span></td>
                        <td>{{ $p->dibuat_pada?->diffForHumans() }}</td>
                        <td>
                            @if($p->status_pendaftaran === 'dibuka' || $p->status_pendaftaran === 'seleksi')
                            <form method="POST" action="{{ route('relawan.pendaftaran.terima', $p) }}" class="d-inline">
                                @csrf @method('PATCH')
                                <button class="btn btn-sm btn-success">Terima</button>
                            </form>
                            <form method="POST" action="{{ route('relawan.pendaftaran.tolak', $p) }}" class="d-inline">
                                @csrf @method('PATCH')
                                <button class="btn btn-sm btn-danger">Tolak</button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center py-3">Belum ada pendaftaran</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $pendaftarans->links() }}
    </div>
</div>
@endsection