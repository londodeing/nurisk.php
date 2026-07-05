<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pengajuan Peran - NURISK PWNU Jateng</title>
    <!-- Google Fonts: Outfit -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Outfit', sans-serif; background-color: #f4f6f9; }
        .card { border-radius: 12px; border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .btn-success { background-color: #157347; border: none; }
    </style>
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="text-center mb-4">
                <h2 class="fw-bold text-success">NURISK</h2>
                <p class="text-muted">Pilih peran dan bergabunglah bersama kami.</p>
            </div>

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="card p-4">
                @if ($pendingApplication)
                    <div class="alert alert-info">
                        <h5><i class="fa-solid fa-clock me-2"></i>Aplikasi Menunggu Persetujuan</h5>
                        <p>Anda telah mengajukan peran <strong>{{ $pendingApplication->peranDiminta->nama_peran }}</strong>. Silakan tunggu persetujuan dari administrator PCNU wilayah Anda.</p>
                    </div>
                @else
                    <h4 class="mb-4">Pengajuan Peran</h4>
                    <form method="POST" action="{{ route('role-application.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="id_peran_diminta" class="form-label">Pilih Peran yang Diinginkan</label>
                            <select name="id_peran_diminta" id="id_peran_diminta" class="form-select @error('id_peran_diminta') is-invalid @enderror" required>
                                <option value="" disabled selected>Pilih Peran...</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id_peran }}">{{ ucfirst(str_replace('_', ' ', $role->nama_peran)) }}</option>
                                @endforeach
                            </select>
                            @error('id_peran_diminta') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-4">
                            <label for="catatan" class="form-label">Catatan Tambahan (Opsional)</label>
                            <textarea name="catatan" id="catatan" class="form-control" rows="3" placeholder="Sebutkan pengalaman atau alasan Anda memilih peran ini..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-success w-100">Ajukan Peran</button>
                    </form>
                @endif
                <div class="mt-4 text-center">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-link text-danger">Keluar (Logout)</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
