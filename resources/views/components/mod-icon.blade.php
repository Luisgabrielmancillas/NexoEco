@props(['name' => 'grid'])
@php($paths = [
    'grid' => 'M3 3h7v7H3zM14 3h7v7h-7zM3 14h7v7H3zM14 14h7v7h-7z',
    'file' => 'M6 3h8l4 4v14H6zM14 3v5h4M9 12h6M9 16h6',
    'flag' => 'M5 22V3h14l-3 5 3 5H5',
    'users' => 'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM17 3a4 4 0 0 1 0 8M22 21v-2a4 4 0 0 0-3-3.9',
    'chart' => 'M3 3v18h18M7 17v-6M12 17V7M17 17v-9',
    'clock' => 'M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0ZM12 7v5l3 2',
    'check' => 'm5 12 4 4L19 6',
    'search' => 'M17 10a7 7 0 1 1-14 0 7 7 0 0 1 14 0Zm-2 5 6 6',
    'bell' => 'M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4',
    'comment' => 'M21 11a8 8 0 0 1-8 8H6l-4 3V11a9 9 0 0 1 19 0ZM7 9h9M7 13h6',
    'settings' => 'M12 3v3M12 18v3M3 12h3M18 12h3M5.6 5.6l2 2M16.4 16.4l2 2M5.6 18.4l2-2M16.4 7.6l2-2M17 12a5 5 0 1 1-10 0 5 5 0 0 1 10 0Z',
    'help' => 'M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0ZM9 8a3 3 0 0 1 6 0c0 3-3 2-3 6M12 17h.01',
    'arrow' => 'm9 5 7 7-7 7',
    'menu' => 'M3 6h18M3 12h18M3 18h18',
    'close' => 'm6 6 12 12M6 18 18 6',
    'eye' => 'M2 12s4-7 10-7 10 7 10 7-4 7-10 7-10-7-10-7ZM15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z',
    'logout' => 'M9 4H4v16h5M14 8l4 4-4 4M8 12h12',
])
<svg {{ $attributes->merge(['viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '1.7', 'aria-hidden' => 'true']) }}><path d="{{ $paths[$name] ?? $paths['grid'] }}" stroke-linecap="round" stroke-linejoin="round"/></svg>
