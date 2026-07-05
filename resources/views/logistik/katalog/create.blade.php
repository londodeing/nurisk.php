@extends('layouts.app')
@section('title', 'Tambah Barang — NURISK')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0">Tambah Barang Baru</h4>
    <a href="{{ route('logistik.katalog.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
</div>
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('logistik.katalog.store') }}">
            @csrf
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Nama Barang</label>
                <div class="col-sm-9">
                    <input type="text" name="nama_barang_standar" class="form-control" required>
                </div>
            </div>
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Kategori</label>
                <div class="col-sm-9">
                    <select name="id_kategori" class="form-select">
                        <option value="">— Pilih Kategori —</option>
                        @foreach($kategoris as $kat)
                        <option value="{{ $kat->id_kategori }}">{{ $kat->nama_kategori }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Satuan</label>
                <div class="col-sm-9">
                    <input type="text" name="satuan" class="form-control" placeholder="kg, pcs, dus, liter, ...">
                </div>
            </div>
            <div class="text-end">
                <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection