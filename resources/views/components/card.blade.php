@props(['class' => ''])

<div {{ $attributes->merge(['class' => 'bg-white rounded-2xl shadow-sm border border-blue-100/70 '.$class]) }}>
    {{ $slot }}
</div>
