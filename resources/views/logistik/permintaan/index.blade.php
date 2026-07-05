@extends('layouts.app')
@section('title', 'Permintaan Logistik — NURISK')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0">Permintaan Logistik</h4>
</div>
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm table-hover">
                <thead class="table-light">
                    <tr><th>Pos Tujuan</th><th>Klaster</th><th>Prioritas</th><th>Status</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    @forelse($permintaan as $p)
                    <tr>
                        <td>{{ $p->posaju?->nama_posaju ?? '—' }}</td>
                        <td>{{ $p->klaster?->masterKlaster?->nama_klaster ?? '—' }}</td>
                        <td><span class="badge bg-{{ $p->prioritas === 'tinggi' ? 'danger' : ($p->prioritas === 'sedang' ? 'warning' : 'info') }}">{{ ucfirst($p->prioritas) }}</span></td>
                        <td>{{ $p->status_permintaan }}</td>
                        <td>
                            @if($p->status_permintaan === 'pending')
                            <form method="POST" action="{{ route('logistik.permintaan.setujui', $p) }}" class="d-inline">
                                @csrf @method('PATCH')
                                <button class="btn btn-sm btn-success">Setujui</button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center py-3">Belum ada permintaan</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $permintaan->links() }}
    </div>
</div>
@endsection