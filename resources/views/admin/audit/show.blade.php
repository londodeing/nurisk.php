@extends('layouts.app')
@section('title', 'Detail Audit — NURISK')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0">Audit: {{ $log->tabel }} #{{ $log->id_record }}</h4>
    <a href="{{ route('admin.audit.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
</div>
<div class="row">
    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-header"><strong>Informasi</strong></div>
            <div class="card-body">
                <dl class="row mb-0 small">
                    <dt class="col-sm-5">Waktu</dt><dd class="col-sm-7">{{ $log->waktu?->format('d/m/Y H:i:s') }}</dd>
                    <dt class="col-sm-5">User</dt><dd class="col-sm-7">{{ $log->pengguna?->profil?->nama_lengkap ?? $log->id_pengguna }}</dd>
                    <dt class="col-sm-5">Aksi</dt><dd class="col-sm-7"><span class="badge bg-info">{{ $log->aksi }}</span></dd>
                    <dt class="col-sm-5">IP</dt><dd class="col-sm-7">{{ $log->ip_address }}</dd>
                    <dt class="col-sm-5">Endpoint</dt><dd class="col-sm-7">{{ $log->endpoint }}</dd>
                </dl>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card mb-3">
            <div class="card-header"><strong>Data Sebelum</strong></div>
            <div class="card-body">
                <pre class="mb-0" style="max-height:300px;overflow:auto;">{{ json_encode($log->data_lama, JSON_PRETTY_PRINT) }}</pre>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><strong>Data Sesudah</strong></div>
            <div class="card-body">
                <pre class="mb-0" style="max-height:300px;overflow:auto;">{{ json_encode($log->data_baru, JSON_PRETTY_PRINT) }}</pre>
            </div>
        </div>
    </div>
</div>
@endsection