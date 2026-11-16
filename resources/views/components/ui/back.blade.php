@props(['href' => null])

{{-- The standard back link: <x-ui.back :href="route('cases.index')" /> --}}
<a href="{{ $href ?? url()->previous() }}" {{ $attributes->merge(['class' => 'ui-back']) }}>
    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
    {{ $slot->isEmpty() ? __('cases.back') : $slot }}
</a>
