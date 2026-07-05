@props(['actions' => []])

@if(count($actions) > 0)
<div class="mb-3">
    <div class="d-flex flex-wrap gap-2 cc-quick-actions">
        @foreach($actions as $action)
            <a href="{{ $action['route'] ?? '#' }}"
               class="btn btn-{{ $action['color'] ?? 'primary' }} btn-sm"
               data-cc-action="{{ $action['action'] ?? '' }}">
                <i class="bi {{ $action['icon'] ?? '' }} me-1"></i>
                {{ $action['label'] ?? '' }}
            </a>
        @endforeach
    </div>
</div>
@endif
