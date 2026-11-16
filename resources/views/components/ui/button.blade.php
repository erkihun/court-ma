@props([
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
    'type' => 'button',
])

@php
    // One look for buttons everywhere: these map to the .btn-* classes in resources/css/app.css.
    $variants = ['primary', 'secondary', 'danger', 'success', 'warn', 'ghost'];
    $variant = in_array($variant, $variants, true) ? $variant : 'primary';
    $sizes = ['sm' => 'btn-sm', 'md' => '', 'lg' => 'btn-lg'];
    $classes = trim('btn btn-'.$variant.' '.($sizes[$size] ?? ''));
    $tag = $href ? 'a' : 'button';
@endphp

<{{ $tag }}
    @if($href) href="{{ $href }}" @else type="{{ $type }}" @endif
    {{ $attributes->merge(['class' => $classes]) }}
>
    {{ $slot }}
</{{ $tag }}>
