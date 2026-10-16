@props(['title', 'subtitle' => null, 'back' => null])

<div class="flex items-center gap-2">
    @if ($back)
        <a href="{{ $back }}" wire:navigate aria-label="Back"
           class="-ml-2 flex h-12 w-12 shrink-0 items-center justify-center rounded-full text-on-surface hover:bg-on-surface/[0.08]">
            <span class="icon">arrow_back</span>
        </a>
    @endif
    <div class="min-w-0 flex-1">
        <h1 class="text-headline-sm sm:text-headline-lg text-on-surface">{{ $title }}</h1>
        @if ($subtitle)
            <p class="mt-1 text-body-lg text-on-surface-variant">{{ $subtitle }}</p>
        @endif
    </div>

    @isset($actions)
        <div class="flex shrink-0 items-center gap-2">
            {{ $actions }}
        </div>
    @endisset
</div>
