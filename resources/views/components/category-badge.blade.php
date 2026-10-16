@props(['category'])

{{-- Material assist chip (small) with a category colour dot --}}
<span {{ $attributes->merge(['class' => 'inline-flex h-6 items-center gap-1.5 rounded-lg border border-outline-variant px-2 text-label-md text-on-surface-variant']) }}>
    <span class="h-2 w-2 rounded-full {{ $category->dotClass() }}"></span>
    {{ $category->label() }}
</span>
