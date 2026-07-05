@extends('layouts.app')
@section('title', 'Transfer Barang — NURISK')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0">Transfer Barang Antar Pos</h4>
    <a href="{{ route('logistik.mutasi.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
</div>
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('logistik.mutasi.transfer') }}">
            @csrf
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Barang</label>
                <div class="col-sm-9">
                    <select name="id_stok" class="form-select" required>
                        <option value="">— Pilih Barang —</option>
                        @foreach($stoks as $s)
                        <option value="{{ $s->id_stok }}">{{ $s->katalog?->nama_barang_standar }} — Tersedia: {{ $s->jumlah_tersedia }}</option>
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
                <label class="col-sm-3 col-form-label">Dari Pos</label>
                <div class="col-sm-9">
                    <select name="id_posaju_asal" class="form-select">
                        <option value="">— Pilih Pos Asal —</option>
                        @foreach($posajus as $p)
                        <option value="{{ $p->id_posaju }}">{{ $p->nama_posaju }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Ke Pos</label>
                <div class="col-sm-9">
                    <select name="id_posaju_tujuan" class="form-select">
                        <option value="">— Pilih Pos Tujuan —</option>
                        @foreach($posajus as $p)
                        <option value="{{ $p->id_posaju }}">{{ $p->nama_posaju }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="text-end">
                <button type="submit" class="btn btn-info"><i class="bi bi-arrow-left-right"></i> Transfer</button>
            </div>
        </form>
    </div>
</div>
@endsection