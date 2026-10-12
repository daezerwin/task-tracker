@props(['title', 'subtitle' => null, 'back' => null])

<div class="flex items-start justify-between gap-4">
    <div>
        @if ($back)
            <a href="{{ $back }}" wire:navigate class="text-sm text-blue-600 hover:text-blue-700 inline-flex items-center gap-1 mb-1">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
                Back
            </a>
        @endif
        <h2 class="font-semibold text-2xl sm:text-3xl text-gray-800 leading-tight">{{ $title }}</h2>
        @if ($subtitle)
            <p class="text-base text-gray-500 mt-1">{{ $subtitle }}</p>
        @endif
    </div>

    @isset($actions)
        <div class="flex items-center gap-2 shrink-0">
            {{ $actions }}
        </div>
    @endisset
</div>
