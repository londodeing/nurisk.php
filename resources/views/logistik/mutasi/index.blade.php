@extends('layouts.app')
@section('title', 'Mutasi Logistik — NURISK')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0">Mutasi Logistik</h4>
    <div>
        <a href="{{ route('logistik.mutasi.masuk') }}" class="btn btn-success btn-sm"><i class="bi bi-box-arrow-in-right"></i> Barang Masuk</a>
        <a href="{{ route('logistik.mutasi.keluar') }}" class="btn btn-danger btn-sm"><i class="bi bi-box-arrow-right"></i> Barang Keluar</a>
        <a href="{{ route('logistik.mutasi.transfer') }}" class="btn btn-info btn-sm"><i class="bi bi-arrow-left-right"></i> Transfer</a>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm table-hover">
                <thead class="table-light">
                    <tr><th>Waktu</th><th>Tipe</th><th>Barang</th><th>Jumlah</th><th>Asal/Tujuan</th><th>Pencatat</th></tr>
                </thead>
                <tbody>
                    @forelse($mutasis as $m)
                    <tr>
                        <td>{{ $m->waktu_mutasi?->format('d/m/Y H:i') }}</td>
                        <td><span class="badge bg-{{ $m->tipe_mutasi === 'masuk' ? 'success' : ($m->tipe_mutasi === 'keluar' ? 'danger' : 'info') }}">{{ ucfirst($m->tipe_mutasi) }}</span></td>
                        <td>{{ $m->stok?->katalog?->nama_barang_standar ?? '—' }}</td>
                        <td>{{ $m->jumlah }}</td>
                        <td>{{ $m->asal_tujuan ?? '—' }}</td>
                        <td>{{ $m->penginput?->profil?->nama_lengkap ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-3">Belum ada mutasi</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $mutasis->links() }}
    </div>
</div>
@endsection