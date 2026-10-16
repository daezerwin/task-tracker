<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;

use function Livewire\Volt\form;
use function Livewire\Volt\layout;

layout('layouts.guest');

form(LoginForm::class);

$login = function () {
    $this->validate();

    $this->form->authenticate();

    Session::regenerate();

    $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
};

?>

<div>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit="login">
        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input wire:model="form.email" id="email" class="block mt-1 w-full" type="email" name="email" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input wire:model="form.password" id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-5">
            <label for="remember" class="inline-flex items-center cursor-pointer">
                <input wire:model="form.remember" id="remember" type="checkbox" class="m3-checkbox" name="remember">
                <span class="ms-3 text-body-md text-on-surface-variant">{{ __('Remember me') }}</span>
            </label>
        </div>

        @if (Route::has('password.request'))
            <a class="mt-4 inline-block text-label-lg text-primary hover:underline rounded focus:outline-none focus-visible:ring-2 focus-visible:ring-primary" href="{{ route('password.request') }}" wire:navigate>
                {{ __('Forgot password?') }}
            </a>
        @endif

        <div class="flex items-center justify-between mt-8">
            @if (Route::has('register'))
                <a href="{{ route('register') }}" wire:navigate
                   class="-ml-3 inline-flex h-10 items-center rounded-full px-3 text-label-lg text-primary hover:bg-primary/[0.08]">
                    {{ __('Create account') }}
                </a>
            @endif

            <x-primary-button class="ms-auto">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>
</div>
