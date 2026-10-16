{{-- Material filled button, error colour --}}
<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center gap-2 h-10 px-6 rounded-full bg-error text-on-error text-label-lg transition hover:bg-error/90 hover:shadow-md hover:shadow-black/40 focus:outline-none focus-visible:ring-2 focus-visible:ring-error focus-visible:ring-offset-2 focus-visible:ring-offset-surface']) }}>
    {{ $slot }}
</button>
