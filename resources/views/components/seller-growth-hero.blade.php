@props(['eyebrow', 'title', 'description', 'icon' => 'sparkles', 'tone' => 'peach', 'badge' => 'Espacio de crecimiento'])
<section {{ $attributes->class(['growth-hero', 'growth-hero-'.$tone]) }} aria-label="{{ $title }}">
    <div class="growth-hero-copy">
        <span class="growth-kicker"><span aria-hidden="true"></span>{{ $eyebrow }}</span>
        <h1>{{ $title }}</h1>
        <p>{{ $description }}</p>
        @isset($actions)<div class="growth-hero-actions">{{ $actions }}</div>@endisset
        @isset($meta)<div class="growth-hero-meta">{{ $meta }}</div>@endisset
    </div>
    <div class="growth-hero-art" aria-hidden="true">
        <span class="growth-art-ring"></span><span class="growth-art-ring inner"></span>
        <span class="growth-art-icon"><x-seller-icon :name="$icon"/></span>
        <span class="growth-art-dot"></span><span class="growth-art-star">✦</span>
        <span class="growth-art-label">{{ $badge }}</span>
    </div>
</section>
