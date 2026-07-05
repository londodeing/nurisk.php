<div>
    <h4 class="text-lg font-bold">{{ $surat->nomor_surat_resmi }}</h4>
    <p class="text-sm text-gray-500 mb-4">{{ $surat->jenisSurat->nama_jenis }}</p>

    <div class="mb-4">
        <strong>Perihal:</strong> {{ $surat->perihal }}<br>
        <strong>Sifat:</strong> {{ $surat->sifat }}<br>
        <strong>Dibuat Oleh:</strong> {{ $surat->pembuat->profil->nama_lengkap ?? '-' }}<br>
        <strong>Ditandatangani Oleh:</strong> {{ $surat->penandatangan->profil->nama_lengkap ?? '-' }}
    </div>

    <div class="p-4 bg-gray-50 border rounded-lg prose prose-sm max-w-none">
        {!! $surat->konten_html !!}
    </div>

    @if($surat->paraf->isNotEmpty())
        <div class="mt-4">
            <h5 class="text-sm font-semibold mb-2">Daftar Paraf:</h5>
            <ul class="text-sm text-gray-600">
                @foreach($surat->paraf as $p)
                    <li>
                        Paraf ke-{{ $p->urutan }}: {{ $p->pengguna->profil->nama_lengkap ?? '-' }} 
                        (<span class="text-green-600">Disetujui</span>)
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
