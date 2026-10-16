@props(['title', 'time', 'meta' => null, 'editUrl' => null, 'deleteUrl' => null, 'confirm' => 'Delete this entry?'])

{{-- Material two/three-line list item with trailing icon buttons --}}
<li class="flex min-h-[72px] items-center gap-2 rounded-2xl py-2 pl-4 pr-1 hover:bg-on-surface/[0.04]">
    <div class="min-w-0 flex-1">
        <p class="text-body-lg text-on-surface">{{ $title }}</p>
        <p class="text-body-md text-on-surface-variant">{{ $time }}@if($meta) &middot; {{ $meta }} @endif</p>
        @isset($notes)
            <p class="mt-0.5 text-body-sm text-outline">{{ $notes }}</p>
        @endisset
    </div>
    @if ($editUrl)
        <a href="{{ $editUrl }}" wire:navigate aria-label="Edit" title="Edit"
           class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-on-surface-variant hover:bg-on-surface/[0.08]">
            <span class="icon text-[20px]">edit</span>
        </a>
    @endif
    @if ($deleteUrl)
        <form method="POST" action="{{ $deleteUrl }}" onsubmit="return confirm('{{ $confirm }}');">
            @csrf
            @method('DELETE')
            <button type="submit" aria-label="Delete" title="Delete"
                    class="flex h-10 w-10 items-center justify-center rounded-full text-on-surface-variant hover:bg-error/[0.08] hover:text-error">
                <span class="icon text-[20px]">delete</span>
            </button>
        </form>
    @endif
</li>
