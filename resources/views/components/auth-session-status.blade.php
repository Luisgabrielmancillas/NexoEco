@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'font-medium text-sm text-success-text dark:text-success-border']) }}>
        {{ $status }}
    </div>
@endif
