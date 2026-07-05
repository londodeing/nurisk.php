@props(['status', 'map' => 'insiden'])

@php
$colors = [
  'insiden' => [
    'draft'         => 'bg-gray-100 text-gray-600',
    'terverifikasi' => 'bg-blue-100 text-blue-700',
    'respon'        => 'bg-orange-100 text-orange-700',
    'pemulihan'     => 'bg-yellow-100 text-yellow-700',
    'selesai'       => 'bg-green-100 text-green-700',
    'dibatalkan'    => 'bg-red-100 text-red-600',
  ],
  'surat' => [
    'draft'             => 'bg-gray-100 text-gray-600',
    'review_paraf'      => 'bg-blue-100 text-blue-700',
    'siap_tanda_tangan' => 'bg-yellow-100 text-yellow-700',
    'ditandatangani'    => 'bg-green-100 text-green-700',
    'ditolak'           => 'bg-red-100 text-red-600',
    'arsip'             => 'bg-slate-100 text-slate-500',
  ],
  'laporan' => [
    'menunggu' => 'bg-yellow-100 text-yellow-700',
    'ya'       => 'bg-green-100 text-green-700',
    'tidak'    => 'bg-red-100 text-red-600',
  ],
  'akun' => [
    'menunggu'  => 'bg-yellow-100 text-yellow-700',
    'aktif'     => 'bg-green-100 text-green-700',
    'nonaktif'  => 'bg-gray-100 text-gray-600',
    'suspend'   => 'bg-red-100 text-red-600',
  ],
  'prioritas' => [
    'rendah'  => 'bg-gray-100 text-gray-500',
    'sedang'  => 'bg-blue-50 text-blue-600',
    'tinggi'  => 'bg-orange-100 text-orange-600',
    'kritis'  => 'bg-red-100 text-red-700',
  ],
  'kondisi' => [
    'sangat_baik'  => 'bg-green-100 text-green-700',
    'baik'         => 'bg-blue-100 text-blue-600',
    'cukup'        => 'bg-yellow-100 text-yellow-600',
    'rusak_ringan' => 'bg-orange-100 text-orange-600',
    'rusak_berat'  => 'bg-red-100 text-red-600',
    'tidak_layak'  => 'bg-red-200 text-red-800',
  ],
  'operasional' => [
    'siap_pakai'      => 'bg-green-50 text-green-600',
    'dalam_penggunaan' => 'bg-blue-50 text-blue-600',
    'maintenance'     => 'bg-yellow-50 text-yellow-600',
    'rusak'           => 'bg-red-50 text-red-600',
  ],
  'pleno' => [
    'draft'     => 'bg-gray-100 text-gray-600',
    'ditinjau'  => 'bg-blue-100 text-blue-700',
    'final'     => 'bg-green-100 text-green-700',
    'dibatalkan' => 'bg-red-100 text-red-600',
  ],
  'warning' => [
    'LOW'      => 'bg-green-100 text-green-700',
    'MEDIUM'   => 'bg-yellow-100 text-yellow-700',
    'HIGH'     => 'bg-orange-100 text-orange-700',
    'CRITICAL' => 'bg-red-100 text-red-700',
  ],
];
$labels = [
  'warning' => [
    'LOW'      => 'Aman',
    'MEDIUM'   => 'Siaga',
    'HIGH'     => 'Waspada',
    'CRITICAL' => 'Bahaya',
  ],
];
$class = $colors[$map][$status] ?? 'bg-gray-100 text-gray-500';
$label = $labels[$map][$status] ?? ucfirst(str_replace('_', ' ', $status));
@endphp

<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $class }}">
    @if($map === 'prioritas' && $status === 'kritis')
    <span class="mr-1">⚡</span>
    @endif
    {{ $label }}
</span>
