@if(session('success') || session('error') || session('info'))
<div
  x-data="{ show: true }"
  x-init="setTimeout(() => show = false, 4000)"
  x-show="show"
  x-transition
  class="fixed bottom-6 right-6 z-50 px-4 py-3 rounded-lg shadow-lg text-sm font-medium text-white
    @if(session('success')) bg-green-600
    @elseif(session('error')) bg-red-600
    @else bg-blue-600 @endif">
  {{ session('success') ?? session('error') ?? session('info') }}
</div>
@endif
