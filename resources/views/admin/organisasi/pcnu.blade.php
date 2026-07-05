@extends('layouts.app')
@section('title', 'Detail PCNU — NURISK')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0">{{ $pcnu->nama_pcnu }}</h4>
    <a href="{{ route('admin.organisasi.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
</div>
<div class="row">
    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-header"><strong>Informasi</strong></div>
            <div class="card-body">
                <p class="mb-1">Unit: {{ $pcnu->unit?->nama_unit }}</p>
                <p class="mb-0">Total MWC: {{ $pcnu->mwc->count() }}</p>
                <p class="mb-0">Total Ranting: {{ $pcnu->ranting()->count() }}</p>
            </div>
        </div>
        <div class="card mb-3">
            <div class="card-header"><strong>Statistik Insiden</strong></div>
            <div class="card-body">
                <p class="mb-0">Total Insiden: {{ $pcnu->insiden->count() }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card mb-3">
            <div class="card-header"><strong>MWC & Ranting</strong></div>
            <div class="card-body">
                @foreach($pcnu->mwc as $mwc)
                <div class="mb-2">
                    <strong><i class="bi bi-diagram-2 me-1"></i> {{ $mwc->nama_mwc }}</strong>
                    <ul class="list-unstyled ms-4 mt-1">
                        @forelse($mwc->ranting as $r)
                        <li><i class="bi bi-dot"></i> {{ $r->nama_ranting }}</li>
                        @empty
                        <li class="text-muted small">Belum ada ranting</li>
                        @endforelse
                    </ul>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection