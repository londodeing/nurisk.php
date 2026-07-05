@extends('layouts.app')
@section('title', 'Daftar Aset — NURISK')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0">Manajemen Aset</h4>
    <a href="{{ route('assets.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Aset Baru</a>
</div>
<div class="card">
    <div class="card-body">
        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-3">
                <select name="kondisi_fisik" class="form-select form-select-sm">
                    <option value="">Semua Kondisi</option>
                    <option value="baik" {{ request('kondisi_fisik') === 'baik' ? 'selected' : '' }}>Baik</option>
                    <option value="rusak_ringan" {{ request('kondisi_fisik') === 'rusak_ringan' ? 'selected' : '' }}>Rusak Ringan</option>
                    <option value="rusak_berat" {{ request('kondisi_fisik') === 'rusak_berat' ? 'selected' : '' }}>Rusak Berat</option>
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-sm btn-primary" type="submit">Filter</button>
            </div>
        </form>
        <div class="table-responsive">
            <table class="table table-sm table-hover">
                <thead class="table-light">
                    <tr><th>No. Registrasi</th><th>Jenis</th><th>Kondisi</th><th>Status</th><th>Posisi</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    @forelse($asets as $a)
                    <tr>
                        <td>{{ $a->nomor_registrasi }}</td>
                        <td>{{ $a->jenis?->nama_jenis ?? '—' }}</td>
                        <td><span class="badge bg-{{ $a->kondisi_fisik === 'baik' ? 'success' : ($a->kondisi_fisik === 'rusak_ringan' ? 'warning' : 'danger') }}">{{ str_replace('_', ' ', $a->kondisi_fisik) }}</span></td>
                        <td>{{ $a->status?->nama_status ?? '—' }}</td>
                        <td>{{ $a->posisi_terakhir ?? '—' }}</td>
                        <td><a href="{{ route('assets.show', $a) }}" class="btn btn-sm btn-outline-info"><i class="bi bi-eye"></i></a></td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-3">Belum ada aset</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $asets->links() }}
    </div>
</div>
@endsection