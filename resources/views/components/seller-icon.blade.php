@props(['name' => 'store'])
@php($paths = [
    'store' => 'M3 10h18l-2-6H5l-2 6ZM5 10v10h14V10M9 20v-6h6v6',
    'box' => 'm12 3 9 5v8l-9 5-9-5V8l9-5ZM3 8l9 5 9-5M12 13v8M7.5 5.5l9 5',
    'heart' => 'M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8L12 21l8.8-8.6a5.5 5.5 0 0 0 0-7.8Z',
    'star' => 'm12 3 2.8 5.7 6.2.9-4.5 4.4 1 6.2-5.5-2.9-5.5 2.9 1-6.2L3 9.6l6.2-.9L12 3Z',
    'shield' => 'm12 3 8 3v6c0 5-8 9-8 9s-8-4-8-9V6l8-3ZM8 12l3 3 5-6',
    'pin' => 'M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 1 1 16 0ZM15 10a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z',
    'clock' => 'M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0ZM12 7v5l3 2',
    'edit' => 'm16 3 5 5-12 12-6 1 1-6L16 3ZM13 6l5 5',
    'plus' => 'M12 5v14M5 12h14',
    'chart' => 'M4 3v17h17M8 16v-5M13 16V7M18 16v-8',
    'chat' => 'M21 11a8 8 0 0 1-8 8H6l-4 3V11a9 9 0 0 1 19 0ZM7 10h10M7 14h6',
])
<svg {{ $attributes->merge(['viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '1.7', 'aria-hidden' => 'true']) }}><path d="{{ $paths[$name] ?? $paths['store'] }}" stroke-linecap="round" stroke-linejoin="round"/></svg>
