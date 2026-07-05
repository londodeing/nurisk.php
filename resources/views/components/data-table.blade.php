@props(['empty' => false, 'emptyMessage' => 'Belum ada data.', 'emptyIcon' => '📋'])

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    {{ $head }}
                </tr>
            </thead>
            @if(!$empty)
            <tbody class="divide-y divide-gray-100 bg-white">
                {{ $body }}
            </tbody>
            @endif
        </table>
        @if($empty)
        <div class="text-center py-16 px-4">
            <div class="text-5xl mb-4">{{ $emptyIcon }}</div>
            <p class="text-gray-500 text-sm">{{ $emptyMessage }}</p>
        </div>
        @endif
    </div>
    @if(isset($footer))
    <div class="px-4 py-3 border-t border-gray-200 bg-gray-50">
        {{ $footer }}
    </div>
    @endif
</div>
