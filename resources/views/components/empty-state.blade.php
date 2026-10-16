@props(['title', 'subtitle' => null, 'icon' => 'task_alt'])

<div class="px-4 py-12 text-center">
    <span class="icon text-[40px] text-on-surface-variant">{{ $icon }}</span>
    <p class="mt-3 text-title-lg text-on-surface">{{ $title }}</p>
    @if ($subtitle)
        <p class="mt-1 text-body-lg text-on-surface-variant">{{ $subtitle }}</p>
    @endif
    @isset($action)
        <div class="mt-6">{{ $action }}</div>
    @endisset
</div>
