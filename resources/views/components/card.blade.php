@props(['class' => ''])

{{-- Material filled card --}}
<div {{ $attributes->merge(['class' => 'bg-surface-container rounded-3xl '.$class]) }}>
    {{ $slot }}
</div>
