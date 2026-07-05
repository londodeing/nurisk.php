@extends('layouts.app')

@section('title', 'Governance Approval Center')

@push('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
<div class="max-w-4xl mx-auto px-4 py-6" id="approval-center">

    {{-- Header dengan counter --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Governance Approval Center</h1>
            <p class="text-sm text-gray-500 mt-1">
                Selamat pagi, {{ Auth::user()->profil->nama_lengkap ?? Auth::user()->no_hp }}.
                @if($totalPending > 0)
                    <span class="text-orange-600 font-medium">
                        Ada {{ $totalPending }} item menunggu persetujuan Anda.
                    </span>
                @else
                    <span class="text-green-600 font-medium">Semua sudah bersih. ✅</span>
                @endif
            </p>
        </div>
        <div class="flex gap-3">
            {{-- Badge counter per kategori --}}
            <span class="inline-flex items-center gap-1 text-xs font-medium bg-blue-50 text-blue-700 px-3 py-1 rounded-full border border-blue-200">
                Paraf
                <span class="bg-blue-600 text-white rounded-full px-1.5 py-0.5 text-xs" id="badge-paraf">
                    {{ $parafMenunggu->count() }}
                </span>
            </span>
            <span class="inline-flex items-center gap-1 text-xs font-medium bg-purple-50 text-purple-700 px-3 py-1 rounded-full border border-purple-200">
                Pleno
                <span class="bg-purple-600 text-white rounded-full px-1.5 py-0.5 text-xs" id="badge-pleno">
                    {{ $plenoMenunggu->count() }}
                </span>
            </span>
            <span class="inline-flex items-center gap-1 text-xs font-medium bg-green-50 text-green-700 px-3 py-1 rounded-full border border-green-200">
                TTD
                <span class="bg-green-600 text-white rounded-full px-1.5 py-0.5 text-xs" id="badge-ttd">
                    {{ $suratMenungguTtd->count() }}
                </span>
            </span>
        </div>
    </div>

    {{-- =========================== --}}
    {{-- PANEL 1: ANTRIAN PARAF     --}}
    {{-- =========================== --}}
    <section class="mb-8" id="panel-paraf">
        <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3 flex items-center gap-2">
            <span>📋</span> Paraf Menunggu Anda
            @if($parafMenunggu->isEmpty())
                <span class="text-green-500 text-xs font-normal normal-case">— Semua selesai</span>
            @endif
        </h2>

        <div class="space-y-3" id="list-paraf">
            @forelse($parafMenunggu as $paraf)
            <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm flex items-start justify-between gap-4"
                 id="paraf-card-{{ $paraf->id_paraf }}">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="font-mono text-sm font-semibold text-gray-800">
                            {{ $paraf->surat->nomor_surat_resmi }}
                        </span>
                        <span class="text-xs text-gray-400">Paraf ke-{{ $paraf->urutan }}</span>
                    </div>
                    <p class="text-sm text-gray-600 truncate">{{ $paraf->surat->perihal }}</p>
                    <p class="text-xs text-gray-400 mt-1">
                        Terbit: {{ $paraf->surat->tgl_terbit?->format('d M Y') }}
                        @if($paraf->surat->insiden)
                            &middot; {{ $paraf->surat->insiden->kode_kejadian }}
                        @endif
                    </p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    {{-- Tombol Setujui Paraf --}}
                    <button
                        onclick="prosesParaf({{ $paraf->id_paraf }}, 'disetujui')"
                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 focus:ring-2 focus:ring-green-500 transition-colors">
                        ✓ Setujui
                    </button>
                    {{-- Tombol Tolak — buka modal --}}
                    <button
                        onclick="bukaModalTolak({{ $paraf->id_paraf }})"
                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-white border border-red-300 text-red-600 text-sm font-medium rounded-lg hover:bg-red-50 focus:ring-2 focus:ring-red-500 transition-colors">
                        ✗ Tolak
                    </button>
                    <a href="{{ route('surat.show', $paraf->id_surat) }}"
                       class="text-xs text-blue-600 hover:underline">Detail</a>
                </div>
            </div>
            @empty
            <div class="text-center py-8 text-gray-400 border border-dashed border-gray-200 rounded-lg">
                Tidak ada paraf menunggu Anda. 🎉
            </div>
            @endforelse
        </div>
    </section>

    {{-- ================================= --}}
    {{-- PANEL 2: FINALISASI PLENO        --}}
    {{-- ================================= --}}
    @if(Auth::user()->can('finalisasi', new \App\Models\OperasiPleno))
    <section class="mb-8" id="panel-pleno">
        <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3 flex items-center gap-2">
            <span>🏛️</span> Pleno Menunggu Finalisasi
        </h2>

        <div class="space-y-3" id="list-pleno">
            @forelse($plenoMenunggu as $pleno)
            <div class="bg-white border border-purple-100 rounded-lg p-4 shadow-sm"
                 id="pleno-card-{{ $pleno->id_pleno }}">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="font-mono text-sm font-semibold text-gray-800">
                                {{ $pleno->nomor_pleno }}
                            </span>
                            <span class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full">
                                {{ ucfirst(str_replace('_', ' ', $pleno->jenis_pleno)) }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-600">
                            Insiden: {{ $pleno->insiden->kode_kejadian ?? '-' }}
                        </p>
                        <p class="text-xs text-gray-400 mt-1">
                            Pimpinan: {{ $pleno->pimpinan->profil->nama_lengkap ?? '-' }}
                            &middot; {{ $pleno->peserta->count() }} peserta
                            @php
                                $allSetuju = $pleno->peserta->filter(fn($p) => $p->hak_suara)->every(fn($p) => $p->status_persetujuan === 'setuju');
                            @endphp
                            @if($allSetuju)
                                &middot; <span class="text-green-600">Semua peserta setuju ✅</span>
                            @else
                                &middot; <span class="text-orange-500">Ada peserta yang belum voting</span>
                            @endif
                        </p>
                    </div>
                    <div class="shrink-0 flex gap-2">
                        <button
                            onclick="finalisasiPleno({{ $pleno->id_pleno }}, '{{ $pleno->nomor_pleno }}')"
                            class="inline-flex items-center gap-1 px-3 py-1.5 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 focus:ring-2 focus:ring-purple-500 transition-colors">
                            🔒 Finalisasi
                        </button>
                        <a href="{{ route('insiden.pleno.show', [$pleno->id_insiden, $pleno->id_pleno]) }}"
                           class="text-xs text-blue-600 hover:underline self-center">Detail</a>
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-8 text-gray-400 border border-dashed border-gray-200 rounded-lg">
                Tidak ada pleno menunggu finalisasi.
            </div>
            @endforelse
        </div>
    </section>
    @endif

    {{-- ================================= --}}
    {{-- PANEL 3: TANDA TANGAN SURAT      --}}
    {{-- ================================= --}}
    <section class="mb-8" id="panel-ttd">
        <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3 flex items-center gap-2">
            <span>✍️</span> Tanda Tangan Surat
        </h2>

        <div class="space-y-3" id="list-ttd">
            @forelse($suratMenungguTtd as $surat)
            <div class="bg-white border border-green-100 rounded-lg p-4 shadow-sm"
                 id="surat-ttd-card-{{ $surat->id_surat }}">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="font-mono text-sm font-semibold text-gray-800">
                                {{ $surat->nomor_surat_resmi }}
                            </span>
                            <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full">
                                {{ $surat->jenisSurat->nama_jenis ?? '-' }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-600">{{ $surat->perihal }}</p>
                        <p class="text-xs text-gray-400 mt-1">
                            Terbit: {{ $surat->tgl_terbit?->format('d M Y') }}
                            &middot; {{ $surat->paraf->count() }} paraf semua sudah setuju ✅
                        </p>
                    </div>
                    <div class="shrink-0 flex gap-2">
                        {{-- Preview dalam modal --}}
                        <button
                            onclick="previewSurat({{ $surat->id_surat }}, '{{ $surat->nomor_surat_resmi }}')"
                            class="px-3 py-1.5 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors">
                            👁 Preview
                        </button>
                        {{-- Tanda tangan langsung --}}
                        <button
                            onclick="tandatanganiSurat({{ $surat->id_surat }}, '{{ $surat->nomor_surat_resmi }}')"
                            class="inline-flex items-center gap-1 px-3 py-1.5 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 focus:ring-2 focus:ring-green-500 transition-colors">
                            ✍ Tandatangani
                        </button>
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-8 text-gray-400 border border-dashed border-gray-200 rounded-lg">
                Tidak ada surat menunggu tanda tangan Anda.
            </div>
            @endforelse
        </div>
    </section>

    {{-- ================================= --}}
    {{-- PANEL 4: RIWAYAT HARI INI        --}}
    {{-- ================================= --}}
    @if($riwayatHariIni->isNotEmpty())
    <section>
        <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">
            ✅ Riwayat Approval Hari Ini
        </h2>
        <div class="bg-white border border-gray-100 rounded-lg divide-y divide-gray-50">
            @foreach($riwayatHariIni as $item)
            <div class="px-4 py-2.5 flex items-center justify-between text-sm">
                <div class="flex items-center gap-3">
                    <span class="text-xs text-gray-400 font-mono w-12">
                        {{ \Carbon\Carbon::parse($item['waktu'])->format('H:i') }}
                    </span>
                    <span class="text-gray-700">{{ $item['label'] }}</span>
                </div>
                <span @class([
                    'text-xs font-medium px-2 py-0.5 rounded-full',
                    'bg-green-100 text-green-700' => $item['status'] === 'disetujui',
                    'bg-red-100 text-red-600'     => $item['status'] === 'ditolak',
                ])>{{ ucfirst($item['status']) }}</span>
            </div>
            @endforeach
        </div>
    </section>
    @endif

</div>

{{-- ================================ --}}
{{-- MODAL: Tolak Paraf               --}}
{{-- ================================ --}}
<div id="modal-tolak-paraf" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-2">Tolak Paraf</h3>
        <p class="text-sm text-gray-500 mb-4">Catatan penolakan wajib diisi agar pemohon mengetahui alasannya.</p>
        <input type="hidden" id="tolak-paraf-id" value="">
        <textarea
            id="tolak-catatan"
            rows="3"
            placeholder="Contoh: Perihal tidak sesuai dengan keputusan pleno tanggal..."
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-300 focus:border-red-400 resize-none">
        </textarea>
        <p class="text-xs text-red-500 mt-1 hidden" id="tolak-catatan-error">Catatan wajib diisi.</p>
        <div class="flex justify-end gap-3 mt-4">
            <button onclick="tutupModalTolak()"
                class="px-4 py-2 text-sm border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                Batal
            </button>
            <button onclick="konfirmasiTolakParaf()"
                class="px-4 py-2 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700">
                Tolak Paraf
            </button>
        </div>
    </div>
</div>

{{-- MODAL: Preview Surat --}}
<div id="modal-preview-surat" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full max-h-[80vh] overflow-y-auto">
        <div class="sticky top-0 bg-white border-b px-6 py-4 flex items-center justify-between">
            <h3 class="font-semibold text-gray-900" id="preview-surat-judul">Preview Surat</h3>
            <button onclick="tutupModalPreview()" class="text-gray-400 hover:text-gray-600">✕</button>
        </div>
        <div id="preview-surat-content" class="p-6">
            <div class="text-center text-gray-400 py-8">Memuat preview...</div>
        </div>
        <div class="sticky bottom-0 bg-white border-t px-6 py-4 flex justify-end gap-3">
            <button onclick="tutupModalPreview()" class="px-4 py-2 text-sm border border-gray-300 rounded-lg">
                Tutup
            </button>
            <button onclick="tandatanganiDariPreview()" id="btn-ttd-dari-preview"
                class="px-4 py-2 text-sm bg-green-600 text-white rounded-lg hover:bg-green-700">
                ✍ Tandatangani
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
let activeSuratId = null;

// ===================== PARAF =====================

async function prosesParaf(parafId, status, catatan = null) {
    const card = document.getElementById(`paraf-card-${parafId}`);
    if (card) {
        card.style.opacity = '0.5';
        card.style.pointerEvents = 'none';
    }

    try {
        const res = await fetch(`/governance/approval/paraf/${parafId}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ status_paraf: status, catatan }),
        });

        const data = await res.json();

        if (!res.ok) {
            showToast('error', data.error || 'Terjadi kesalahan.');
            if (card) { card.style.opacity = '1'; card.style.pointerEvents = 'auto'; }
            return;
        }

        if (card) {
            card.style.transition = 'all 0.3s ease';
            card.style.maxHeight = card.scrollHeight + 'px';
            requestAnimationFrame(() => {
                card.style.maxHeight = '0';
                card.style.opacity = '0';
                card.style.marginBottom = '0';
                card.style.padding = '0';
            });
            setTimeout(() => card.remove(), 300);
        }

        updateBadge('badge-paraf', -1);
        showToast('success', data.message);

    } catch (e) {
        showToast('error', 'Gagal memproses. Cek koneksi internet.');
        if (card) { card.style.opacity = '1'; card.style.pointerEvents = 'auto'; }
    }
}

function bukaModalTolak(parafId) {
    document.getElementById('tolak-paraf-id').value = parafId;
    document.getElementById('tolak-catatan').value = '';
    document.getElementById('tolak-catatan-error').classList.add('hidden');
    document.getElementById('modal-tolak-paraf').classList.remove('hidden');
}

function tutupModalTolak() {
    document.getElementById('modal-tolak-paraf').classList.add('hidden');
}

function konfirmasiTolakParaf() {
    const catatan = document.getElementById('tolak-catatan').value.trim();
    if (!catatan) {
        document.getElementById('tolak-catatan-error').classList.remove('hidden');
        return;
    }
    const parafId = document.getElementById('tolak-paraf-id').value;
    tutupModalTolak();
    prosesParaf(parseInt(parafId), 'ditolak', catatan);
}

// ===================== PLENO =====================

async function finalisasiPleno(plenoId, nomorPleno) {
    if (!confirm(`Finalisasi Pleno ${nomorPleno}? Tindakan ini tidak dapat dibatalkan.`)) return;

    const card = document.getElementById(`pleno-card-${plenoId}`);
    if (card) { card.style.opacity = '0.5'; card.style.pointerEvents = 'none'; }

    try {
        const res = await fetch(`/governance/approval/pleno/${plenoId}/finalisasi`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: JSON.stringify({}),
        });

        const data = await res.json();
        if (!res.ok) {
            showToast('error', data.error || 'Gagal memfinalisasi pleno.');
            if (card) { card.style.opacity = '1'; card.style.pointerEvents = 'auto'; }
            return;
        }

        if (card) {
            card.style.transition = 'all 0.3s ease';
            card.style.maxHeight = card.scrollHeight + 'px';
            requestAnimationFrame(() => { card.style.maxHeight = '0'; card.style.opacity = '0'; card.style.margin = '0'; });
            setTimeout(() => card.remove(), 300);
        }

        updateBadge('badge-pleno', -1);
        showToast('success', data.message);

    } catch (e) {
        showToast('error', 'Gagal. Cek koneksi internet.');
        if (card) { card.style.opacity = '1'; card.style.pointerEvents = 'auto'; }
    }
}

// ===================== TANDA TANGAN =====================

async function previewSurat(suratId, nomorSurat) {
    activeSuratId = suratId;
    document.getElementById('preview-surat-judul').textContent = 'Preview: ' + nomorSurat;
    document.getElementById('preview-surat-content').innerHTML = '<div class="text-center py-8 text-gray-400">Memuat preview...</div>';
    document.getElementById('btn-ttd-dari-preview').dataset.suratId = suratId;
    document.getElementById('btn-ttd-dari-preview').dataset.nomorSurat = nomorSurat;
    document.getElementById('modal-preview-surat').classList.remove('hidden');

    try {
        const res = await fetch(`/governance/approval/surat/${suratId}/preview`);
        document.getElementById('preview-surat-content').innerHTML = await res.text();
    } catch (e) {
        document.getElementById('preview-surat-content').innerHTML = '<p class="text-red-500">Gagal memuat preview.</p>';
    }
}

function tutupModalPreview() {
    document.getElementById('modal-preview-surat').classList.add('hidden');
    activeSuratId = null;
}

function tandatanganiDariPreview() {
    const btn = document.getElementById('btn-ttd-dari-preview');
    tutupModalPreview();
    tandatanganiSurat(parseInt(btn.dataset.suratId), btn.dataset.nomorSurat);
}

async function tandatanganiSurat(suratId, nomorSurat) {
    if (!confirm(`Tandatangani dan finalisasi surat ${nomorSurat}?\n\nTindakan ini tidak dapat dibatalkan. PDF akan digenerate secara otomatis.`)) return;

    const card = document.getElementById(`surat-ttd-card-${suratId}`);
    if (card) { card.style.opacity = '0.5'; card.style.pointerEvents = 'none'; }

    try {
        const res = await fetch(`/governance/approval/surat/${suratId}/tandatangani`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: JSON.stringify({ konfirmasi: true }),
        });

        const data = await res.json();
        if (!res.ok) {
            showToast('error', data.error || 'Gagal menandatangani surat.');
            if (card) { card.style.opacity = '1'; card.style.pointerEvents = 'auto'; }
            return;
        }

        if (card) {
            card.style.transition = 'all 0.3s ease';
            card.style.maxHeight = card.scrollHeight + 'px';
            requestAnimationFrame(() => { card.style.maxHeight = '0'; card.style.opacity = '0'; card.style.margin = '0'; });
            setTimeout(() => card.remove(), 300);
        }

        updateBadge('badge-ttd', -1);
        showToast('success', data.message);

    } catch (e) {
        showToast('error', 'Gagal. Cek koneksi internet.');
        if (card) { card.style.opacity = '1'; card.style.pointerEvents = 'auto'; }
    }
}

// ===================== UTILITIES =====================

function updateBadge(badgeId, delta) {
    const badge = document.getElementById(badgeId);
    if (!badge) return;
    const current = parseInt(badge.textContent) || 0;
    const next = Math.max(0, current + delta);
    badge.textContent = next;
    if (next === 0) {
        badge.classList.replace('bg-blue-600', 'bg-gray-300');
        badge.classList.replace('bg-purple-600', 'bg-gray-300');
        badge.classList.replace('bg-green-600', 'bg-gray-300');
    }
}

function showToast(type, message) {
    const colors = {
        success: 'bg-green-600',
        error:   'bg-red-600',
        info:    'bg-blue-600',
    };
    const toast = document.createElement('div');
    toast.className = `fixed bottom-6 right-6 z-50 px-4 py-3 rounded-lg text-white text-sm font-medium shadow-lg ${colors[type] || colors.info} transition-all`;
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => { toast.style.opacity = '0'; setTimeout(() => toast.remove(), 300); }, 4000);
}

// Polling: refresh counter setiap 30 detik (tidak reload page)
setInterval(async () => {
    try {
        const res = await fetch('/governance/approval', {
            headers: { 'Accept': 'application/json' }
        });
        const data = await res.json();
        document.getElementById('badge-paraf').textContent = data.paraf_count;
        document.getElementById('badge-pleno').textContent = data.pleno_count;
        document.getElementById('badge-ttd').textContent   = data.surat_ttd_count;
    } catch (e) { /* silent fail */ }
}, 30000);
</script>
@endpush
@endsection
