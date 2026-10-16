<?php

use App\Http\Controllers\StarterChoreController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

// No landing page: guests end up on the login screen via the dashboard's auth middleware.
Route::redirect('/', '/dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

// No `verified` middleware: it only does anything for a User that implements
// MustVerifyEmail, so add the two together if verification is ever wanted.
Route::middleware(['auth'])->group(function () {
    Volt::route('dashboard', 'dashboard')->name('dashboard');
    Volt::route('calendar', 'calendar')->name('calendar');

    Route::post('tasks/starter', StarterChoreController::class)->name('tasks.starter');
    Route::resource('tasks', TaskController::class)->except(['show']);
});

require __DIR__.'/auth.php';
