@props(['icon' => '📋', 'message' => 'Belum ada data.', 'action' => null, 'actionLabel' => null, 'actionRoute' => null])

<div class="text-center py-16 px-4">
    <div class="text-5xl mb-4">{{ $icon }}</div>
    <p class="text-gray-500 text-sm">{{ $message }}</p>
    @if($action && $actionLabel && $actionRoute)
        <a href="{{ $actionRoute }}" class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700">
            {{ $actionLabel }}
        </a>
    @endif
</div>
