<?php

namespace App\View\Components;

use App\Models\ChoreTemplate;
use App\Models\Task;
use App\Services\StarterChores;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * A modal that walks through the starter chores the user doesn't have yet,
 * one at a time, asking whether to add or skip each. Open it with
 * $dispatch('open-modal', 'starter-chores').
 */
class StarterChoresWizard extends Component
{
    /** @var Collection<int, Task> unsaved previews keyed by template id */
    public Collection $chores;

    public function __construct()
    {
        $this->chores = StarterChores::missingFor(Auth::user())
            ->mapWithKeys(fn (ChoreTemplate $template) => [$template->id => StarterChores::taskFrom($template)]);
    }

    public function render(): View
    {
        return view('components.starter-chores-wizard');
    }
}
