{{-- Material outlined button --}}
<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center gap-2 h-10 px-6 rounded-full border border-outline text-primary text-label-lg transition hover:bg-primary/[0.08] active:bg-primary/[0.12] focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 focus-visible:ring-offset-surface disabled:opacity-40']) }}>
    {{ $slot }}
</button>
