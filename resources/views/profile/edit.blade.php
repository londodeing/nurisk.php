<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-slate-800 leading-tight tracking-tight flex items-center gap-2">
            <i class="bi bi-person-badge text-indigo-500"></i> {{ __('Profil Relawan NURISK') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row gap-8">
                
                <!-- Kiri: Biodata & Keahlian -->
                <div class="w-full lg:w-2/3 space-y-8">
                    <!-- Biodata Form -->
                    <div class="p-8 bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl relative overflow-hidden">
                        <div class="absolute top-0 right-0 -mr-16 -mt-16 w-48 h-48 rounded-full bg-indigo-500/10 blur-3xl pointer-events-none"></div>
                        <div class="relative z-10">
                            @include('profile.partials.update-biodata-form')
                        </div>
                    </div>

                    <!-- Keahlian Form -->
                    <div class="p-8 bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl relative overflow-hidden">
                        <div class="absolute top-0 left-0 -ml-16 -mt-16 w-48 h-48 rounded-full bg-emerald-500/10 blur-3xl pointer-events-none"></div>
                        <div class="relative z-10">
                            @include('profile.partials.update-keahlian-form')
                        </div>
                    </div>
                    
                    <!-- Submit Button Panel -->
                    <div class="p-6 bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl flex items-center justify-between">
                        <div>
                            @if (session('status') === 'profile-updated')
                                <p
                                    x-data="{ show: true }"
                                    x-show="show"
                                    x-transition
                                    x-init="setTimeout(() => show = false, 3000)"
                                    class="text-sm font-semibold text-emerald-600 bg-emerald-50 px-3 py-1 rounded-lg border border-emerald-100"
                                >
                                    <i class="bi bi-check-circle-fill mr-1"></i> {{ __('Biodata & Keahlian Berhasil Disimpan.') }}
                                </p>
                            @endif
                        </div>
                        <button type="submit" form="biodata-form" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-indigo-500 to-indigo-600 border border-transparent rounded-xl font-bold text-xs text-white uppercase tracking-widest hover:from-indigo-600 hover:to-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-lg shadow-indigo-500/30">
                            {{ __('Simpan Perubahan') }}
                        </button>
                    </div>
                </div>

                <!-- Kanan: Keamanan (Password) -->
                <div class="w-full lg:w-1/3 space-y-8">
                    
                    <!-- Password Update -->
                    <div class="p-8 bg-slate-900/90 backdrop-blur-xl border border-slate-700 shadow-xl rounded-2xl relative overflow-hidden">
                        <div class="absolute bottom-0 right-0 -mr-16 -mb-16 w-48 h-48 rounded-full bg-rose-500/10 blur-3xl pointer-events-none"></div>
                        <div class="relative z-10 text-white">
                            @include('profile.partials.update-password-form')
                        </div>
                    </div>
                    
                    <!-- Delete Account -->
                    <!-- Disembunyikan atau dihilangkan agar relawan tidak sembarangan menghapus akun (bisa disesuaikan policy organisasi) -->
                    {{-- <div class="p-8 bg-white shadow sm:rounded-lg">
                        <div class="max-w-xl">
                            @include('profile.partials.delete-user-form')
                        </div>
                    </div> --}}

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
