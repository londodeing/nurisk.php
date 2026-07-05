@extends('layouts.app')
@section('title', 'Barang Keluar — NURISK')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0">Catat Barang Keluar</h4>
    <a href="{{ route('logistik.mutasi.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
</div>
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('logistik.mutasi.store') }}">
            @csrf
            <input type="hidden" name="tipe_mutasi" value="keluar">
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Barang</label>
                <div class="col-sm-9">
                    <select name="id_stok" class="form-select" required>
                        <option value="">— Pilih Barang —</option>
                        @foreach($stoks as $s)
                        <option value="{{ $s->id_stok }}">{{ $s->katalog?->nama_barang_standar }} ({{ $s->posaju?->nama_posaju ?? 'Gudang' }} — tersedia: {{ $s->jumlah_tersedia }})</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Jumlah</label>
                <div class="col-sm-9">
                    <input type="number" name="jumlah" class="form-control" step="0.01" required>
                </div>
            </div>
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Tujuan / Penerima</label>
                <div class="col-sm-9">
                    <input type="text" name="asal_tujuan" class="form-control" placeholder="Pos Aju / Pengungsi / ...">
                </div>
            </div>
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Keterangan</label>
                <div class="col-sm-9">
                    <textarea name="keterangan" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="text-end">
                <button type="submit" class="btn btn-danger"><i class="bi bi-save"></i> Catat</button>
            </div>
        </form>
    </div>
</div>
@endsection