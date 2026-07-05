@extends('layouts.app')
@section('title', 'Katalog Barang — NURISK')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0">Katalog Barang</h4>
    <a href="{{ route('logistik.katalog.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Barang Baru</a>
</div>
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm table-hover">
                <thead class="table-light">
                    <tr><th>Nama Barang</th><th>Kategori</th><th>Satuan</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    @forelse($katalogs as $k)
                    <tr>
                        <td>{{ $k->nama_barang_standar }}</td>
                        <td>{{ $k->kategori?->nama_kategori ?? '—' }}</td>
                        <td>{{ $k->satuan ?? 'unit' }}</td>
                        <td><a href="#" class="btn btn-sm btn-outline-info"><i class="bi bi-eye"></i></a></td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center py-3">Belum ada katalog barang</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $katalogs->links() }}
    </div>
</div>
@endsection