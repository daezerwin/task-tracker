@props(['title', 'subtitle' => null])

<div class="text-center py-14 px-4">
    <p class="text-gray-700 font-medium text-lg">{{ $title }}</p>
    @if ($subtitle)
        <p class="text-base text-gray-500 mt-1.5">{{ $subtitle }}</p>
    @endif
    @isset($action)
        <div class="mt-4">{{ $action }}</div>
    @endisset
</div>
