@extends('layouts.app')
@section('title', 'Detail Aset — NURISK')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0">Aset: {{ $aset->nomor_registrasi }}</h4>
    <a href="{{ route('assets.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
</div>
<div class="card">
    <div class="card-body">
        <dl class="row">
            <dt class="col-sm-3">No. Registrasi</dt><dd class="col-sm-9">{{ $aset->nomor_registrasi }}</dd>
            <dt class="col-sm-3">Jenis</dt><dd class="col-sm-9">{{ $aset->jenis?->nama_jenis ?? '—' }}</dd>
            <dt class="col-sm-3">Kondisi Fisik</dt><dd class="col-sm-9">{{ $aset->kondisi_fisik }}</dd>
            <dt class="col-sm-3">Status</dt><dd class="col-sm-9">{{ $aset->status?->nama_status ?? '—' }}</dd>
            <dt class="col-sm-3">Pemilik</dt><dd class="col-sm-9">{{ $aset->pemilik?->nama_unit ?? '—' }}</dd>
            <dt class="col-sm-3">Posisi</dt><dd class="col-sm-9">{{ $aset->posisi_terakhir ?? '—' }}</dd>
            <dt class="col-sm-3">Kapasitas Angkut</dt><dd class="col-sm-9">{{ $aset->kapasitas_angkut ?? '—' }}</dd>
        </dl>
    </div>
</div>
@endsection