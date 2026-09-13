@props([
    'align' => 'right', // 'left', 'right', 'top-left', 'top-right'
    'width' => 'w-48',
    'contentClasses' => 'p-1.5',
])

@php
    $alignmentClasses = match ($align) {
        'left' => 'origin-top-left left-0 top-full mt-2',
        'top-left' => 'origin-bottom-left left-0 bottom-full mb-2',
        'top-right' => 'origin-bottom-right right-0 bottom-full mb-2',
        default => 'origin-top-right right-0 top-full mt-2',
    };
@endphp

<div class="relative inline-block text-left" x-data="{ open: false }" @click.outside="open = false"
    @close.stop="open = false">
    <!-- Trigger -->
    <div @click="open = !open">
        @if (isset($trigger))
            {{ $trigger }}
        @else
            <button type="button"
                class="m3-btn-secondary min-h-[40px] px-3.5 text-[13px] inline-flex items-center gap-2">
                <span>Menu</span>
                <i class="bi bi-chevron-down text-xs transition-transform duration-200"
                    :class="{ 'rotate-180': open }"></i>
            </button>
        @endif
    </div>

    <!-- Dropdown Panel -->
    <div x-show="open" x-cloak style="display: none;" x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95 translate-y-1"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 translate-y-1"
        class="absolute z-50 {{ $width }} {{ $alignmentClasses }} bg-white/95 dark:bg-zinc-900/95 backdrop-blur-xl border border-zinc-200/90 dark:border-zinc-800/90 rounded-2xl shadow-xl dark:shadow-none overflow-hidden {{ $contentClasses }}"
        @click="open = false">
        {{ $slot }}
    </div>
</div>
