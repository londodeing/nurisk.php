@extends('layouts.app')
@section('title', 'Struktur Organisasi — NURISK')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0">Struktur Organisasi</h4>
</div>
<div class="row">
    @forelse($pcnuList as $pcnu)
    <div class="col-md-6 col-lg-4 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-building me-2"></i>{{ $pcnu->nama_pcnu }}</h5>
                <h6 class="card-subtitle mb-2 text-muted">MWC: {{ $pcnu->mwc->count() }} | Ranting: {{ $pcnu->ranting()->count() }}</h6>
                <ul class="list-unstyled mb-0">
                    @foreach($pcnu->mwc->take(3) as $mwc)
                    <li><i class="bi bi-diagram-2 me-1"></i> {{ $mwc->nama_mwc }}</li>
                    @endforeach
                    @if($pcnu->mwc->count() > 3)
                    <li class="text-muted small">...dan {{ $pcnu->mwc->count() - 3 }} lainnya</li>
                    @endif
                </ul>
            </div>
            <div class="card-footer">
                <a href="{{ route('admin.organisasi.pcnu', $pcnu) }}" class="btn btn-sm btn-outline-primary">Detail</a>
            </div>
        </div>
    </div>
    @empty
    <div class="col"><p class="text-muted">Belum ada data organisasi</p></div>
    @endforelse
</div>
@endsection