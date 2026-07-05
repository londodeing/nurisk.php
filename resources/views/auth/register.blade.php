@extends('layouts.public')

@section('title', 'Pendaftaran — NURISK')
@section('layout-class', 'full-width')

@push('head')
<style>
    .auth-split {
        display: flex; min-height: calc(100vh - var(--header-height));
        margin: calc(-24px - 32px); margin-top: calc(-24px);
    }
    .auth-hero {
        flex: 1; display: flex; flex-direction: column;
        justify-content: center; padding: 48px 56px;
        background: linear-gradient(135deg, #157347 0%, #0d4f2e 100%);
        color: #fff; position: relative; overflow: hidden;
    }
    .auth-hero::before {
        content: ''; position: absolute; top: -120px; right: -120px;
        width: 400px; height: 400px;
        background: rgba(255,255,255,0.04); border-radius: 50%;
    }
    .auth-hero h1 { font-size: 28px; font-weight: 800; margin-bottom: 6px; }
    .auth-hero p { font-size: 14px; opacity: 0.8; line-height: 1.6; max-width: 380px; }
    .auth-hero .hero-icon { font-size: 48px; margin-bottom: 16px; }
    .auth-hero .hero-steps { margin-top: 28px; display: flex; flex-direction: column; gap: 12px; }
    .auth-hero .hero-step { display: flex; align-items: center; gap: 12px; font-size: 13px; opacity: 0.8; }
    .auth-hero .hero-step .hs-num {
        width: 24px; height: 24px; border-radius: 50%;
        background: rgba(255,255,255,0.15); display: flex;
        align-items: center; justify-content: center;
        font-size: 12px; font-weight: 700; flex-shrink: 0;
    }

    .auth-form-wrap {
        flex: 0 0 520px; display: flex; flex-direction: column;
        justify-content: center; padding: 32px 48px;
        background: #fff; overflow-y: auto;
    }
    .auth-form-wrap .form-header { margin-bottom: 20px; }
    .auth-form-wrap .form-header h2 { font-size: 20px; font-weight: 700; color: #1a1a2e; }
    .auth-form-wrap .form-header p { font-size: 13px; color: #999; margin-top: 2px; }

    .progress-bar { display: flex; align-items: center; margin-bottom: 24px; }
    .progress-step { display: flex; align-items: center; flex: 1; }
    .progress-dot {
        width: 30px; height: 30px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 12px; font-weight: 700; flex-shrink: 0; transition: all 0.3s;
    }
    .progress-dot.done { background: var(--nu-green); color: #fff; }
    .progress-dot.current { border: 2px solid var(--nu-green); color: var(--nu-green); background: #fff; }
    .progress-dot.pending { background: #e0e0e0; color: #999; }
    .progress-dot.error { border: 2px solid #e74c3c; color: #e74c3c; background: #fff; }
    .progress-line {
        flex: 1; height: 3px; margin: 0 8px; border-radius: 2px; transition: all 0.3s;
    }
    .progress-line.done { background: var(--nu-green); }
    .progress-line.pending { background: #e0e0e0; }

    .step-content { display: none; }
    .step-content.active { display: block; }

    .form-grid { display: grid; gap: 16px; }
    .form-grid-2 { grid-template-columns: 1fr 1fr; }
    .form-group { margin-bottom: 0; }
    .form-group label { display: block; font-size: 13px; font-weight: 600; color: #555; margin-bottom: 5px; }
    .form-group label .required { color: #e74c3c; margin-left: 2px; }
    .form-group .form-input {
        width: 100%; padding: 10px 12px;
        border: 1.5px solid #e0e0e0; border-radius: 8px;
        font-size: 13px; transition: all 0.2s; background: #fafafa;
    }
    .form-group .form-input:focus {
        outline: none; border-color: var(--nu-green);
        box-shadow: 0 0 0 3px rgba(21,115,71,0.1); background: #fff;
    }
    .form-group .form-input.invalid { border-color: #e74c3c; background: #fef2f2; }
    .form-group .form-input.valid { border-color: var(--nu-green); background: #f0fdf4; }
    .form-group select.form-input { appearance: auto; }
    .form-group .field-error { font-size: 11px; color: #e74c3c; margin-top: 3px; display: none; }
    .form-group .field-error.show { display: block; }

    .checkbox-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
    .checkbox-item {
        display: flex; align-items: center; gap: 8px;
        padding: 8px 10px; border: 1.5px solid #eee; border-radius: 8px;
        cursor: pointer; transition: all 0.2s; font-size: 13px;
    }
    .checkbox-item:hover { border-color: var(--nu-green); }
    .checkbox-item input[type="checkbox"] { accent-color: var(--nu-green); }

    .step-actions { display: flex; gap: 10px; margin-top: 20px; }
    .step-actions .btn {
        flex: 1; padding: 10px; border-radius: 8px;
        font-size: 14px; font-weight: 600; cursor: pointer;
        transition: all 0.2s; border: none; text-align: center;
    }
    .step-actions .btn-primary { background: var(--nu-green); color: #fff; }
    .step-actions .btn-primary:hover { background: var(--nu-green-dark); }
    .step-actions .btn-secondary { background: #f0f0f0; color: #666; }
    .step-actions .btn-secondary:hover { background: #e0e0e0; }
    .step-actions .btn:disabled { opacity: 0.5; cursor: not-allowed; }

    .alert { padding: 10px 14px; border-radius: 8px; font-size: 13px; margin-bottom: 16px; }
    .alert-error { background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; }
    .alert-validation { background: #fff9e6; border: 1px solid #f0e6c0; color: #8a7a3a; }

    .form-footer { margin-top: 20px; text-align: center; }
    .form-footer p { font-size: 13px; color: #999; }
    .form-footer a { color: var(--nu-green); font-weight: 600; text-decoration: none; }

    @media (max-width: 900px) {
        .auth-split { flex-direction: column; margin: -16px; margin-top: -16px; }
        .auth-hero { padding: 24px 20px; }
        .auth-hero h1 { font-size: 22px; }
        .auth-hero .hero-steps { display: none; }
        .auth-form-wrap { flex: 1; padding: 20px 16px; }
        .form-grid-2 { grid-template-columns: 1fr; }
        .checkbox-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')
<div class="auth-split">
    <div class="auth-hero">
        <div class="hero-icon">📝</div>
        <h1>Form Pendaftaran</h1>
        <p>Lengkapi data diri Anda untuk menjadi bagian dari NU Peduli Jawa Tengah.</p>
        <div class="hero-steps">
            <div class="hero-step"><span class="hs-num">1</span> Data akun — buat kredensial login</div>
            <div class="hero-step"><span class="hs-num">2</span> Biodata — data diri sesuai KTP</div>
            <div class="hero-step"><span class="hs-num">3</span> Domisili — pilih wilayah tempat tinggal</div>
            <div class="hero-step"><span class="hs-num">4</span> Keahlian & lingkup tugas</div>
        </div>
    </div>
    <div class="auth-form-wrap">
        <div class="form-header">
            <h2>Pendaftaran {{ ucfirst(str_replace('_', ' ', $jenis)) }}</h2>
            <p>Langkah <span id="stepLabel">1</span> dari 4</p>
        </div>

        <div id="validationAlert" class="alert" style="display:none;"></div>

        @if (session('error'))
            <div class="alert alert-error" style="margin-bottom: 16px;">
                <strong>Terjadi Kesalahan:</strong> {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-error">
                <ul style="margin:0;padding-left:16px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Progress --}}
        <div class="progress-bar" id="progressBar"></div>

        <form method="POST" action="{{ route('register.proses', $jenis) }}" id="registerForm" novalidate>
            @csrf

            {{-- Step 1: Akun --}}
            <div class="step-content active" data-step="1">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="no_hp">Nomor HP <span class="required">*</span></label>
                        <input type="tel" name="no_hp" id="no_hp" value="{{ old('no_hp') }}"
                            placeholder="Contoh: 08123456789" class="form-input" required
                            data-required="true" data-label="Nomor HP">
                        <div class="field-error" data-for="no_hp">Nomor HP harus diisi.</div>
                    </div>
                    <div class="form-group">
                        <label for="kata_sandi">Kata Sandi <span class="required">*</span> (min. 8 karakter)</label>
                        <input type="password" name="kata_sandi" id="kata_sandi"
                            class="form-input" required minlength="8"
                            data-required="true" data-label="Kata Sandi">
                        <div class="field-error" data-for="kata_sandi">Kata sandi minimal 8 karakter.</div>
                    </div>
                    <div class="form-group">
                        <label for="kata_sandi_confirmation">Konfirmasi Kata Sandi <span class="required">*</span></label>
                        <input type="password" name="kata_sandi_confirmation" id="kata_sandi_confirmation"
                            class="form-input" required
                            data-required="true" data-label="Konfirmasi Kata Sandi"
                            data-match="kata_sandi">
                        <div class="field-error" data-for="kata_sandi_confirmation">Kata sandi tidak sama.</div>
                    </div>
                </div>
                <div class="step-actions">
                    <span></span>
                    <button type="button" class="btn btn-primary" onclick="validateAndNext(1)">Lanjutkan →</button>
                </div>
            </div>

            {{-- Step 2: Biodata --}}
            <div class="step-content" data-step="2">
                <div class="form-grid form-grid-2">
                    <div class="form-group">
                        <label for="nama_lengkap">Nama Lengkap <span class="required">*</span></label>
                        <input type="text" name="nama_lengkap" id="nama_lengkap" value="{{ old('nama_lengkap') }}"
                            placeholder="Sesuai KTP" class="form-input" required
                            data-required="true" data-label="Nama Lengkap">
                        <div class="field-error" data-for="nama_lengkap">Nama lengkap harus diisi.</div>
                    </div>
                    <div class="form-group">
                        <label for="nik">NIK <span style="color:#999;font-weight:400;">(16 digit, opsional)</span></label>
                        <input type="text" name="nik" id="nik" value="{{ old('nik') }}"
                            maxlength="16" placeholder="Nomor induk kependudukan" class="form-input">
                    </div>
                    <div class="form-group" style="grid-column:1/-1;">
                        <label for="email">Email <span style="color:#999;font-weight:400;">(opsional)</span></label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}"
                            placeholder="nama@email.com" class="form-input">
                    </div>
                </div>
                <div class="step-actions">
                    <button type="button" class="btn btn-secondary" onclick="goToStep(1)">← Kembali</button>
                    <button type="button" class="btn btn-primary" onclick="validateAndNext(2)">Lanjutkan →</button>
                </div>
            </div>

            {{-- Step 3: Domisili --}}
            <div class="step-content" data-step="3">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="sel-kabupaten">Kabupaten/Kota <span class="required">*</span></label>
                        <select name="id_kabupaten" id="sel-kabupaten" class="form-input" required
                            data-required="true" data-label="Kabupaten/Kota"
                            onchange="loadKecamatan(this.value); validateField(this)">
                            <option value="">— Pilih Kabupaten/Kota —</option>
                        </select>
                        <div class="field-error" data-for="id_kabupaten">Pilih kabupaten/kota terlebih dahulu.</div>
                    </div>
                    <div class="form-group">
                        <label for="sel-kecamatan">Kecamatan <span class="required">*</span></label>
                        <select name="id_kecamatan" id="sel-kecamatan" class="form-input" required
                            data-required="true" data-label="Kecamatan"
                            onchange="loadDesa(this.value); validateField(this)">
                            <option value="">— Pilih Kecamatan —</option>
                        </select>
                        <div class="field-error" data-for="id_kecamatan">Pilih kecamatan terlebih dahulu.</div>
                    </div>
                    <div class="form-group">
                        <label for="sel-desa">Desa/Kelurahan <span class="required">*</span></label>
                        <select name="id_desa" id="sel-desa" class="form-input" required
                            data-required="true" data-label="Desa/Kelurahan"
                            onchange="validateField(this)">
                            <option value="">— Pilih Desa —</option>
                        </select>
                        <div class="field-error" data-for="id_desa">Pilih desa/kelurahan terlebih dahulu.</div>
                    </div>
                    <div class="form-group">
                        <label for="alamat_deskriptif">Alamat Lengkap <span class="required">*</span> <span style="color:#999;font-weight:400;">(RT/RW, Dusun, Jalan)</span></label>
                        <input type="text" name="alamat_deskriptif" id="alamat_deskriptif" value="{{ old('alamat_deskriptif') }}"
                            placeholder="Contoh: RT 03 RW 05, Dusun Krajan, Jl. Raya Selatan No. 10"
                            class="form-input" required
                            data-required="true" data-label="Alamat Lengkap">
                        <div class="field-error" data-for="alamat_deskriptif">Alamat lengkap harus diisi.</div>
                    </div>
                </div>
                <div class="step-actions">
                    <button type="button" class="btn btn-secondary" onclick="goToStep(2)">← Kembali</button>
                    <button type="button" class="btn btn-primary" onclick="validateAndNext(3)">Lanjutkan →</button>
                </div>
            </div>

            {{-- Step 4: Keahlian --}}
            <div class="step-content" data-step="4">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Keahlian yang Dimiliki</label>
                        <div class="checkbox-grid">
                            @foreach($keahlianList as $keahlian)
                            <label class="checkbox-item">
                                <input type="checkbox" name="keahlian[]" value="{{ $keahlian->id_keahlian }}"
                                    {{ in_array($keahlian->id_keahlian, old('keahlian', [])) ? 'checked' : '' }}>
                                {{ $keahlian->nama_keahlian }}
                            </label>
                            @endforeach
                        </div>
                    </div>

                    @if($jenis === 'admin_pcnu')
                    <div class="alert alert-validation" style="margin-top: 10px;">
                        <strong>Informasi Penempatan:</strong> Sebagai Admin PCNU, cabang penugasan Anda akan ditentukan secara otomatis berdasarkan Kabupaten/Kota domisili Anda di Langkah 3.
                    </div>
                    @elseif($pcnuList->isNotEmpty())
                    <div class="form-group">
                        <label for="id_pcnu">PCNU Asal <span class="required">*</span></label>
                        <p style="font-size:11px;color:#999;margin-bottom:4px;">Pilih cabang tempat Anda bertugas</p>
                        <select name="id_pcnu" id="id_pcnu" class="form-input" required
                            data-required="true" data-label="PCNU Asal"
                            onchange="validateField(this)">
                            <option value="">— Pilih PCNU —</option>
                            @foreach($pcnuList as $pcnu)
                                <option value="{{ $pcnu->id_pcnu }}" {{ old('id_pcnu') == $pcnu->id_pcnu ? 'selected' : '' }}>{{ $pcnu->nama_pcnu }}</option>
                            @endforeach
                        </select>
                        <div class="field-error" data-for="id_pcnu">Pilih PCNU asal terlebih dahulu.</div>
                    </div>
                    @endif
                </div>
                <div class="step-actions">
                    <button type="button" class="btn btn-secondary" onclick="goToStep(3)">← Kembali</button>
                    <button type="submit" class="btn btn-primary">Daftar Sekarang ✓</button>
                </div>
            </div>
        </form>

        <div class="form-footer">
            <p>Sudah punya akun? <a href="{{ route('login') }}">Masuk di sini</a></p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const TOTAL_STEPS = 4;
let currentStep = 1;
let stepData = {};

function renderProgress() {
    const bar = document.getElementById('progressBar');
    let html = '';
    for (let i = 1; i <= TOTAL_STEPS; i++) {
        let cls = 'pending';
        if (i < currentStep) cls = 'done';
        else if (i === currentStep) cls = 'current';
        const label = i < currentStep ? '✓' : i;
        html += `<div class="progress-step"><div class="progress-dot ${cls}">${label}</div>`;
        if (i < TOTAL_STEPS) {
            html += `<div class="progress-line ${i < currentStep ? 'done' : 'pending'}"></div>`;
        }
        html += '</div>';
    }
    bar.innerHTML = html;
    document.getElementById('stepLabel').textContent = currentStep;
}

function showAlert(msg, type) {
    const el = document.getElementById('validationAlert');
    el.textContent = msg;
    el.className = 'alert alert-' + type;
    el.style.display = 'block';
}

function hideAlert() {
    document.getElementById('validationAlert').style.display = 'none';
}

function validateField(el) {
    const err = document.querySelector(`.field-error[data-for="${el.name || el.id}"]`);
    if (el.hasAttribute('data-required') && !el.value) {
        el.classList.add('invalid');
        el.classList.remove('valid');
        if (err) err.classList.add('show');
        return false;
    }
    // Password match
    if (el.id === 'kata_sandi_confirmation') {
        const pw = document.getElementById('kata_sandi').value;
        if (el.value !== pw) {
            el.classList.add('invalid');
            el.classList.remove('valid');
            if (err) { err.textContent = 'Kata sandi tidak sama.'; err.classList.add('show'); }
            return false;
        }
    }
    // Minlength
    if (el.hasAttribute('minlength') && el.value.length > 0 && el.value.length < parseInt(el.minLength)) {
        el.classList.add('invalid');
        el.classList.remove('valid');
        if (err) { err.textContent = 'Minimal ' + el.minLength + ' karakter.'; err.classList.add('show'); }
        return false;
    }
    el.classList.remove('invalid');
    el.classList.add('valid');
    if (err) err.classList.remove('show');
    return true;
}

function validateStep(step) {
    const stepEl = document.querySelector(`.step-content[data-step="${step}"]`);
    const inputs = stepEl.querySelectorAll('[data-required]');
    let valid = true;
    inputs.forEach(el => {
        if (!validateField(el)) valid = false;
    });
    return valid;
}

function validateAndNext(step) {
    hideAlert();
    if (!validateStep(step)) {
        showAlert('Harap isi semua kolom yang wajib diisi sebelum melanjutkan.', 'validation');
        return;
    }
    goToStep(step + 1);
}

function goToStep(n) {
    hideAlert();
    document.querySelectorAll('.step-content').forEach(el => el.classList.remove('active'));
    document.querySelector(`.step-content[data-step="${n}"]`).classList.add('active');
    currentStep = n;
    renderProgress();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Real-time validation on input
document.addEventListener('input', function(e) {
    if (e.target.hasAttribute('data-required') || e.target.id === 'kata_sandi_confirmation') {
        validateField(e.target);
    }
});

// Load kabupaten on page load
document.addEventListener('DOMContentLoaded', function () {
    renderProgress();
    fetchKabupaten();
});

function fetchKabupaten() {
    fetch('/api/wilayah/kabupaten')
        .then(res => res.json())
        .then(data => {
            const sel = document.getElementById('sel-kabupaten');
            if (data.data) {
                data.data.forEach(item => {
                    const opt = document.createElement('option');
                    opt.value = item.id;
                    opt.textContent = item.nama;
                    sel.appendChild(opt);
                });
            }
        })
        .catch(() => showAlert('Gagal memuat data wilayah. Silakan refresh halaman.', 'validation'));
}

function loadKecamatan(idKab) {
    const selKec = document.getElementById('sel-kecamatan');
    const selDes = document.getElementById('sel-desa');
    selKec.innerHTML = '<option value="">— Pilih Kecamatan —</option>';
    selDes.innerHTML = '<option value="">— Pilih Desa —</option>';
    selDes.disabled = true;
    if (!idKab) { selKec.disabled = true; return; }
    selKec.disabled = false;
    fetch(`/api/wilayah/kecamatan?id_kab=${idKab}`)
        .then(res => res.json())
        .then(data => {
            if (data.data) data.data.forEach(k => {
                const opt = document.createElement('option');
                opt.value = k.id;
                opt.textContent = k.nama;
                selKec.appendChild(opt);
            });
        })
        .catch(() => showAlert('Gagal memuat data kecamatan.', 'validation'));
}

function loadDesa(idKec) {
    const sel = document.getElementById('sel-desa');
    sel.innerHTML = '<option value="">— Pilih Desa —</option>';
    if (!idKec) { sel.disabled = true; return; }
    sel.disabled = false;
    fetch(`/api/wilayah/desa?id_kec=${idKec}`)
        .then(res => res.json())
        .then(data => {
            if (data.data) data.data.forEach(d => {
                const opt = document.createElement('option');
                opt.value = d.id;
                opt.textContent = d.nama;
                sel.appendChild(opt);
            });
        })
        .catch(() => showAlert('Gagal memuat data desa.', 'validation'));
}
</script>
@endpush
