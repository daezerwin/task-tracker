{{-- Material filled button --}}
<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center gap-2 h-10 px-6 rounded-full bg-primary text-on-primary text-label-lg transition hover:bg-primary/90 hover:shadow-md hover:shadow-black/40 active:bg-primary/80 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 focus-visible:ring-offset-surface disabled:opacity-40 disabled:shadow-none']) }}>
    {{ $slot }}
</button>
