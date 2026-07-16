<x-app-layout>
    <x-slot name="header">Edit Assessment</x-slot>

    <div class="mb-6 p-4 bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl rounded-2xl flex flex-col md:flex-row justify-between items-center gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('insiden.assessment.show', [$insiden->kode_kejadian, $assessment->id_assessment_utama]) }}"
               class="p-2 bg-slate-100 text-slate-600 rounded-xl hover:bg-slate-200 transition-colors">
                <i class="bi bi-arrow-left text-xl"></i>
            </a>
            <div class="p-3 bg-amber-100 text-amber-600 rounded-xl">
                <i class="bi bi-pencil-square text-2xl"></i>
            </div>
            <div>
                <h2 class="text-xl font-bold text-slate-800">Edit Assessment</h2>
                <p class="text-sm text-slate-500">Insiden #{{ $insiden->kode_kejadian }}</p>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-600 rounded-xl">
            <div class="font-bold mb-2"><i class="bi bi-exclamation-triangle-fill"></i> Terdapat Kesalahan:</div>
            <ul class="list-disc pl-5 text-sm space-y-1">
                @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('insiden.assessment.update', [$insiden->kode_kejadian, $assessment->id_assessment_utama]) }}"
          id="assessment-edit-form" x-data="wizard" onsubmit="return validateEditForm()" novalidate>
        @csrf
        @method('PUT')
        <input type="hidden" name="uuid_insiden" value="{{ $insiden->uuid_insiden }}">

        @include('partials.assessment.form-fields')
    </form>
</x-app-layout>
