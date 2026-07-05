@extends('layouts.public')

@section('title', 'Lapor Kejadian — NURISK')
@section('nav-lapor', 'active')

@push('head')
<style>
    .lapor-layout { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; align-items: start; }
    .lapor-hero { padding: 32px 0 16px; }
    .lapor-hero h1 { font-size: 28px; font-weight: 800; color: #1a1a2e; }
    .lapor-hero p { font-size: 15px; color: #888; margin-top: 6px; line-height: 1.6; }

    .lapor-card {
        background: #fff; border-radius: 14px; padding: 24px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04); border: 1px solid #f0f0f0;
    }
    .lapor-card h3 { font-size: 17px; font-weight: 700; color: #1a1a2e; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }

    .form-group { margin-bottom: 16px; }
    .form-group label { display: block; font-size: 13px; font-weight: 600; color: #555; margin-bottom: 5px; }
    .form-group .form-input {
        width: 100%; padding: 10px 12px;
        border: 1.5px solid #e0e0e0; border-radius: 8px;
        font-size: 13px; transition: all 0.2s; background: #fafafa;
    }
    .form-group .form-input:focus {
        outline: none; border-color: var(--nu-green);
        box-shadow: 0 0 0 3px rgba(21,115,71,0.1); background: #fff;
    }
    .form-group .form-input.error { border-color: #e74c3c; }
    .form-group .error-text { font-size: 12px; color: #e74c3c; margin-top: 4px; }
    .form-group textarea.form-input { min-height: 100px; resize: vertical; }

    .btn {
        padding: 10px 16px; border: none; border-radius: 8px;
        font-size: 13px; font-weight: 600; cursor: pointer;
        transition: all 0.2s; display: inline-flex; align-items: center; gap: 6px;
    }
    .btn-gps { background: #e8f5ee; color: var(--nu-green); border: 1px solid var(--nu-green); }
    .btn-gps:hover { background: var(--nu-green); color: #fff; }
    .btn-gps:disabled { opacity: 0.5; cursor: not-allowed; }
    .btn-gps.loading { pointer-events: none; }
    .btn-gps.btn-gps-success { background: var(--nu-green); color: #fff; border-color: var(--nu-green); }
    .btn-submit {
        width: 100%; padding: 11px; border: none; border-radius: 8px;
        background: linear-gradient(135deg, var(--nu-green), var(--nu-green-dark));
        color: #fff; font-size: 14px; font-weight: 600;
        cursor: pointer; transition: all 0.2s;
    }
    .btn-submit:hover { transform: translateY(-1px); box-shadow: 0 4px 14px rgba(21,115,71,0.25); }
    .btn-submit:disabled { opacity: 0.5; cursor: not-allowed; transform: none; box-shadow: none; }

    .gps-status { font-size: 12px; margin-top: 6px; display: flex; align-items: center; gap: 6px; }
    .gps-status .gps-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
    .gps-status .gps-dot.idle { background: #ccc; }
    .gps-status .gps-dot.searching { background: #f0ad4e; animation: pulse 1s infinite; }
    .gps-status .gps-dot.found { background: #5cb85c; }
    .gps-status .gps-dot.error { background: #e74c3c; }
    @keyframes pulse { 0%,100% { opacity: 1; } 50% { opacity: 0.4; } }

    .alert { padding: 12px 16px; border-radius: 10px; font-size: 13px; margin-bottom: 16px; line-height: 1.5; }
    .alert-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; }
    .alert-error { background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; }

    .login-cta {
        background: #f9fafb; border-radius: 12px; padding: 20px; text-align: center;
        border: 1.5px dashed #ddd; margin-top: 20px;
    }
    .login-cta p { font-size: 13px; color: #888; margin-bottom: 10px; }
    .login-cta .btn-ghost {
        display: inline-block; padding: 8px 24px; border-radius: 8px;
        border: 1.5px solid var(--nu-green); color: var(--nu-green);
        font-weight: 600; font-size: 13px; text-decoration: none; transition: all 0.2s;
    }
    .login-cta .btn-ghost:hover { background: var(--nu-green); color: #fff; }

    .jenis-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 6px; }
    .jenis-option {
        display: flex; align-items: center; gap: 6px; padding: 8px 10px;
        border: 1.5px solid #eee; border-radius: 8px; cursor: pointer;
        font-size: 13px; transition: all 0.2s;
    }
    .jenis-option:hover { border-color: var(--nu-green); background: #fafff8; }
    .jenis-option input[type="radio"] { accent-color: var(--nu-green); margin: 0; }

    .manual-coords {
        display: none; margin-top: 8px; gap: 8px;
    }
    .manual-coords.show { display: flex; }
    .manual-coords input { flex: 1; }

    @media (max-width: 900px) {
        .lapor-layout { grid-template-columns: 1fr; }
        .lapor-hero { padding: 16px 0; }
        .lapor-hero h1 { font-size: 22px; }
        .jenis-grid { grid-template-columns: 1fr 1fr; }
    }
    @media (max-width: 480px) {
        .jenis-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')
<div class="page-container">

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-error">
            <ul style="margin:0;padding-left:16px;">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="lapor-layout">
        <div>
            <div class="lapor-hero">
                <h1>Laporkan Kejadian</h1>
                <p>Laporkan bencana atau keadaan darurat di sekitar Anda. Setiap laporan akan ditindaklanjuti oleh Tim NU Peduli Jawa Tengah.</p>
            </div>

            <div class="lapor-card">
                <h3>Jenis Kejadian yang Dapat Dilaporkan</h3>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;font-size:13px;color:#666;">
                    @foreach ($jenisBencana as $jb)
                        <div>{{ $jb->nama_bencana }}</div>
                    @endforeach
                </div>
                <div style="margin-top:16px;padding:12px 16px;background:#fff9e6;border:1px solid #f0e6c0;border-radius:10px;font-size:13px;color:#8a7a3a;line-height:1.6;">
                    <strong>Darurat?</strong> Hubungi Call Center NU Peduli di <strong>0821-2345-6789</strong> atau hubungi BPBD/PLS setempat.
                </div>
            </div>

            <div class="login-cta">
                <p>Anda relawan atau anggota TRC NU? Laporkan kejadian dengan data lengkap melalui akun Anda.</p>
                <a href="{{ route('login') }}" class="btn-ghost">Masuk ke Akun</a>
                <span style="color:#ccc;margin:0 6px;">atau</span>
                <a href="{{ route('register') }}" class="btn-ghost">Daftar Akun</a>
            </div>
        </div>

        <div class="lapor-card">
            <h3>Form Laporan Publik</h3>
            <p style="font-size:13px;color:#999;margin-bottom:16px;">Isi form di bawah untuk melaporkan kejadian. Tidak perlu login. Lokasi GPS <strong>wajib</strong> diaktifkan.</p>

            <form method="POST" action="{{ route('public.lapor.store') }}" id="formLapor" enctype="multipart/form-data">
                @csrf

                <div class="form-group">
                    <label for="nama">Nama Lengkap *</label>
                    <input type="text" name="nama" id="nama" value="{{ old('nama') }}"
                        placeholder="Nama lengkap pelapor"
                        class="form-input" required>
                    @error('nama') <div class="error-text">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="no_hp">Nomor HP *</label>
                    <input type="tel" name="no_hp" id="no_hp" value="{{ old('no_hp') }}"
                        placeholder="Contoh: 08123456789 (10-13 digit angka)"
                        class="form-input" required
                        pattern="[0-9]*"
                        minlength="10"
                        maxlength="13">
                    <div id="noHpError" class="error-text" style="display:none; font-size:12px; color:#e74c3c; margin-top:4px;">
                        Nomor HP harus berupa angka 10-13 digit (contoh: 08123456789)
                    </div>
                    @error('no_hp') <div class="error-text">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="id_jenis_bencana">Jenis Kejadian *</label>
                    <select name="id_jenis_bencana" id="id_jenis_bencana" class="form-input" required>
                        <option value="">-- Pilih Jenis Kejadian --</option>
                        @foreach($jenisBencana as $jb)
                        <option value="{{ $jb->id_jenis }}" {{ old('id_jenis_bencana') == $jb->id_jenis ? 'selected' : '' }}>
                            {{ $jb->nama_bencana }}
                        </option>
                        @endforeach
                    </select>
                    @error('id_jenis_bencana') <div class="error-text">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="waktu_kejadian">Waktu Kejadian *</label>
                    <input type="datetime-local" name="waktu_kejadian" id="waktu_kejadian"
                        value="{{ old('waktu_kejadian', now()->format('Y-m-d\TH:i')) }}"
                        class="form-input" required>
                    @error('waktu_kejadian') <div class="error-text">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="lokasi">Titik Kenal  *</label>
                    <input type="text" name="lokasi" id="lokasi" value="{{ old('lokasi') }}"
                        placeholder="Contoh: Depan Masjid Al-Akbar, atau nama jalan"
                        class="form-input" required>
                    @error('lokasi') <div class="error-text">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="id_kab">Kabupaten / Kota *</label>
                    <select name="id_kab" id="id_kab" class="form-input" onchange="loadKecamatan()" required>
                        <option value="">-- Pilih Kabupaten/Kota --</option>
                        @foreach($kabupatenList as $kab)
                        <option value="{{ $kab->id_kab }}" {{ old('id_kab') == $kab->id_kab ? 'selected' : '' }}>
                            {{ $kab->nama_kab }}
                        </option>
                        @endforeach
                    </select>
                    @error('id_kab') <div class="error-text">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="id_kec">Kecamatan *</label>
                    <select name="id_kec" id="id_kec" class="form-input" onchange="loadDesa()" required disabled>
                        <option value="">-- Pilih Kecamatan --</option>
                    </select>
                    @error('id_kec') <div class="error-text">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="id_desa">Desa / Kelurahan *</label>
                    <select name="id_desa" id="id_desa" class="form-input" required disabled>
                        <option value="">-- Pilih Desa/Kelurahan --</option>
                    </select>
                    @error('id_desa') <div class="error-text">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <button type="button" id="btnGps" class="btn btn-gps" onclick="ambilLokasi()">
                        Dapatkan Lokasi Saya (wajib)
                    </button>
                    <div class="gps-status" id="gpsStatus">
                        <span class="gps-dot idle" id="gpsDot"></span>
                        <span id="gpsText">Klik tombol GPS untuk mendapatkan lokasi — wajib diisi</span>
                    </div>
                    <input type="hidden" name="latitude" id="inputLat" value="{{ old('latitude') }}">
                    <input type="hidden" name="longitude" id="inputLng" value="{{ old('longitude') }}">

                    <div class="manual-coords" id="manualCoords">
                        <input type="text" name="manual_lat" id="manualLat" placeholder="Latitude (cth: -7.12345678)"
                            class="form-input" oninput="manualCoordChange()">
                        <input type="text" name="manual_lng" id="manualLng" placeholder="Longitude (cth: 110.12345678)"
                            class="form-input" oninput="manualCoordChange()">
                    </div>
                    <div style="font-size:11px;color:#999;margin-top:4px;">
                        GPS gagal? <a href="#" onclick="showManualCoords();return false;">Isi koordinat manual</a>
                    </div>
                </div>

                <div class="form-group">
                    <label for="deskripsi">Deskripsi Kejadian *</label>
                    <textarea name="deskripsi" id="deskripsi" placeholder="Jelaskan kronologi kejadian, dampak, dan bantuan yang dibutuhkan..."
                        class="form-input" required>{{ old('deskripsi') }}</textarea>
                    @error('deskripsi') <div class="error-text">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="foto">Foto Kejadian <span style="color:#e74c3c;">*</span> <span style="font-size:12px;color:#666;">(wajib diisi, akses kamera diperlukan di handphone)</span></label>
                    <input type="file" name="foto" id="foto" accept="image/jpeg,image/png"
                        class="form-input" required>
                    @error('foto') <div class="error-text">{{ $message }}</div> @enderror
                    <div style="font-size:11px;color:#999;margin-top:4px;">Format: JPEG/PNG, maks 10MB</div>
                </div>

                <button type="submit" class="btn-submit" id="btnSubmit">Kirim Laporan</button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const NOMINATIM_URL = 'https://nominatim.openstreetmap.org/reverse';

function setGpsStatus(dotClass, text) {
    document.getElementById('gpsDot').className = 'gps-dot ' + dotClass;
    document.getElementById('gpsText').textContent = text;
}

function showManualCoords() {
    document.getElementById('manualCoords').classList.add('show');
}

function manualCoordChange() {
    var lat = document.getElementById('manualLat').value.trim();
    var lng = document.getElementById('manualLng').value.trim();
    if (lat && lng) {
        document.getElementById('inputLat').value = lat;
        document.getElementById('inputLng').value = lng;
        setGpsStatus('found', 'Koordinat: ' + lat + ', ' + lng);
    }
}

function ambilLokasi() {
    if (!navigator.geolocation) {
        showManualCoords();
        setGpsStatus('error', 'Browser tidak mendukung geolokasi. Isi koordinat manual di bawah.');
        return;
    }

    var btn = document.getElementById('btnGps');
    btn.disabled = true;
    btn.classList.add('loading');
    setGpsStatus('searching', 'Meminta izin lokasi...');

    navigator.geolocation.getCurrentPosition(
        function (pos) {
            var lat = pos.coords.latitude;
            var lng = pos.coords.longitude;
            document.getElementById('inputLat').value = lat;
            document.getElementById('inputLng').value = lng;
            setGpsStatus('searching', 'Mendapatkan alamat dari koordinat...');

            fetch(NOMINATIM_URL + '?format=json&lat=' + lat + '&lon=' + lng + '&zoom=18&accept-language=id', {
                headers: { 'User-Agent': 'NURISK/1.0' }
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                var alamat = data.display_name || '';
                var addr = data.address || {};

                var parts = [];
                if (addr.road) parts.push(addr.road);
                if (addr.house_number) parts.push('No. ' + addr.house_number);
                if (addr.village && addr.village !== addr.city) parts.push(addr.village);
                if (addr.suburb) parts.push(addr.suburb);
                if (addr.city_district) parts.push(addr.city_district);
                if (addr.city) parts.push(addr.city);
                if (addr.county) parts.push(addr.county);

                var hasil = parts.join(', ') || alamat;
                setGpsStatus('found', 'Lokasi GPS (' + lat.toFixed(6) + ', ' + lng.toFixed(6) + '): ' + hasil);
                btn.disabled = false;
                btn.classList.remove('loading');
                btn.classList.add('btn-gps-success');
                btn.textContent = 'Lokasi GPS Aktif';
            })
            .catch(function () {
                setGpsStatus('found', 'Lokasi GPS: ' + lat.toFixed(6) + ', ' + lng.toFixed(6));
                btn.disabled = false;
                btn.classList.remove('loading');
                btn.classList.add('btn-gps-success');
                btn.textContent = 'Lokasi GPS Aktif';
            });
        },
        function (err) {
            var msg = 'Gagal mendapatkan lokasi. ';
            switch (err.code) {
                case err.PERMISSION_DENIED:
                    msg += 'Izin lokasi ditolak. Aktifkan GPS di pengaturan browser, atau isi koordinat manual.';
                    break;
                case err.POSITION_UNAVAILABLE:
                    msg += 'Sinyal GPS tidak tersedia. Coba di luar ruangan, atau isi koordinat manual.';
                    break;
                case err.TIMEOUT:
                    msg += 'Waktu permintaan lokasi habis. Coba lagi, atau isi koordinat manual.';
                    break;
                default:
                    msg += 'Terjadi kesalahan (' + err.code + ').';
            }
            showManualCoords();
            setGpsStatus('error', msg);
            btn.disabled = false;
            btn.classList.remove('loading');
        },
        { enableHighAccuracy: true, timeout: 15000, maximumAge: 60000 }
    );
}

document.getElementById('formLapor').addEventListener('submit', function(e) {
    var lat = document.getElementById('inputLat').value;
    var lng = document.getElementById('inputLng').value;
    if (!lat || !lng) {
        e.preventDefault();
        setGpsStatus('error', 'Lokasi GPS wajib diisi. Klik tombol "Dapatkan Lokasi Saya" atau isi koordinat manual.');
        document.getElementById('lokasi').focus();
    }
    
    // Validate nomor HP
    var noHp = document.getElementById('no_hp').value;
    var noHpError = document.getElementById('noHpError');
    var isValid = /^\d{10,13}$/.test(noHp);
    
    if (!isValid && noHp !== '') {
        e.preventDefault();
        noHpError.style.display = 'block';
        document.getElementById('no_hp').focus();
    }
});

// Real-time validation for nomor HP field
document.getElementById('no_hp').addEventListener('input', function() {
    var noHp = this.value;
    var noHpError = document.getElementById('noHpError');
    var isValid = /^\d{0,13}$/.test(noHp) && (noHp === '' || noHp.length >= 10 || noHp.length <= 13);
    
    if (noHp.length > 0 && noHp.length < 10) {
        noHpError.textContent = 'Nomor HP harus minimal 10 digit';
        noHpError.style.display = 'block';
    } else if (noHp.length > 13) {
        noHpError.textContent = 'Nomor HP maksimal 13 digit';
        noHpError.style.display = 'block';
    } else if (!/^\d*$/.test(noHp)) {
        noHpError.textContent = 'Nomor HP hanya boleh berisi angka';
        noHpError.style.display = 'block';
    } else {
        noHpError.style.display = 'none';
        noHpError.style.display = 'none';
    }
});

function loadKecamatan() {
    var idKab = document.getElementById('id_kab').value;
    var selectKec = document.getElementById('id_kec');
    var selectDesa = document.getElementById('id_desa');
    
    selectKec.innerHTML = '<option value="">-- Memuat Kecamatan... --</option>';
    selectKec.disabled = true;
    selectDesa.innerHTML = '<option value="">-- Pilih Desa/Kelurahan --</option>';
    selectDesa.disabled = true;

    if (!idKab) {
        selectKec.innerHTML = '<option value="">-- Pilih Kecamatan --</option>';
        return;
    }

    fetch('/api/wilayah/kecamatan?id_kab=' + idKab)
        .then(response => response.json())
        .then(data => {
            selectKec.innerHTML = '<option value="">-- Pilih Kecamatan --</option>';
            data.forEach(item => {
                var option = document.createElement('option');
                option.value = item.id_kec;
                option.text = item.nama_kec;
                selectKec.appendChild(option);
            });
            selectKec.disabled = false;
        })
        .catch(error => {
            console.error('Error fetching kecamatan:', error);
            selectKec.innerHTML = '<option value="">-- Gagal Memuat --</option>';
        });
}

function loadDesa() {
    var idKec = document.getElementById('id_kec').value;
    var selectDesa = document.getElementById('id_desa');
    
    selectDesa.innerHTML = '<option value="">-- Memuat Desa... --</option>';
    selectDesa.disabled = true;

    if (!idKec) {
        selectDesa.innerHTML = '<option value="">-- Pilih Desa/Kelurahan --</option>';
        return;
    }

    fetch('/api/wilayah/desa?id_kec=' + idKec)
        .then(response => response.json())
        .then(data => {
            selectDesa.innerHTML = '<option value="">-- Pilih Desa/Kelurahan --</option>';
            data.forEach(item => {
                var option = document.createElement('option');
                option.value = item.id_desa;
                option.text = item.nama_desa;
                selectDesa.appendChild(option);
            });
            selectDesa.disabled = false;
        })
        .catch(error => {
            console.error('Error fetching desa:', error);
            selectDesa.innerHTML = '<option value="">-- Gagal Memuat --</option>';
        });
}
</script>
@endpush
