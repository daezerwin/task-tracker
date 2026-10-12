@props(['title', 'time', 'meta' => null, 'editUrl' => null, 'deleteUrl' => null, 'confirm' => 'Delete this entry?'])

<li class="flex items-center justify-between gap-3 py-4">
    <div class="min-w-0">
        <p class="text-base font-medium text-gray-800">{{ $title }}</p>
        <p class="text-sm text-gray-500 mt-0.5">{{ $time }}@if($meta) &middot; {{ $meta }} @endif</p>
        @isset($notes)
            <p class="text-sm text-gray-400 mt-0.5">{{ $notes }}</p>
        @endisset
    </div>
    <div class="flex items-center gap-4 shrink-0">
        @if ($editUrl)
            <a href="{{ $editUrl }}" wire:navigate class="text-sm text-blue-600 hover:text-blue-700">Edit</a>
        @endif
        @if ($deleteUrl)
            <form method="POST" action="{{ $deleteUrl }}" onsubmit="return confirm('{{ $confirm }}');">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-sm text-gray-400 hover:text-red-600">Delete</button>
            </form>
        @endif
    </div>
</li>
