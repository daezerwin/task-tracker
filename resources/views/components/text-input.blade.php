@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-300 focus:border-blue-600 focus:ring-blue-600 rounded-lg shadow-sm text-base py-2.5 px-3.5']) }}>
