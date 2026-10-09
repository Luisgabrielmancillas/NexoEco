@props(['name' => 'home'])
@php($paths = [
    'chat' => 'M21 11a8 8 0 0 1-8 8H7l-4 3V5a2 2 0 0 1 2-2h8a8 8 0 0 1 8 8ZM7 8h10M7 12h7',
    'bookmark' => 'M6 3h12v19l-6-4-6 4V3Z',
    'home' => 'm3 11 9-8 9 8M5 10v11h14V10M9 21v-7h6v7',
    'food' => 'M3 12h18a9 9 0 0 1-18 0ZM5 21h14M7 3v5M12 2v6M17 3v5',
    'cart' => 'M2 3h3l3 12h11l3-9H6M9 21a1 1 0 1 1 0-2 1 1 0 0 1 0 2ZM18 21a1 1 0 1 1 0-2 1 1 0 0 1 0 2Z',
    'medical' => 'M9 3h6v6h6v6h-6v6H9v-6H3V9h6V3Z',
    'bags' => 'M4 8h16l1 13H3L4 8ZM8 9V7a4 4 0 0 1 8 0v2',
    'paw' => 'M8 13c-1 2-4 3-4 5 0 3 4 3 8 1 4 2 8 2 8-1 0-2-3-3-4-5-2-4-6-4-8 0ZM7 5a2 3 0 1 1-4 0 2 3 0 0 1 4 0ZM14 4a2 3 0 1 1-4 0 2 3 0 0 1 4 0ZM21 5a2 3 0 1 1-4 0 2 3 0 0 1 4 0Z',
    'sofa' => 'M5 11V7a3 3 0 0 1 3-3h8a3 3 0 0 1 3 3v4M5 16v5M19 16v5M5 11a2 2 0 0 0-4 0v6h22v-6a2 2 0 0 0-4 0v3H5v-3Z',
    'shirt' => 'm8 3-6 4 3 5 3-2v11h8V10l3 2 3-5-6-4a4 4 0 0 1-8 0Z',
    'laptop' => 'M5 4h14v12H5V4ZM2 20l3-4h14l3 4H2Z',
    'star' => 'm12 3 2.8 5.7 6.2.9-4.5 4.4 1 6.2-5.5-2.9-5.5 2.9 1-6.2L3 9.6l6.2-.9L12 3Z',
    'heart' => 'M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8L12 21l8.8-8.6a5.5 5.5 0 0 0 0-7.8Z',
    'help' => 'M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0ZM9 8a3 3 0 1 1 5 3c-2 1-2 1-2 3M12 17h.01',
    'store' => 'M3 10h18l-2-6H5l-2 6ZM5 10v10h14V10M9 20v-6h6v6',
    'arrow' => 'm14 6-6 6 6 6M8 12h13',
    'close' => 'm6 6 12 12M6 18 18 6',
    'chevron' => 'm9 5 7 7-7 7',
    'chevron-down' => 'm6 9 6 6 6-6',
])
<svg {{ $attributes->merge(['viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '1.7', 'aria-hidden' => 'true']) }}><path d="{{ $paths[$name] ?? $paths['home'] }}" stroke-linecap="round" stroke-linejoin="round"/></svg>
