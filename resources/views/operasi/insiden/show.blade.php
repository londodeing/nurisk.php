<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <span class="font-mono text-xl">{{ $insiden->kode_kejadian }}</span>
            <x-badge-status :status="$insiden->status_insiden" map="insiden" />
            <x-badge-status :status="$insiden->prioritas" map="prioritas" />
            @if($insiden->is_locked)
            <span class="px-2 py-0.5 text-xs font-medium bg-gray-100 text-gray-600 rounded-full" title="Terkunci">🔒</span>
            @endif
        </div>
        <div class="text-sm text-gray-500 mt-1">
            {{ $insiden->jenisBencana?->nama_bencana ?? 'Jenis tidak diketahui' }}
            | PCNU: {{ $insiden->pcnu?->nama_pcnu ?? '-' }}
            | Mulai: {{ $insiden->waktu_mulai ? Carbon\Carbon::parse($insiden->waktu_mulai)->locale('id')->isoFormat('D MMM YYYY, HH:mm') : '-' }}
        </div>
    </x-slot>

    @if($insiden->is_locked)
    <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-3 mb-4 flex items-center gap-2">
        <i class="bi bi-lock-fill"></i> Insiden ini telah ditutup dan dikunci. Tidak ada perubahan yang dapat dilakukan.
    </div>
    @endif

    <div x-data="{ tab: 'ringkasan' }">
        <div class="border-b border-gray-200 mb-6">
            <nav class="flex gap-6 -mb-px">
                <button @click="tab = 'ringkasan'" :class="tab === 'ringkasan' ? 'border-green-600 text-green-700' : 'border-transparent text-gray-500 hover:text-gray-700'" class="pb-3 text-sm font-medium border-b-2 transition-colors">Ringkasan</button>
                <button @click="tab = 'assessment'" :class="tab === 'assessment' ? 'border-green-600 text-green-700' : 'border-transparent text-gray-500 hover:text-gray-700'" class="pb-3 text-sm font-medium border-b-2 transition-colors">Assessment</button>
                <button @click="tab = 'personel'" :class="tab === 'personel' ? 'border-green-600 text-green-700' : 'border-transparent text-gray-500 hover:text-gray-700'" class="pb-3 text-sm font-medium border-b-2 transition-colors">Personel</button>
                <button @click="tab = 'logistik'" :class="tab === 'logistik' ? 'border-green-600 text-green-700' : 'border-transparent text-gray-500 hover:text-gray-700'" class="pb-3 text-sm font-medium border-b-2 transition-colors">Logistik</button>
                <button @click="tab = 'pleno'" :class="tab === 'pleno' ? 'border-green-600 text-green-700' : 'border-transparent text-gray-500 hover:text-gray-700'" class="pb-3 text-sm font-medium border-b-2 transition-colors">Pleno</button>
                <button @click="tab = 'jurnal'" :class="tab === 'jurnal' ? 'border-green-600 text-green-700' : 'border-transparent text-gray-500 hover:text-gray-700'" class="pb-3 text-sm font-medium border-b-2 transition-colors">Jurnal Operasi</button>
            </nav>
        </div>

        {{-- TAB 1: RINGKASAN --}}
        <div x-show="tab === 'ringkasan'" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-4">
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4">Informasi Umum</h3>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-xs font-medium text-gray-500">Waktu Mulai</dt>
                            <dd class="text-sm text-gray-900">{{ $insiden->waktu_mulai ? Carbon\Carbon::parse($insiden->waktu_mulai)->locale('id')->isoFormat('D MMM YYYY, HH:mm') : '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500">Waktu Selesai</dt>
                            <dd class="text-sm text-gray-900">{{ $insiden->waktu_selesai ? Carbon\Carbon::parse($insiden->waktu_selesai)->locale('id')->isoFormat('D MMM YYYY, HH:mm') : '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500">PCNU</dt>
                            <dd class="text-sm text-gray-900">{{ $insiden->pcnu?->nama_pcnu ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500">No. SPK</dt>
                            <dd class="text-sm text-gray-900">{{ $insiden->no_spk_assesment ?? '-' }}</dd>
                        </div>
                    </dl>
                </div>

                @if($insiden->laporanAsal)
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4">Laporan Kejadian</h3>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <dt class="text-xs font-medium text-gray-500">Pelapor</dt>
                            <dd class="text-sm text-gray-900">{{ $insiden->laporanAsal->nama_pelapor }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500">Kontak Pelapor</dt>
                            <dd class="text-sm text-gray-900">{{ $insiden->laporanAsal->hp_pelapor }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500">Waktu Kejadian</dt>
                            <dd class="text-sm text-gray-900">{{ $insiden->laporanAsal->waktu_kejadian ? Carbon\Carbon::parse($insiden->laporanAsal->waktu_kejadian)->locale('id')->isoFormat('D MMM YYYY, HH:mm') : '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500">Status Validasi</dt>
                            <dd class="text-sm"><x-badge-status :status="$insiden->laporanAsal->is_valid" map="laporan" /></dd>
                        </div>
                    </dl>
                    <div class="mb-4">
                        <dt class="text-xs font-medium text-gray-500 mb-1">Keterangan Situasi</dt>
                        <dd class="text-sm text-gray-900 bg-gray-50 rounded-lg p-3">{{ $insiden->laporanAsal->keterangan_situasi }}</dd>
                    </div>
                    @if($insiden->laporanAsal->titik_kenal)
                    <div class="mb-4">
                        <dt class="text-xs font-medium text-gray-500 mb-1">Titik Kenal</dt>
                        <dd class="text-sm text-gray-900">{{ $insiden->laporanAsal->titik_kenal }}</dd>
                    </div>
                    @endif
                    @if($insiden->laporanAsal->alamat_lengkap)
                    <div class="mb-4">
                        <dt class="text-xs font-medium text-gray-500 mb-1">Alamat Lengkap</dt>
                        <dd class="text-sm text-gray-900">{{ $insiden->laporanAsal->alamat_lengkap }}</dd>
                    </div>
                    @endif
                    @if($insiden->laporanAsal->photo_path)
                    <div>
                        <dt class="text-xs font-medium text-gray-500 mb-2">Foto Kejadian</dt>
                        <img src="{{ media_url($insiden->laporanAsal->photo_path) }}" alt="Foto Kejadian" class="rounded-lg max-w-full h-auto border border-gray-200 max-h-64 object-cover">
                    </div>
                    @endif
                    @if($insiden->laporanAsal->catatan_validasi)
                    <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <dt class="text-xs font-medium text-yellow-700 mb-1">Catatan Validasi</dt>
                        <dd class="text-sm text-yellow-800">{{ $insiden->laporanAsal->catatan_validasi }}</dd>
                    </div>
                    @endif
                </div>
                @endif

                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4">Riwayat Status</h3>
                    @if($insiden->riwayatStatus->count())
                    <div class="space-y-3">
                        @foreach($insiden->riwayatStatus as $r)
                        <div class="flex items-start gap-3">
                            <div class="w-2 h-2 rounded-full bg-green-500 mt-2 flex-shrink-0"></div>
                            <div>
                                <x-badge-status :status="$r->status_terbaru" map="insiden" />
                                <span class="text-xs text-gray-500 ml-2">oleh {{ $r->pengguna?->profil?->nama_lengkap ?? $r->pengguna?->no_hp ?? 'System' }} — {{ $r->dibuat_pada ? Carbon\Carbon::parse($r->dibuat_pada)->locale('id')->isoFormat('D MMM YYYY, HH:mm') : '-' }}</span>
                                @if($r->alasan)
                                <p class="text-xs text-gray-400 mt-0.5">{{ $r->alasan }}</p>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-sm text-gray-400">Belum ada riwayat transisi status.</p>
                    @endif
                </div>

                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4">Peta Lokasi</h3>
                    @php
                    $lat = $insiden->laporanAsal?->latitude ?? $insiden->posaju->first()?->latitude;
                    $lng = $insiden->laporanAsal?->longitude ?? $insiden->posaju->first()?->longitude;
                    @endphp
                    @if($lat && $lng)
                    <div id="peta-insiden" style="height:250px" class="rounded-lg border border-gray-200"></div>
                    @else
                    <div class="bg-gray-100 flex items-center justify-center h-48 rounded-lg text-gray-400 text-sm">Koordinat belum tersedia</div>
                    @endif
                </div>
            </div>

            <div class="space-y-4">
                @can('ubahStatus', $insiden)
                @if(!$insiden->is_locked)
                <div class="bg-white rounded-xl border border-gray-200 p-4">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">Ubah Status</h3>
                    @php
                    $transitions = [
                        'draft' => ['terverifikasi'],
                        'terverifikasi' => ['respon', 'dibatalkan'],
                        'respon' => ['pemulihan', 'dibatalkan'],
                        'pemulihan' => ['selesai', 'dibatalkan'],
                        'selesai' => [],
                        'dibatalkan' => [],
                    ];
                    $allowed = $transitions[$insiden->status_insiden] ?? [];
                    @endphp
                    @foreach($allowed as $target)
                    <form action="{{ route('insiden.status.update', $insiden) }}" method="POST" class="mb-2">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status_baru" value="{{ $target }}">
                        @if($target === 'dibatalkan')
                        <textarea name="alasan" rows="2" required class="w-full rounded-lg border-gray-300 text-xs mb-2 focus:border-red-500 focus:ring-red-500" placeholder="Alasan pembatalan..."></textarea>
                        @endif
                        <button type="submit" class="w-full px-4 py-2 text-sm font-medium rounded-lg text-white
                            {{ $target === 'dibatalkan' ? 'bg-red-600 hover:bg-red-700' : 'bg-blue-600 hover:bg-blue-700' }}">
                            {{ match($target) { 'terverifikasi' => '→ Terverifikasi', 'respon' => '→ Mulai Respon', 'pemulihan' => '→ Pemulihan', 'selesai' => '→ Selesai', 'dibatalkan' => '✗ Batalkan', default => $target } }}
                        </button>
                    </form>
                    @endforeach
                    @if(empty($allowed))
                    <p class="text-xs text-gray-400 italic">Tidak ada transisi yang tersedia dari status ini.</p>
                    @endif
                </div>
                @endif
                @endcan
            </div>
        </div>

        {{-- TAB 2: ASSESSMENT --}}
        <div x-show="tab === 'assessment'" x-cloak>
            @if(empty($insiden->no_spk_assesment))
                <div class="bg-amber-50 border border-amber-200 text-amber-800 p-4 rounded-xl mb-6">
                    <div class="flex gap-2">
                        <i class="bi bi-exclamation-triangle-fill text-amber-600 text-lg"></i>
                        <div>
                            <h4 class="font-bold text-sm">Surat Perintah Kerja (SPK) / Surat Tugas Belum Diterbitkan</h4>
                            <p class="text-xs text-amber-700 mt-1">Laporan assessment tidak dapat dibuat sebelum petugas TRC ditugaskan secara resmi melalui Surat Tugas (SPK).</p>
                        </div>
                    </div>
                </div>

                @if(!$insiden->is_locked)
                    @can('issueSpk', $insiden)
                    <div class="bg-white rounded-xl border border-gray-200 p-6 max-w-xl">
                        <h3 class="text-sm font-bold text-gray-800 mb-4">Penerbitan Surat Perintah Kerja (SPK) Assessment</h3>
                        <form action="{{ route('insiden.spk.store', $insiden) }}" method="POST" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Pilih Petugas TRC / Relawan Penerima Tugas (Bisa pilih lebih dari satu) <span class="text-red-500">*</span></label>
                                <select name="petugas_trc_ids[]" multiple required class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500 text-sm h-32">
                                    @foreach($trcList as $trc)
                                        <option value="{{ $trc->id_pengguna }}">{{ $trc->profil->nama_lengkap ?? $trc->no_hp }} ({{ $trc->peran->nama_peran ?? 'TRC' }})</option>
                                    @endforeach
                                </select>
                                <p class="text-[10px] text-gray-500 mt-1">Tahan tombol Ctrl (Windows) atau Command (Mac) untuk memilih lebih dari satu.</p>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Catatan Penugasan / Instruksi Khusus</label>
                                <textarea name="catatan_penugasan" rows="3" class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500 text-sm" placeholder="Contoh: Lakukan kaji cepat fokus pada area pemukiman pinggir sungai..."></textarea>
                            </div>
                            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">
                                <i class="bi bi-file-earmark-text-fill"></i> Terbitkan SPK & Surat Tugas
                            </button>
                        </form>
                    </div>
                    @else
                    <div class="bg-white rounded-xl border border-gray-200 p-6 max-w-xl text-center">
                        <i class="bi bi-clock-history text-4xl text-gray-400 mb-3 inline-block"></i>
                        <h4 class="font-bold text-gray-800 text-sm">Menunggu Surat Perintah Kerja</h4>
                        <p class="text-xs text-gray-500 mt-1">Harap hubungi Pimpinan / Ketua NU Peduli untuk menerbitkan Surat Tugas (SPK) agar assessment dapat dimulai.</p>
                    </div>
                    @endcan
                @else
                <p class="text-sm text-gray-400 italic">Insiden dikunci. SPK tidak dapat diterbitkan.</p>
                @endif
            @else
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-700">Assessment</h3>
                    @can('create', [App\Models\AssessmentUtama::class, $insiden])
                    @if(!$insiden->is_locked)
                    <a href="{{ route('insiden.assessment.create', $insiden) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700">
                        <i class="bi bi-plus-lg"></i> Assessment Baru
                    </a>
                    @endif
                    @endcan
                </div>
                @if($insiden->assessments->count())
                <div class="space-y-3">
                    @foreach($insiden->assessments()->latest('dibuat_pada')->get() as $assessment)
                    <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <x-badge-status :status="$assessment->is_latest ? 'Terkini' : 'Lama'" map="laporan" />
                            <div>
                                <p class="text-sm text-gray-700">
                                    {{ $assessment->waktu_assesment ? Carbon\Carbon::parse($assessment->waktu_assesment)->locale('id')->isoformat('D MMM YYYY, HH:mm') : '-' }}
                                </p>
                                <p class="text-xs text-gray-400">{{ $assessment->petugas?->profil?->nama_lengkap ?? 'Petugas tidak diketahui' }}</p>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <a href="{{ route('insiden.assessment.show', [$insiden, $assessment]) }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium">Lihat Detail</a>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <x-empty-state message="Belum ada assessment. Klik '+ Assessment Baru' untuk memulai." icon="📋" :action="!$insiden->is_locked" actionLabel="+ Assessment Baru" :actionRoute="route('insiden.assessment.create', $insiden)" />
                @endif
            @endif
        </div>

        {{-- TAB 3: PERSONEL --}}
        <div x-show="tab === 'personel'" x-cloak>
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-gray-700">Personel</h3>
                <div class="flex gap-2">
                    <a href="{{ route('insiden.penugasan.index', $insiden) }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                        <i class="bi bi-list-ul"></i> Lihat Semua
                    </a>
                    @can('create', [App\Models\OperasiPenugasan::class, $insiden])
                    @if(!$insiden->is_locked)
                    <a href="{{ route('insiden.penugasan.create', $insiden) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700">
                        <i class="bi bi-plus-lg"></i> Tugaskan Personel
                    </a>
                    @endif
                    @endcan
                </div>
            </div>
            @php
                $activeStatuses = ['assigned', 'notified', 'accepted', 'on_route', 'on_site', 'aktif'];
                $activePenugasan = $insiden->penugasan->filter(fn($p) => in_array($p->status_penugasan, $activeStatuses));
            @endphp
            @if($activePenugasan->count())
            <x-data-table>
                <x-slot name="head">
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Nama</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Peran</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Sejak</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Aksi</th>
                </x-slot>
                <x-slot name="body">
                    @foreach($activePenugasan as $p)
                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $p->pengguna?->profil?->nama_lengkap ?? $p->pengguna?->no_hp ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm"><span class="px-2 py-0.5 text-xs font-medium rounded-full bg-blue-100 text-blue-700">{{ ucfirst(str_replace('_', ' ', $p->peran_otoritas)) }}</span></td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ ucfirst(str_replace('_', ' ', $p->status_penugasan)) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $p->waktu_mulai ? Carbon\Carbon::parse($p->waktu_mulai)->locale('id')->isoFormat('D MMM YYYY') : '-' }}</td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('insiden.penugasan.show', [$insiden, $p]) }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium">Detail</a>
                        </td>
                    </tr>
                    @endforeach
                </x-slot>
            </x-data-table>
            @else
            <x-empty-state message="Belum ada penugasan personel." icon="👥" />
            @endif
        </div>

        {{-- TAB 4: LOGISTIK --}}
        <div x-show="tab === 'logistik'" x-cloak>
            <x-empty-state message="Data logistik akan ditampilkan di sini." icon="📦" />
        </div>

        {{-- TAB 5: PLENO --}}
        <div x-show="tab === 'pleno'" x-cloak>
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-gray-700">Pleno</h3>
                @can('create', [\App\Models\OperasiPleno::class, $insiden])
                @if(!$insiden->is_locked)
                <a href="{{ route('insiden.pleno.create', $insiden) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700">
                    <i class="bi bi-plus-lg"></i> Pleno Baru
                </a>
                @endif
                @endcan
            </div>
            @if($plenoList->count())
            <x-data-table>
                <x-slot name="head">
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Tanggal</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Aksi</th>
                </x-slot>
                <x-slot name="body">
                    @foreach($plenoList as $pleno)
                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $pleno->waktu_pleno ? Carbon\Carbon::parse($pleno->waktu_pleno)->locale('id')->isoFormat('D MMM YYYY') : '-' }}</td>
                        <td class="px-4 py-3 text-center"><x-badge-status :status="$pleno->status_pleno" map="pleno" /></td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('insiden.pleno.show', [$insiden, $pleno]) }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium">Lihat</a>
                        </td>
                    </tr>
                    @endforeach
                </x-slot>
            </x-data-table>
            @else
            <x-empty-state message="Belum ada pleno untuk insiden ini." icon="📋" />
            @endif
        </div>

        {{-- TAB 6: JURNAL OPERASI --}}
        <div x-show="tab === 'jurnal'" x-cloak>
            <h3 class="text-sm font-semibold text-gray-700 mb-4">Jurnal Operasi</h3>
            @if($jurnalList->count())
            <div class="space-y-3">
                @foreach($jurnalList as $jurnal)
                <div class="bg-white rounded-xl border border-gray-200 p-4">
                    <div class="flex items-center gap-2 text-sm">
                        <span class="text-xs font-mono text-gray-400">{{ $jurnal->dibuat_pada ? Carbon\Carbon::parse($jurnal->dibuat_pada)->locale('id')->isoFormat('HH:mm DD/MM') : '-' }}</span>
                        <x-badge-status :status="$jurnal->kategori_event ?? 'info'" map="insiden" />
                        <span class="font-medium text-gray-800">{{ $jurnal->judul_event }}</span>
                    </div>
                    @if($jurnal->deskripsi_event)
                    <p class="text-xs text-gray-500 mt-1">{{ $jurnal->deskripsi_event }}</p>
                    @endif
                </div>
                @endforeach
            </div>
            @else
            <x-empty-state message="Belum ada jurnal operasional." icon="📝" />
            @endif
        </div>
    </div>

    @if(($lat ?? null) && ($lng ?? null))
    @push('scripts')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const map = L.map('peta-insiden').setView([{{ $lat }}, {{ $lng }}], 10);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);
        L.marker([{{ $lat }}, {{ $lng }}]).addTo(map);
    </script>
    @endpush
    @endif
</x-app-layout>
