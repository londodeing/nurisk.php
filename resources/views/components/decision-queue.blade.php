@props(['queue'])

<ul class="divide-y divide-slate-100" id="decision-queue-list">
    @forelse($queue as $item)
        @php
            $icon = 'bi-exclamation-circle text-slate-500';
            $btnColor = 'text-slate-600 border-slate-300 hover:bg-slate-50 focus:ring-slate-500';
            
            if ($item['priority'] === 'critical') { 
                $icon = 'bi-exclamation-octagon-fill text-red-500'; 
                $btnColor = 'text-red-600 border-red-200 hover:bg-red-50 focus:ring-red-500 bg-red-50/50'; 
            }
            if ($item['priority'] === 'high') { 
                $icon = 'bi-exclamation-triangle-fill text-yellow-500'; 
                $btnColor = 'text-yellow-600 border-yellow-200 hover:bg-yellow-50 focus:ring-yellow-500 bg-yellow-50/50'; 
            }
        @endphp
        <li class="flex items-center justify-between p-4 hover:bg-slate-50 transition-colors">
            <div class="flex items-center gap-3">
                <i class="bi {{ $icon }} text-xl"></i>
                <span class="font-medium text-slate-800">{{ $item['title'] }}</span>
            </div>
            <a href="{{ $item['action_url'] }}" class="inline-flex items-center px-3 py-1.5 text-xs font-semibold uppercase tracking-wider border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors {{ $btnColor }}">
                TINDAK
            </a>
        </li>
    @empty
        <li class="py-8 text-center">
            <div class="inline-flex items-center justify-center w-12 h-12 mb-3 rounded-full bg-green-100 text-green-600">
                <i class="bi bi-check-lg text-2xl"></i>
            </div>
            <p class="text-sm font-medium text-slate-500">Tidak ada antrean keputusan mendesak.</p>
        </li>
    @endforelse
</ul>
