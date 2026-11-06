@props([
    'variant' => 'icon', // 'icon' or 'full'
])

@php
    $src = $variant === 'full'
        ? asset('images/logo-infra-daya-transparent.png')
        : asset('images/logo-icon.png');
@endphp

<img
    src="{{ $src }}"
    alt="Logo INFRA-DAYA"
    {{ $attributes->merge(['class' => 'object-contain inline-block']) }}
>
