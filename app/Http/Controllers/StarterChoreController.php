<?php

namespace App\Http\Controllers;

use App\Services\StarterChores;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class StarterChoreController extends Controller
{
    public function __invoke(): RedirectResponse
    {
        $added = StarterChores::addTo(Auth::user());

        return redirect()->route('tasks.index')->with('status', $added
            ? 'Added '.$added.' starter '.Str::plural('chore', $added).'.'
            : 'You already have all the starter chores.');
    }
}
