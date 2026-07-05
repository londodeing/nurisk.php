@extends('layouts.app')
@section('title', 'Buka Slot Relawan — NURISK')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0">Buka Slot Kebutuhan Relawan</h4>
    <a href="{{ route('relawan.kebutuhan.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
</div>
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('relawan.kebutuhan.store') }}">
            @csrf
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Judul Posisi</label>
                <div class="col-sm-9">
                    <input type="text" name="judul_posisi" class="form-control" required>
                </div>
            </div>
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Insiden</label>
                <div class="col-sm-9">
                    <select name="id_insiden" class="form-select" required>
                        <option value="">— Pilih Insiden —</option>
                        @foreach($insidenList as $i)
                        <option value="{{ $i->id_insiden }}">{{ $i->kode_kejadian }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Pos Aju</label>
                <div class="col-sm-9">
                    <select name="id_posaju" class="form-select">
                        <option value="">— Pilih Pos —</option>
                    </select>
                </div>
            </div>
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Deskripsi Tugas</label>
                <div class="col-sm-9">
                    <textarea name="deskripsi_tugas" class="form-control" rows="3"></textarea>
                </div>
            </div>
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Jumlah Dibutuhkan</label>
                <div class="col-sm-9">
                    <input type="number" name="jumlah_dibutuhkan" class="form-control" value="1" min="1">
                </div>
            </div>
            <div class="text-end">
                <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Buka Slot</button>
            </div>
        </form>
    </div>
</div>
@endsection