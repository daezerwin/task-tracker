<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Edit task" :back="route('tasks.index')" />
    </x-slot>

    <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <x-card class="p-6 sm:p-8">
            <form method="POST" action="{{ route('tasks.update', $task) }}" class="space-y-6">
                @csrf
                @method('PUT')
                @include('tasks._fields', ['task' => $task])

                <!-- Material switch -->
                <label class="flex cursor-pointer items-center justify-between gap-4">
                    <span>
                        <span class="block text-body-lg text-on-surface">Active</span>
                        <span class="block text-body-md text-on-surface-variant">Turn off to pause this task without deleting it or its history.</span>
                    </span>
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" class="peer sr-only" @checked(old('is_active', $task->is_active))>
                    <span class="relative h-8 w-[52px] shrink-0 rounded-full border-2 border-outline bg-surface-highest transition-colors
                                 after:absolute after:left-[6px] after:top-1/2 after:h-4 after:w-4 after:-translate-y-1/2 after:rounded-full after:bg-outline after:transition-all
                                 peer-checked:border-primary peer-checked:bg-primary
                                 peer-checked:after:left-[22px] peer-checked:after:h-6 peer-checked:after:w-6 peer-checked:after:bg-on-primary
                                 peer-focus-visible:ring-2 peer-focus-visible:ring-primary peer-focus-visible:ring-offset-2 peer-focus-visible:ring-offset-surface-container"></span>
                </label>

                <div class="flex justify-end gap-2 pt-2">
                    <a href="{{ route('tasks.index') }}" wire:navigate><x-secondary-button type="button">Cancel</x-secondary-button></a>
                    <x-primary-button>Save</x-primary-button>
                </div>
            </form>
        </x-card>
    </div>
</x-app-layout>
