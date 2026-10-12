<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Add Task" :back="route('tasks.index')" />
    </x-slot>

    <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <x-card class="p-10">
            <form method="POST" action="{{ route('tasks.store') }}" class="space-y-5">
                @csrf
                @include('tasks._fields', ['defaultDate' => $date])
                <div class="flex justify-end gap-2">
                    <a href="{{ route('tasks.index') }}" wire:navigate><x-secondary-button type="button">Cancel</x-secondary-button></a>
                    <x-primary-button>Save</x-primary-button>
                </div>
            </form>
        </x-card>
    </div>
</x-app-layout>
