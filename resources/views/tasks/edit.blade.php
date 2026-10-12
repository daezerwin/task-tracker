<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Edit Task" :back="route('tasks.index')" />
    </x-slot>

    <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <x-card class="p-10">
            <form method="POST" action="{{ route('tasks.update', $task) }}" class="space-y-5">
                @csrf
                @method('PUT')
                @include('tasks._fields', ['task' => $task])

                <div>
                    <input type="hidden" name="is_active" value="0">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $task->is_active))
                               class="mt-1 w-5 h-5 rounded border-gray-300 text-blue-600 focus:ring-blue-600">
                        <span>
                            <span class="block font-medium text-base text-gray-700">Active</span>
                            <span class="block text-sm text-gray-500">Untick to pause this task without deleting it or its history.</span>
                        </span>
                    </label>
                </div>

                <div class="flex justify-end gap-2">
                    <a href="{{ route('tasks.index') }}" wire:navigate><x-secondary-button type="button">Cancel</x-secondary-button></a>
                    <x-primary-button>Save</x-primary-button>
                </div>
            </form>
        </x-card>
    </div>
</x-app-layout>
