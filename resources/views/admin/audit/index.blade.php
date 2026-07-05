@extends('layouts.app')
@section('title', 'Audit Log — NURISK')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0">Audit Log</h4>
</div>
<div class="card">
    <div class="card-body">
        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-3">
                <input type="text" name="tabel" class="form-control form-control-sm" placeholder="Filter tabel..." value="{{ request('tabel') }}">
            </div>
            <div class="col-md-2">
                <select name="aksi" class="form-select form-select-sm">
                    <option value="">Semua Aksi</option>
                    <option value="INSERT" {{ request('aksi') === 'INSERT' ? 'selected' : '' }}>INSERT</option>
                    <option value="UPDATE" {{ request('aksi') === 'UPDATE' ? 'selected' : '' }}>UPDATE</option>
                    <option value="DELETE" {{ request('aksi') === 'DELETE' ? 'selected' : '' }}>DELETE</option>
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-sm btn-primary" type="submit"><i class="bi bi-search"></i> Filter</button>
            </div>
        </form>
        <div class="table-responsive">
            <table class="table table-sm table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Waktu</th>
                        <th>User</th>
                        <th>Tabel</th>
                        <th>ID Record</th>
                        <th>Aksi</th>
                        <th>IP</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        <td>{{ $log->waktu?->format('d/m/Y H:i:s') }}</td>
                        <td>{{ $log->pengguna?->profil?->nama_lengkap ?? $log->id_pengguna }}</td>
                        <td>{{ $log->tabel }}</td>
                        <td>{{ $log->id_record }}</td>
                        <td><span class="badge bg-{{ $log->aksi === 'INSERT' ? 'success' : ($log->aksi === 'DELETE' ? 'danger' : 'warning') }}">{{ $log->aksi }}</span></td>
                        <td>{{ $log->ip_address }}</td>
                        <td>
                            <a href="{{ route('admin.audit.show', $log) }}" class="btn btn-sm btn-outline-info"><i class="bi bi-eye"></i> Diff</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted py-3">Belum ada data audit</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $logs->links() }}
    </div>
</div>
@endsection