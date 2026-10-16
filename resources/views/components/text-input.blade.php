@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'm3-field']) }}>
