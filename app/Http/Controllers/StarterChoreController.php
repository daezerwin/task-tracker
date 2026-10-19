<?php

namespace App\Http\Controllers;

use App\Services\StarterChores;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class StarterChoreController extends Controller
{
    /**
     * Adds the starter chores picked in the wizard (see the
     * starter-chores-wizard component) and returns to wherever it was opened.
     */
    public function __invoke(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'templates' => ['required', 'array'],
            'templates.*' => ['integer'],
        ]);

        $added = StarterChores::addTo(Auth::user(), only: array_map('intval', $validated['templates']));

        return redirect()->back()->with('status', $added
            ? 'Added '.$added.' starter '.Str::plural('chore', $added).'.'
            : 'You already have those starter chores.');
    }
}
