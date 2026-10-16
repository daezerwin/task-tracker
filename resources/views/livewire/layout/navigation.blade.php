<?php

use App\Livewire\Actions\Logout;

$logout = function (Logout $logout) {
    $logout();

    $this->redirect('/', navigate: true);
};

?>

@php
    $destinations = [
        ['route' => 'dashboard', 'match' => 'dashboard', 'icon' => 'task_alt', 'label' => 'Today'],
        ['route' => 'calendar', 'match' => 'calendar', 'icon' => 'calendar_month', 'label' => 'Calendar'],
        ['route' => 'tasks.index', 'match' => 'tasks.*', 'icon' => 'checklist', 'label' => 'Tasks'],
    ];
@endphp

<div>
    <!-- Navigation rail (tablet and up) -->
    <nav class="hidden sm:flex fixed inset-y-0 left-0 z-30 w-20 flex-col items-center py-4 bg-surface" aria-label="Main">
        <a href="{{ route('dashboard') }}" wire:navigate aria-label="{{ config('app.name') }}">
            <x-application-logo class="w-10 h-10" />
        </a>

        {{-- Slot for the page's FAB (x-fab docks here on tablet and up). --}}
        <div class="mt-5 mb-7 h-14" aria-hidden="true"></div>

        <div class="flex flex-col items-center gap-3">
            @foreach ($destinations as $item)
                @php($active = request()->routeIs($item['match']))
                <a href="{{ route($item['route']) }}" wire:navigate class="group flex w-20 flex-col items-center gap-1" @if ($active) aria-current="page" @endif>
                    <span class="flex h-8 w-14 items-center justify-center rounded-full transition {{ $active ? 'bg-secondary-container text-on-secondary-container' : 'text-on-surface-variant group-hover:bg-on-surface/[0.08]' }}">
                        <span class="icon {{ $active ? 'icon-filled' : '' }}">{{ $item['icon'] }}</span>
                    </span>
                    <span class="text-label-md {{ $active ? 'text-on-surface' : 'text-on-surface-variant' }}">{{ $item['label'] }}</span>
                </a>
            @endforeach
        </div>

        <div class="mt-auto flex flex-col items-center gap-3">
            @php($active = request()->routeIs('profile'))
            <a href="{{ route('profile') }}" wire:navigate class="group flex w-20 flex-col items-center gap-1" @if ($active) aria-current="page" @endif>
                <span class="flex h-8 w-14 items-center justify-center rounded-full transition {{ $active ? 'bg-secondary-container text-on-secondary-container' : 'text-on-surface-variant group-hover:bg-on-surface/[0.08]' }}">
                    <span class="icon {{ $active ? 'icon-filled' : '' }}">person</span>
                </span>
                <span class="text-label-md {{ $active ? 'text-on-surface' : 'text-on-surface-variant' }}">Profile</span>
            </a>

            <button type="button" wire:click="logout" class="group flex w-20 flex-col items-center gap-1">
                <span class="flex h-8 w-14 items-center justify-center rounded-full text-on-surface-variant transition group-hover:bg-on-surface/[0.08]">
                    <span class="icon">logout</span>
                </span>
                <span class="text-label-md text-on-surface-variant">Log out</span>
            </button>
        </div>
    </nav>

    <!-- Top app bar (phones) -->
    <header class="sm:hidden flex h-16 items-center gap-3 px-4 bg-surface">
        <x-application-logo class="w-8 h-8" />
        <span class="flex-1 text-title-lg text-on-surface">{{ config('app.name', 'Task Tracker') }}</span>
        <button type="button" wire:click="logout" aria-label="Log out"
                class="flex h-12 w-12 items-center justify-center rounded-full text-on-surface-variant hover:bg-on-surface/[0.08]">
            <span class="icon">logout</span>
        </button>
    </header>
</div>
