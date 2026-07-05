<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-slate-800 leading-tight tracking-tight">
            {{ __('Dashboard Portal') }}
        </h2>
    </x-slot>

    @inject('authCtx', 'App\Services\Auth\AuthorizationContextService')

    <div class="py-8">
        <div class="max-w-7xl mx-auto space-y-8">
            
            <div class="bg-white/60 backdrop-blur-xl border border-white/40 shadow-2xl rounded-3xl overflow-hidden relative">
                <!-- Decorative background elements -->
                <div class="absolute top-0 right-0 -mr-20 -mt-20 w-64 h-64 rounded-full bg-blue-500/10 blur-3xl pointer-events-none"></div>
                <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-80 h-80 rounded-full bg-emerald-500/10 blur-3xl pointer-events-none"></div>

                <div class="p-8 md:p-10 relative z-10">
                    <div class="mb-8">
                        <h3 class="text-2xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-slate-800 to-slate-500 mb-2">Akses Cepat Modul NURISK</h3>
                        <p class="text-slate-500">Pilih modul dashboard sesuai dengan peran dan penugasan Anda.</p>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        
                        <!-- Laporan Masuk (Verifikasi) -->
                        @can('viewAny', App\Models\OperasiInsiden::class)
                        <a href="{{ route('dashboard.laporan.index') }}" class="group relative block p-6 bg-white/80 backdrop-blur border border-blue-100 rounded-2xl hover:bg-gradient-to-br hover:from-blue-50 hover:to-indigo-50 transition-all duration-300 shadow-sm hover:shadow-xl hover:-translate-y-1">
                            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                                <i class="bi bi-inbox-fill text-6xl text-blue-600"></i>
                            </div>
                            <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center mb-4 shadow-inner">
                                <i class="bi bi-shield-check text-2xl"></i>
                            </div>
                            <div class="font-bold text-slate-800 text-xl mb-2 group-hover:text-blue-700 transition-colors">Verifikasi Laporan Masuk</div>
                            <div class="text-sm text-slate-500 leading-relaxed">Tinjau laporan kejadian dari publik dan tingkatkan menjadi Operasi Insiden.</div>
                        </a>
                        @endcan

                        <!-- Command Center -->
                        @if($authCtx->hasAnyRole(['super_admin', 'pwnu', 'pcnu', 'posko_commander']))
                        <a href="{{ route('command-center') }}" class="group relative block p-6 bg-slate-900 backdrop-blur border border-slate-700 rounded-2xl hover:bg-slate-800 transition-all duration-300 shadow-lg hover:shadow-2xl hover:shadow-red-500/20 hover:-translate-y-1">
                            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-30 transition-opacity">
                                <i class="bi bi-globe-asia-australia text-6xl text-red-500"></i>
                            </div>
                            <div class="w-12 h-12 bg-red-500/20 text-red-400 rounded-xl flex items-center justify-center mb-4 shadow-inner border border-red-500/30">
                                <i class="bi bi-display text-2xl"></i>
                            </div>
                            <div class="font-bold text-white text-xl mb-2 group-hover:text-red-400 transition-colors">Command Center</div>
                            <div class="text-sm text-slate-400 leading-relaxed">Pusat komando dan pemantauan situasi insiden secara real-time.</div>
                        </a>
                        @endif

                        <!-- PWNU Dashboard -->
                        @if($authCtx->hasAnyRole(['super_admin', 'pwnu']))
                        <a href="{{ route('dashboard.pwnu') }}" class="group relative block p-6 bg-white/80 backdrop-blur border border-emerald-100 rounded-2xl hover:bg-gradient-to-br hover:from-emerald-50 hover:to-teal-50 transition-all duration-300 shadow-sm hover:shadow-xl hover:-translate-y-1">
                            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                                <i class="bi bi-building-fill text-6xl text-emerald-600"></i>
                            </div>
                            <div class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center mb-4 shadow-inner">
                                <i class="bi bi-building text-2xl"></i>
                            </div>
                            <div class="font-bold text-slate-800 text-xl mb-2 group-hover:text-emerald-700 transition-colors">Dashboard PWNU</div>
                            <div class="text-sm text-slate-500 leading-relaxed">Executive Dashboard untuk tingkat wilayah provinsi PWNU.</div>
                        </a>
                        @endif

                        <!-- PCNU Dashboard -->
                        @if($authCtx->hasAnyRole(['super_admin', 'pwnu', 'pcnu']))
                        <a href="{{ route('dashboard.pcnu') }}" class="group relative block p-6 bg-white/80 backdrop-blur border border-cyan-100 rounded-2xl hover:bg-gradient-to-br hover:from-cyan-50 hover:to-sky-50 transition-all duration-300 shadow-sm hover:shadow-xl hover:-translate-y-1">
                            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                                <i class="bi bi-diagram-3-fill text-6xl text-cyan-600"></i>
                            </div>
                            <div class="w-12 h-12 bg-cyan-100 text-cyan-600 rounded-xl flex items-center justify-center mb-4 shadow-inner">
                                <i class="bi bi-diagram-3 text-2xl"></i>
                            </div>
                            <div class="font-bold text-slate-800 text-xl mb-2 group-hover:text-cyan-700 transition-colors">Dashboard PCNU</div>
                            <div class="text-sm text-slate-500 leading-relaxed">Mission Coordination Dashboard untuk tingkat cabang PCNU.</div>
                        </a>
                        @endif

                        <!-- Posko Dashboard -->
                        @if($authCtx->hasAnyRole(['super_admin', 'pwnu', 'pcnu', 'posko']))
                        <a href="{{ route('dashboard.posko') }}" class="group relative block p-6 bg-white/80 backdrop-blur border border-amber-100 rounded-2xl hover:bg-gradient-to-br hover:from-amber-50 hover:to-orange-50 transition-all duration-300 shadow-sm hover:shadow-xl hover:-translate-y-1">
                            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                                <i class="bi bi-house-fill text-6xl text-amber-600"></i>
                            </div>
                            <div class="w-12 h-12 bg-amber-100 text-amber-600 rounded-xl flex items-center justify-center mb-4 shadow-inner">
                                <i class="bi bi-house-door text-2xl"></i>
                            </div>
                            <div class="font-bold text-slate-800 text-xl mb-2 group-hover:text-amber-700 transition-colors">Dashboard Posko</div>
                            <div class="text-sm text-slate-500 leading-relaxed">Pusat operasional dan koordinasi posko darurat di lapangan.</div>
                        </a>
                        @endif

                        <!-- Operator Dashboard -->
                        @if($authCtx->hasAnyRole(['super_admin', 'posko']))
                        <a href="{{ route('dashboard.operator') }}" class="group relative block p-6 bg-white/80 backdrop-blur border border-purple-100 rounded-2xl hover:bg-gradient-to-br hover:from-purple-50 hover:to-fuchsia-50 transition-all duration-300 shadow-sm hover:shadow-xl hover:-translate-y-1">
                            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                                <i class="bi bi-keyboard-fill text-6xl text-purple-600"></i>
                            </div>
                            <div class="w-12 h-12 bg-purple-100 text-purple-600 rounded-xl flex items-center justify-center mb-4 shadow-inner">
                                <i class="bi bi-journal-text text-2xl"></i>
                            </div>
                            <div class="font-bold text-slate-800 text-xl mb-2 group-hover:text-purple-700 transition-colors">Dashboard Operator</div>
                            <div class="text-sm text-slate-500 leading-relaxed">Data Entry Center untuk Situation Report dan Assessment.</div>
                        </a>
                        @endif

                        <!-- TRC Dashboard -->
                        @if($authCtx->hasAnyRole(['super_admin', 'trc']))
                        <a href="{{ route('dashboard.trc') }}" class="group relative block p-6 bg-white/80 backdrop-blur border border-rose-100 rounded-2xl hover:bg-gradient-to-br hover:from-rose-50 hover:to-pink-50 transition-all duration-300 shadow-sm hover:shadow-xl hover:-translate-y-1">
                            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                                <i class="bi bi-person-bounding-box text-6xl text-rose-600"></i>
                            </div>
                            <div class="w-12 h-12 bg-rose-100 text-rose-600 rounded-xl flex items-center justify-center mb-4 shadow-inner">
                                <i class="bi bi-truck text-2xl"></i>
                            </div>
                            <div class="font-bold text-slate-800 text-xl mb-2 group-hover:text-rose-700 transition-colors">Dashboard TRC</div>
                            <div class="text-sm text-slate-500 leading-relaxed">Mobile-first dashboard untuk Tim Reaksi Cepat di lapangan.</div>
                        </a>
                        @endif

                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
