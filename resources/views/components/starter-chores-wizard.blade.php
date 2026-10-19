{{--
    One starter chore per step: Skip or Add, then a review step that submits
    only the added ones. Nothing is saved until that last step, so closing the
    dialog part-way loses nothing — reopening it picks up where it left off.
    Buttons that come and go use x-if rather than x-show so the modal's focus
    trap never lands on a hidden one.
--}}
<x-modal name="starter-chores" max-width="md">
    @if ($chores->isEmpty())
        <div class="p-6">
            <h2 class="text-headline-sm text-on-surface">Starter chores</h2>
            <p class="mt-4 text-body-lg text-on-surface-variant">You already have every starter chore.</p>
            <div class="mt-6 flex justify-end">
                <button type="button" x-on:click="$dispatch('close-modal', 'starter-chores')"
                        class="h-10 px-4 rounded-full text-label-lg text-primary hover:bg-primary/[0.08]">Close</button>
            </div>
        </div>
    @else
        <form method="POST" action="{{ route('tasks.starter') }}" class="p-6"
              x-data="{
                  step: 0,
                  total: {{ $chores->count() }},
                  decisions: [],
                  submitting: false,
                  get addedCount() {
                      return this.decisions.filter(Boolean).length;
                  },
                  decide(add) {
                      this.decisions[this.step] = add;
                      this.step++;
                  },
              }"
              x-on:submit="submitting = true">
            @csrf

            <div class="flex items-center gap-2">
                <h2 class="min-w-0 flex-1 text-headline-sm text-on-surface">Starter chores</h2>
                <span x-show="step < total" x-text="`${step + 1} of ${total}`"
                      class="shrink-0 text-body-md tabular-nums text-on-surface-variant"></span>
                <button type="button" x-on:click="$dispatch('close-modal', 'starter-chores')" aria-label="Close" title="Close"
                        class="-mr-2 flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-on-surface-variant hover:bg-on-surface/[0.08]">
                    <span class="icon text-[20px]">close</span>
                </button>
            </div>
            <div class="mt-3 h-1 overflow-hidden rounded-full bg-surface-highest">
                <div class="h-full rounded-full bg-primary transition-all duration-300" x-bind:style="`width: ${step / total * 100}%`"></div>
            </div>

            @foreach ($chores as $templateId => $task)
                <div x-show="step === {{ $loop->index }}" class="mt-6 min-h-[136px]">
                    <x-category-badge :category="$task->category" />
                    <p class="mt-3 text-title-lg text-on-surface">{{ $task->title }}</p>
                    <p class="mt-1 flex items-center gap-1.5 text-body-md text-on-surface-variant">
                        <span class="icon text-[18px]">repeat</span>
                        {{ $task->scheduleLabel() }}@if ($task->timeLabel()) &middot; {{ $task->timeLabel() }}@endif
                    </p>
                    @if ($task->notes)
                        <p class="mt-3 text-body-md text-on-surface-variant">{{ $task->notes }}</p>
                    @endif

                    {{-- Disabled inputs aren't submitted, so only added chores reach the server. --}}
                    <input type="hidden" name="templates[]" value="{{ $templateId }}" x-bind:disabled="! decisions[{{ $loop->index }}]">
                </div>
            @endforeach

            <div x-show="step === total" class="mt-6 min-h-[136px]">
                <p class="text-title-lg text-on-surface"
                   x-text="addedCount ? `Add ${addedCount} ${addedCount === 1 ? 'chore' : 'chores'}?` : 'Nothing to add'"></p>
                <ul x-show="addedCount" class="mt-3 max-h-64 space-y-2 overflow-y-auto">
                    @foreach ($chores as $task)
                        <li x-show="decisions[{{ $loop->index }}]" class="flex items-center gap-3 text-body-md text-on-surface">
                            <span class="h-2 w-2 shrink-0 rounded-full {{ $task->category->dotClass() }}"></span>
                            <span class="min-w-0 flex-1 truncate">{{ $task->title }}</span>
                        </li>
                    @endforeach
                </ul>
                <p x-show="addedCount < total" class="mt-3 text-body-md text-on-surface-variant"
                   x-text="`${addedCount ? total - addedCount : 'All'} skipped — you can add skipped chores later from the Tasks page.`"></p>
            </div>

            <div class="mt-6 flex items-center gap-2">
                <button type="button" x-on:click="step--" x-bind:disabled="step === 0 || submitting"
                        class="h-10 px-4 rounded-full text-label-lg text-primary hover:bg-primary/[0.08] disabled:opacity-40 disabled:hover:bg-transparent">
                    Back
                </button>
                <span class="flex-1"></span>

                <template x-if="step < total">
                    <div class="flex gap-2">
                        <x-secondary-button x-on:click="decide(false)">Skip</x-secondary-button>
                        <x-primary-button type="button" x-on:click="decide(true)">
                            <span class="icon text-[18px]">add</span> Add
                        </x-primary-button>
                    </div>
                </template>
                <template x-if="step === total && addedCount > 0">
                    <x-primary-button x-bind:disabled="submitting">
                        <span class="icon text-[18px]">check</span> Add chores
                    </x-primary-button>
                </template>
                <template x-if="step === total && addedCount === 0">
                    <x-secondary-button x-on:click="$dispatch('close-modal', 'starter-chores')">Close</x-secondary-button>
                </template>
            </div>
        </form>
    @endif
</x-modal>
