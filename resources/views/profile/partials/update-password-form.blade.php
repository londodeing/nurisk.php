<section>
    <header>
        <h2 class="text-xl font-bold text-white flex items-center gap-2">
            <i class="bi bi-shield-lock text-rose-500"></i> {{ __('Ubah Kata Sandi') }}
        </h2>

        <p class="mt-1 text-sm text-slate-400">
            {{ __('Pastikan akun Anda menggunakan kata sandi yang panjang, acak, dan aman.') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        <div>
            <x-input-label for="update_password_current_password" :value="__('Kata Sandi Saat Ini')" class="text-slate-300" />
            <x-text-input id="update_password_current_password" name="current_password" type="password" class="mt-1 block w-full bg-slate-800/50 border-slate-600 text-white focus:border-rose-500 focus:ring-rose-500" autocomplete="current-password" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2 text-rose-400" />
        </div>

        <div>
            <x-input-label for="update_password_password" :value="__('Kata Sandi Baru')" class="text-slate-300" />
            <x-text-input id="update_password_password" name="password" type="password" class="mt-1 block w-full bg-slate-800/50 border-slate-600 text-white focus:border-rose-500 focus:ring-rose-500" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2 text-rose-400" />
        </div>

        <div>
            <x-input-label for="update_password_password_confirmation" :value="__('Konfirmasi Kata Sandi')" class="text-slate-300" />
            <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full bg-slate-800/50 border-slate-600 text-white focus:border-rose-500 focus:ring-rose-500" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2 text-rose-400" />
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="inline-flex items-center px-6 py-2.5 bg-gradient-to-r from-rose-500 to-rose-600 border border-transparent rounded-xl font-bold text-xs text-white uppercase tracking-widest hover:from-rose-600 hover:to-rose-700 focus:bg-rose-700 active:bg-rose-900 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 focus:ring-offset-slate-900 transition ease-in-out duration-150 shadow-lg shadow-rose-500/30">
                {{ __('Perbarui Sandi') }}
            </button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 3000)"
                    class="text-sm font-semibold text-rose-400 bg-rose-500/10 px-3 py-1 rounded-lg border border-rose-500/20"
                >
                    <i class="bi bi-check-circle-fill mr-1"></i> {{ __('Sandi Diperbarui.') }}
                </p>
            @endif
        </div>
    </form>
</section>
