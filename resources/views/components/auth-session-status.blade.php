@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'text-body-md text-tertiary']) }}>
        {{ $status }}
    </div>
@endif
