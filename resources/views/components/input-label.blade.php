@props(['value'])

<label {{ $attributes->merge(['class' => 'block text-label-lg text-on-surface-variant mb-1.5']) }}>
    {{ $value ?? $slot }}
</label>
