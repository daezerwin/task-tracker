<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center px-8 py-4 bg-white border-2 border-blue-200 rounded-lg font-semibold text-lg text-blue-700 shadow-sm hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
