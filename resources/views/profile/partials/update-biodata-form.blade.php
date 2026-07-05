<section>
    <header class="mb-6">
        <h2 class="text-xl font-bold text-slate-800">
            {{ __('Informasi Biodata') }}
        </h2>
        <p class="mt-1 text-sm text-slate-500">
            {{ __("Perbarui data pribadi, informasi kontak, dan rekam jejak Anda sebagai relawan NURISK.") }}
        </p>
    </header>

    <form method="post" action="{{ route('profile.update') }}" class="space-y-6">
        @csrf
        @method('patch')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- NIK -->
            <div>
                <x-input-label for="nik" value="Nomor Induk Kependudukan (NIK)" />
                <x-text-input id="nik" name="nik" type="text" class="mt-1 block w-full bg-slate-50 border-slate-200 focus:border-indigo-500 focus:ring-indigo-500" :value="old('nik', optional($user->profil)->nik)" required />
                <x-input-error class="mt-2" :messages="$errors->get('nik')" />
            </div>

            <!-- Nama Lengkap -->
            <div>
                <x-input-label for="nama_lengkap" value="Nama Lengkap" />
                <x-text-input id="nama_lengkap" name="nama_lengkap" type="text" class="mt-1 block w-full bg-slate-50 border-slate-200 focus:border-indigo-500 focus:ring-indigo-500" :value="old('nama_lengkap', optional($user->profil)->nama_lengkap)" required autocomplete="name" />
                <x-input-error class="mt-2" :messages="$errors->get('nama_lengkap')" />
            </div>

            <!-- Email -->
            <div>
                <x-input-label for="email" value="Alamat Email" />
                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full bg-slate-50 border-slate-200 focus:border-indigo-500 focus:ring-indigo-500" :value="old('email', optional($user->profil)->email)" required autocomplete="email" />
                <x-input-error class="mt-2" :messages="$errors->get('email')" />
            </div>

            <!-- Profesi -->
            <div>
                <x-input-label for="profesi" value="Profesi Saat Ini" />
                <x-text-input id="profesi" name="profesi" type="text" class="mt-1 block w-full bg-slate-50 border-slate-200 focus:border-indigo-500 focus:ring-indigo-500" :value="old('profesi', optional($user->profil)->profesi)" />
                <x-input-error class="mt-2" :messages="$errors->get('profesi')" />
            </div>

            <!-- Tempat Lahir -->
            <div>
                <x-input-label for="tempat_lahir" value="Tempat Lahir" />
                <x-text-input id="tempat_lahir" name="tempat_lahir" type="text" class="mt-1 block w-full bg-slate-50 border-slate-200 focus:border-indigo-500 focus:ring-indigo-500" :value="old('tempat_lahir', optional($user->profil)->tempat_lahir)" />
                <x-input-error class="mt-2" :messages="$errors->get('tempat_lahir')" />
            </div>

            <!-- Tanggal Lahir -->
            <div>
                <x-input-label for="tanggal_lahir" value="Tanggal Lahir" />
                <x-text-input id="tanggal_lahir" name="tanggal_lahir" type="date" class="mt-1 block w-full bg-slate-50 border-slate-200 focus:border-indigo-500 focus:ring-indigo-500" :value="old('tanggal_lahir', optional($user->profil)->tanggal_lahir ? optional($user->profil)->tanggal_lahir->format('Y-m-d') : '')" />
                <x-input-error class="mt-2" :messages="$errors->get('tanggal_lahir')" />
            </div>

            <!-- Jenis Kelamin -->
            <div>
                <x-input-label for="jenis_kelamin" value="Jenis Kelamin" />
                <select id="jenis_kelamin" name="jenis_kelamin" class="mt-1 block w-full bg-slate-50 border-slate-200 text-slate-700 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">-- Pilih --</option>
                    <option value="L" {{ old('jenis_kelamin', optional($user->profil)->jenis_kelamin) == 'L' ? 'selected' : '' }}>Laki-laki</option>
                    <option value="P" {{ old('jenis_kelamin', optional($user->profil)->jenis_kelamin) == 'P' ? 'selected' : '' }}>Perempuan</option>
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('jenis_kelamin')" />
            </div>
        </div>

        <!-- Alamat Lengkap -->
        <div>
            <x-input-label for="alamat" value="Alamat Domisili Lengkap" />
            <textarea id="alamat" name="alamat" rows="3" class="mt-1 block w-full bg-slate-50 border-slate-200 text-slate-700 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('alamat', optional($user->profil)->alamat) }}</textarea>
            <x-input-error class="mt-2" :messages="$errors->get('alamat')" />
        </div>

        <!-- Pengalaman Kebencanaan -->
        <div>
            <x-input-label for="pengalaman_kebencanaan" value="Catatan Pengalaman Kebencanaan" />
            <textarea id="pengalaman_kebencanaan" name="pengalaman_kebencanaan" rows="4" class="mt-1 block w-full bg-slate-50 border-slate-200 text-slate-700 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Tulis riwayat operasi kemanusiaan yang pernah Anda ikuti (jika ada)">{{ old('pengalaman_kebencanaan', optional($user->profil)->pengalaman_kebencanaan) }}</textarea>
            <x-input-error class="mt-2" :messages="$errors->get('pengalaman_kebencanaan')" />
            <p class="mt-1 text-xs text-slate-400">Kosongkan jika belum memiliki pengalaman.</p>
        </div>

        <!-- Keahlian Selection (Hidden in this form, processed together via JS or just submit separate? Wait, if it's the same form action, we should put the Keahlian fields in the other include and just use one submit button, but HTML doesn't allow form inside form. Actually, Keahlian is included as a separate section in edit.blade.php. We need to structure it so they can be submitted. The easiest is to use 'form="biodata-form"' attribute on the keahlian inputs or merge them into one form). Let's use form="biodata-form". -->
        <!-- Adding ID to this form -->
    </form>
</section>

<!-- Fix for separating forms: Since edit.blade.php includes both partials in different divs, we should give this form an ID and add form="biodata-form" to inputs in the other partial, or just change the layout in edit.blade.php so they are wrapped in a single <form>. For simplicity, I'll update edit.blade.php to wrap them in one form. Wait, I already overwrote edit.blade.php. I will just give this form id="biodata-form" and the Keahlian partial will use form="biodata-form". Wait, submit button needs to be shared too. -->
<script>
    document.querySelector('form[action="{{ route('profile.update') }}"]').setAttribute('id', 'biodata-form');
</script>
