<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\LaporanKejadian;
use App\Services\LocationService;
use App\Services\Media\MediaUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LaporController extends Controller
{
    public function __construct(
        private LocationService $locationService,
        private MediaUploadService $mediaUploadService,
    ) {}

    public function store(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'nama'              => ['required', 'string', 'max:150'],
            'no_hp'             => ['required', 'string', 'max:20', 'min:8', 'regex:/^[0-9\+\-\(\)\s]+$/'],
            'id_jenis_bencana'  => ['required', 'integer', 'exists:bencana_master_jenis,id_jenis'],
            'id_kab'            => ['required', 'string', 'exists:wilayah_kabupaten,id_kab'],
            'id_kec'            => ['required', 'string', 'exists:wilayah_kecamatan,id_kec'],
            'id_desa'           => ['required', 'string', 'exists:wilayah_desa,id_desa'],
            'lokasi'            => ['required', 'string', 'max:500'],
            'deskripsi'         => ['required', 'string', 'max:2000'],
            'latitude'          => ['required_without:manual_lat', 'nullable', 'numeric', 'between:-10,10'],
            'longitude'         => ['required_without:manual_lng', 'nullable', 'numeric', 'between:90,150'],
            'manual_lat'        => ['required_without:latitude', 'nullable', 'numeric', 'between:-10,10'],
            'manual_lng'        => ['required_without:longitude', 'nullable', 'numeric', 'between:90,150'],
            'waktu_kejadian'    => ['required', 'date', 'before_or_equal:now'],
            'foto'              => [
                'nullable',
                ...$this->mediaUploadService->toValidationRules('laporan'),
            ],
        ], [
            'nama.required'                  => 'Nama harus diisi.',
            'no_hp.required'                 => 'Nomor HP harus diisi.',
            'no_hp.min'                      => 'Nomor HP minimal 8 digit.',
            'no_hp.regex'                    => 'Format nomor HP tidak valid.',
            'id_jenis_bencana.required'      => 'Jenis kejadian harus dipilih.',
            'id_jenis_bencana.exists'        => 'Jenis kejadian tidak valid.',
            'id_kab.required'                => 'Kabupaten/Kota harus dipilih.',
            'id_kec.required'                => 'Kecamatan harus dipilih.',
            'id_desa.required'               => 'Desa/Kelurahan harus dipilih.',
            'lokasi.required'                => 'Lokasi kejadian harus diisi.',
            'deskripsi.required'             => 'Deskripsi kejadian harus diisi.',
            'latitude.required_without'      => 'Lokasi GPS diperlukan. Klik tombol "Dapatkan Lokasi Saya" atau isi koordinat manual.',
            'latitude.between'               => 'Latitude tidak valid.',
            'longitude.required_without'     => 'Lokasi GPS diperlukan. Klik tombol "Dapatkan Lokasi Saya" atau isi koordinat manual.',
            'longitude.between'              => 'Longitude tidak valid.',
            'manual_lat.required_without'    => 'Latitude manual tidak valid.',
            'manual_lat.between'             => 'Latitude tidak valid.',
            'manual_lng.required_without'    => 'Longitude manual tidak valid.',
            'manual_lng.between'             => 'Longitude tidak valid.',
            'waktu_kejadian.required'        => 'Waktu kejadian harus diisi.',
            'waktu_kejadian.before_or_equal' => 'Waktu kejadian tidak boleh di masa depan.',
            'foto.mimes'                     => 'Format foto harus jpeg, png, atau webp.',
            'foto.max'                       => 'Ukuran foto maksimal 10MB.',
            'foto.uploaded'                  => 'File foto gagal diupload. Ukuran file mungkin melebihi batas maksimal.',
        ])->after(function ($validator) use ($request) {
            if ($request->files->has('foto') && !$request->hasFile('foto')) {
                $validator->errors()->add('foto', 'File foto gagal diupload. Ukuran file mungkin melebihi batas maksimal.');
            }
        })->validate();

        $lat = (float) ($validated['latitude'] ?? $validated['manual_lat']);
        $lng = (float) ($validated['longitude'] ?? $validated['manual_lng']);

        $titikKenal = $validated['lokasi'];

        // Prioritaskan id_kab (dipilih user) untuk menentukan PCNU, bukan latlong
        $idPcnu = null;
        if (!empty($validated['id_kab'])) {
            $idPcnu = $this->locationService->findPcnuByIdKab($validated['id_kab']);
        }

        $alamatLengkap = null;
        if ($lat && $lng) {
            $generated = $this->locationService->reverseGeocode($lat, $lng);
            $alamatLengkap = $generated['alamat_lengkap'] ?? null;

            if (empty($validated['id_kab'])) {
                $idPcnu = $this->locationService->findPcnuByKabupaten($generated['kabupaten'] ?? null);
            }
        }

        if ($alamatLengkap) {
            // Do not concatenate alamat to titik_kenal
            // $titikKenal = $alamatLengkap . ' — ' . $titikKenal;
        }

        $photoPath = null;
        $mediaId = null;
        if ($request->hasFile('foto')) {
            try {
                $result = $this->mediaUploadService->upload($request->file('foto'), 'laporan');
                $photoPath = $result->path;
                $mediaId = $result->mediaId;
            } catch (\App\Services\Media\MediaUploadException $e) {
                return back()->withInput()->withErrors(['foto' => $e->getMessage()]);
            }
        }

        unset($validated['foto']);

        $laporan = DB::transaction(function () use ($validated, $lat, $lng, $titikKenal, $photoPath, $alamatLengkap, $idPcnu, $mediaId) {
            $laporan = LaporanKejadian::create([
                'kode_kejadian'      => LaporanKejadian::generateKodeKejadian(),
                'id_pengguna'        => null,
                'id_jenis_bencana'   => (int) $validated['id_jenis_bencana'],
                'nama_pelapor'       => $validated['nama'],
                'hp_pelapor'         => $validated['no_hp'],
                'keterangan_situasi' => $validated['deskripsi'],
                'titik_kenal'        => $titikKenal,
                'waktu_kejadian'     => $validated['waktu_kejadian'],
                'latitude'           => $lat,
                'longitude'          => $lng,
                'alamat_lengkap'     => $alamatLengkap,
                'id_kab'             => $validated['id_kab'] ?? null,
                'id_kec'             => $validated['id_kec'] ?? null,
                'id_desa'            => $validated['id_desa'] ?? null,
                'id_pcnu'            => $idPcnu,
                'photo_path'         => $photoPath,
                'is_valid'           => 'menunggu',
            ]);

            if ($mediaId) {
                $this->mediaUploadService->associate($mediaId, 'laporan', $laporan->id_laporan_kejadian);
            }

            return $laporan;
        });

        return redirect()->route('public.lapor')
            ->with('success', 'Laporan Anda telah diterima. Kode: ' . $laporan->kode_kejadian . '. Tim NU Peduli akan menindaklanjuti laporan Anda. Terima kasih atas partisipasi Anda.');
    }
}
