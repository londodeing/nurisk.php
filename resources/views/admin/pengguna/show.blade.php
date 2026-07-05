@extends('layouts.app')

@section('title', 'Detail Pengguna — NURISK')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0">Detail Pengguna</h4>
    <a href="{{ route('admin.pengguna.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-body text-center">
                <div class="display-6 mb-2"><i class="bi bi-person-circle"></i></div>
                <h5>{{ $pengguna->profil?->nama_lengkap }}</h5>
                <p class="text-muted mb-1">{{ $pengguna->peran?->nama_peran }}</p>
                @if($pengguna->status_akun === 'aktif')
                    <span class="badge bg-success">Aktif</span>
                @elseif($pengguna->status_akun === 'menunggu')
                    <span class="badge bg-warning text-dark">Menunggu</span>
                @else
                    <span class="badge bg-secondary">{{ $pengguna->status_akun }}</span>
                @endif
            </div>
        </div>
        <div class="card mb-3">
            <div class="card-header"><strong>Ketersediaan</strong></div>
            <div class="card-body">
                <p class="mb-1">Status: {!! $pengguna->is_tersedia ? '<span class="text-success">Tersedia</span>' : '<span class="text-danger">Tidak Tersedia</span>' !!}</p>
                <p class="mb-0">Terakhir Masuk: {{ $pengguna->terakhir_masuk?->format('d/m/Y H:i') ?? '—' }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card mb-3">
            <div class="card-header"><strong>Profil</strong></div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">NIK</dt>
                    <dd class="col-sm-8">{{ $pengguna->profil?->nik ?? '—' }}</dd>
                    <dt class="col-sm-4">Email</dt>
                    <dd class="col-sm-8">{{ $pengguna->profil?->email ?? '—' }}</dd>
                    <dt class="col-sm-4">Tempat, Tgl Lahir</dt>
                    <dd class="col-sm-8">{{ $pengguna->profil?->tempat_lahir }}, {{ $pengguna->profil?->tanggal_lahir?->format('d/m/Y') }}</dd>
                    <dt class="col-sm-4">Jenis Kelamin</dt>
                    <dd class="col-sm-8">{{ $pengguna->profil?->jenis_kelamin === 'L' ? 'Laki-laki' : ($pengguna->profil?->jenis_kelamin === 'P' ? 'Perempuan' : '—') }}</dd>
                    <dt class="col-sm-4">Alamat</dt>
                    <dd class="col-sm-8">{{ $pengguna->profil?->alamat ?? '—' }}</dd>
                    <dt class="col-sm-4">Profesi</dt>
                    <dd class="col-sm-8">{{ $pengguna->profil?->profesi ?? '—' }}</dd>
                </dl>
            </div>
        </div>
        <div class="card mb-3">
            <div class="card-header"><strong>Keahlian</strong></div>
            <div class="card-body">
                @forelse($pengguna->keahlian as $k)
                    <span class="badge bg-info me-1">{{ $k->nama_keahlian }}</span>
                @empty
                    <span class="text-muted">Belum ada keahlian</span>
                @endforelse
            </div>
        </div>
        <div class="card mb-3">
            <div class="card-header"><strong>Riwayat & Penugasan Jabatan</strong></div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    @forelse($pengguna->jabatanPosisi as $j)
                        <li class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                            <div>
                                <i class="bi bi-briefcase me-2 text-primary"></i>
                                <strong>{{ $j->jabatan?->nama_jabatan }}</strong>
                                <span class="ms-2 badge {{ $j->status_aktif ? 'bg-success' : 'bg-warning text-dark' }}">
                                    {{ $j->status_aktif ? 'Aktif' : 'Menunggu Persetujuan' }}
                                </span>
                                <div class="text-xs text-muted ms-4">
                                    Lingkup: {{ strtoupper($j->tipe_lingkup) }} (ID: {{ $j->id_lingkup }})
                                </div>
                            </div>
                            @can('update', $pengguna)
                                <form action="{{ route('admin.pengguna-jabatan.toggle', $j) }}" method="POST" class="d-inline">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn-xs {{ $j->status_aktif ? 'btn-outline-danger' : 'btn-outline-success' }}">
                                        {{ $j->status_aktif ? 'Deaktivasi' : 'Aktivasi/Setujui' }}
                                    </button>
                                </form>
                            @endcan
                        </li>
                    @empty
                        <li class="text-muted">Belum memiliki pengajuan jabatan</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
