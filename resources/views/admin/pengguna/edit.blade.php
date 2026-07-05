@extends('layouts.app')

@section('title', 'Edit Pengguna — NURISK')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0">Edit: {{ $pengguna->profil?->nama_lengkap }}</h4>
    <a href="{{ route('admin.pengguna.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.pengguna.update', $pengguna) }}">
            @csrf @method('PUT')
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Status Akun</label>
                <div class="col-sm-9">
                    <select name="status_akun" class="form-select">
                        @foreach(['aktif','nonaktif','suspend'] as $s)
                            <option value="{{ $s }}" {{ $pengguna->status_akun === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Peran</label>
                <div class="col-sm-9">
                    <select name="id_peran" class="form-select">
                        @foreach($roles as $role)
                            <option value="{{ $role->id_peran }}" {{ $pengguna->id_peran === $role->id_peran ? 'selected' : '' }}>{{ $role->nama_peran }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Tersedia</label>
                <div class="col-sm-9">
                    <div class="form-check">
                        <input type="checkbox" name="is_tersedia" class="form-check-input" value="1" id="is_tersedia" {{ $pengguna->is_tersedia ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_tersedia">Ya, tersedia untuk tugas</label>
                    </div>
                </div>
            </div>
            <hr>
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Password Baru</label>
                <div class="col-sm-9">
                    <input type="password" name="kata_sandi" class="form-control" placeholder="Kosongkan jika tidak ingin mengubah">
                </div>
            </div>
            <div class="text-end">
                <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection
