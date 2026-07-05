@if (session('success'))
    <div x-data="{ show: true }" x-show="show" x-transition class="flex items-center justify-between p-4 mb-6 bg-green-50 border border-green-200 rounded-lg shadow-sm">
        <div class="flex items-center">
            <i class="bi bi-check-circle-fill text-green-500 text-xl mr-3"></i>
            <span class="text-sm font-medium text-green-800">{{ session('success') }}</span>
        </div>
        <button @click="show = false" class="text-green-500 hover:text-green-700 transition">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
@endif

@if (session('error') || $errors->any())
    <div x-data="{ show: true }" x-show="show" x-transition class="flex items-center justify-between p-4 mb-6 bg-red-50 border border-red-200 rounded-lg shadow-sm">
        <div class="flex items-start">
            <i class="bi bi-exclamation-triangle-fill text-red-500 text-xl mr-3 mt-0.5"></i>
            <div>
                <span class="block text-sm font-medium text-red-800">{{ session('error') ?? 'Terjadi kesalahan saat memproses permintaan Anda.' }}</span>
                @if($errors->any())
                    <ul class="mt-1 list-disc list-inside text-xs text-red-700">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
        <button @click="show = false" class="text-red-500 hover:text-red-700 transition self-start mt-0.5">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
@endif
