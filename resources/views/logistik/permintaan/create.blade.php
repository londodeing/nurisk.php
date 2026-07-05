@extends('layouts.app')
@section('title', 'Ajukan Permintaan — NURISK')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0">Ajukan Permintaan Logistik</h4>
    <a href="{{ route('logistik.permintaan.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
</div>
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('logistik.permintaan.store') }}">
            @csrf
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Pos Tujuan</label>
                <div class="col-sm-9">
                    <select name="id_posaju_tujuan" class="form-select" required>
                        <option value="">— Pilih Pos —</option>
                        @foreach($posajus as $p)
                        <option value="{{ $p->id_posaju }}">{{ $p->nama_posaju }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Prioritas</label>
                <div class="col-sm-9">
                    <select name="prioritas" class="form-select">
                        <option value="rendah">Rendah</option>
                        <option value="sedang" selected>Sedang</option>
                        <option value="tinggi">Tinggi</option>
                    </select>
                </div>
            </div>
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Keterangan</label>
                <div class="col-sm-9">
                    <textarea name="keterangan" class="form-control" rows="3"></textarea>
                </div>
            </div>
            <div class="text-end">
                <button type="submit" class="btn btn-primary"><i class="bi bi-send"></i> Ajukan</button>
            </div>
        </form>
    </div>
</div>
@endsection