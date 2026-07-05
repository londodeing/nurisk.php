<section>
    <header class="mb-4">
        <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2">
            <i class="bi bi-tools text-emerald-500"></i> {{ __('Keahlian Khusus') }}
        </h2>
        <p class="mt-1 text-sm text-slate-500">
            {{ __('Pilih keahlian operasional atau penunjang yang Anda miliki.') }}
        </p>
    </header>

    @php
        // Dapatkan ID keahlian user saat ini
        $userKeahlianIds = $user->keahlian->pluck('id_keahlian')->toArray();
    @endphp

    <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
        @foreach($keahlianMaster as $keahlian)
            <label class="relative flex items-start p-4 cursor-pointer rounded-xl border border-slate-200 bg-white hover:bg-emerald-50 hover:border-emerald-200 transition-colors group">
                <div class="flex items-center h-5">
                    <input type="checkbox" name="keahlian[]" value="{{ $keahlian->id_keahlian }}" form="biodata-form"
                           class="w-5 h-5 text-emerald-600 bg-slate-100 border-slate-300 rounded focus:ring-emerald-500 focus:ring-2"
                           {{ in_array($keahlian->id_keahlian, old('keahlian', $userKeahlianIds)) ? 'checked' : '' }}>
                </div>
                <div class="ml-3 text-sm flex-1">
                    <span class="font-bold text-slate-800 block group-hover:text-emerald-700 transition-colors">{{ $keahlian->nama_keahlian }}</span>
                    <p id="keahlian-desc-{{ $keahlian->id_keahlian }}" class="text-xs font-normal text-slate-500 mt-0.5">
                        {{ Str::limit($keahlian->deskripsi, 80) }}
                    </p>
                </div>
            </label>
        @endforeach
    </div>
    
    @if($errors->has('keahlian'))
        <p class="mt-2 text-sm text-rose-600">{{ $errors->first('keahlian') }}</p>
    @endif
    
    <div class="mt-6 p-4 bg-blue-50 border border-blue-100 rounded-xl flex gap-3 text-sm text-blue-800">
        <i class="bi bi-info-circle-fill text-blue-500 text-lg"></i>
        <p>Keahlian yang Anda pilih akan membantu komandan posko dalam menugaskan Anda ke operasi spesifik yang membutuhkan keterampilan khusus.</p>
    </div>
</section>
