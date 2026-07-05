@extends('layouts.app')
@section('title', 'Kebutuhan Relawan — NURISK')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0">Kebutuhan Relawan</h4>
    <a href="{{ route('relawan.kebutuhan.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Buka Slot</a>
</div>
<div class="row">
    @forelse($kebutuhans as $k)
    <div class="col-md-6 col-lg-4 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <h6>{{ $k->judul_posisi }}</h6>
                <p class="small text-muted mb-1">{{ $k->insiden?->kode_kejadian }} — {{ $k->posaju?->nama_posaju ?? '—' }}</p>
                <p class="small mb-2">{{ $k->deskripsi_tugas }}</p>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="badge bg-info">{{ $k->sisaSlot() }}/{{ $k->jumlah_dibutuhkan }} tersisa</span>
                    <span class="badge bg-{{ $k->status_kebutuhan === 'dibuka' ? 'success' : 'secondary' }}">{{ $k->status_kebutuhan }}</span>
                </div>
            </div>
            <div class="card-footer">
                <a href="#" class="btn btn-sm btn-outline-primary">Lihat Pendaftar</a>
            </div>
        </div>
    </div>
    @empty
    <div class="col"><p class="text-muted">Belum ada kebutuhan relawan</p></div>
    @endforelse
</div>
@endsection