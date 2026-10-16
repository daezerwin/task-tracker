@props(['href' => null, 'icon' => 'add', 'label' => null])

@php
    // Material FAB. Phones: extended, floating bottom-right above the nav bar.
    // Tablet and up: icon-only, docked at the top of the navigation rail
    // (the rail leaves a slot for it — see livewire/layout/navigation).
    $classes = 'fixed z-40 right-4 bottom-24 sm:right-auto sm:bottom-auto sm:left-3 sm:top-[76px] inline-flex h-14 items-center gap-3 rounded-2xl bg-primary-container text-on-primary-container shadow-lg shadow-black/50 transition hover:brightness-110 hover:shadow-xl focus:outline-none focus-visible:ring-2 focus-visible:ring-primary '
        .($label ? 'pl-4 pr-5 sm:w-14 sm:justify-center sm:px-0' : 'w-14 justify-center');
@endphp

@if ($href)
    <a href="{{ $href }}" wire:navigate @if ($label) aria-label="{{ $label }}" title="{{ $label }}" @endif {{ $attributes->merge(['class' => $classes]) }}>
        <span class="icon">{{ $icon }}</span>
        @if ($label)<span class="text-label-lg sm:hidden">{{ $label }}</span>@endif
    </a>
@else
    <button type="button" @if ($label) aria-label="{{ $label }}" title="{{ $label }}" @endif {{ $attributes->merge(['class' => $classes]) }}>
        <span class="icon">{{ $icon }}</span>
        @if ($label)<span class="text-label-lg sm:hidden">{{ $label }}</span>@endif
    </button>
@endif
