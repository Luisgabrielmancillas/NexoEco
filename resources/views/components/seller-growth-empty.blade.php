@props(['icon' => 'sparkles', 'title', 'description'])
<div {{ $attributes->class(['growth-empty']) }}>
    <span class="growth-empty-icon"><x-seller-icon :name="$icon"/></span>
    <h3>{{ $title }}</h3><p>{{ $description }}</p>
    @if($slot->isNotEmpty())<div class="growth-empty-actions">{{ $slot }}</div>@endif
</div>
