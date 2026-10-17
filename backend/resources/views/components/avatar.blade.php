@props([
    'src' => null,
    'name' => 'User',
    'size' => 'md', // 'xs', 'sm', 'md', 'lg', 'xl'
    'shape' => 'squircle', // 'squircle', 'circle'
    'status' => null, // 'online', 'offline', 'busy'
    'alt' => null,
])

@php
    $sizeClasses = match ($size) {
        'xs' => 'w-7 h-7 text-[10px]',
        'sm' => 'w-8 h-8 text-xs',
        'lg' => 'w-12 h-12 text-lg',
        'xl' => 'w-16 h-16 text-2xl',
        default => 'w-10 h-10 text-sm',
    };

    $shapeClasses = match ($shape) {
        'circle' => 'rounded-full',
        default => match ($size) {
            'xs', 'sm' => 'rounded-xl',
            'xl' => 'rounded-3xl',
            default => 'rounded-2xl',
        },
    };

    $statusSize = match ($size) {
        'xs' => 'w-2 h-2',
        'sm' => 'w-2.5 h-2.5',
        'lg', 'xl' => 'w-3.5 h-3.5',
        default => 'w-3 h-3',
    };

    $statusColor = match ($status) {
        'online' => 'bg-emerald-500',
        'offline' => 'bg-zinc-400',
        'busy' => 'bg-rose-500',
        default => '',
    };

    $initial = strtoupper(mb_substr(trim($name ?? 'U'), 0, 1));
@endphp

<div class="relative inline-flex shrink-0">
    <div
        {{ $attributes->merge(['class' => "{$sizeClasses} {$shapeClasses} bg-primary/10 dark:bg-primary-dark/20 border border-primary/20 dark:border-primary-dark/30 text-primary dark:text-primary-dark flex items-center justify-center font-black select-none overflow-hidden transition-colors duration-200"]) }}>
        @if ($src)
            <img src="{{ $src }}" alt="{{ $alt ?? $name }}" class="w-full h-full object-cover object-top">
        @else
            <span>{{ $initial }}</span>
        @endif
    </div>

    @if ($status)
        <span
            class="absolute bottom-0 right-0 {{ $statusSize }} {{ $statusColor }} {{ $shape === 'circle' ? 'rounded-full' : 'rounded-md' }} ring-2 ring-white dark:ring-black"></span>
    @endif
</div>
