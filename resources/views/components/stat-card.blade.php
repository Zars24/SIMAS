@props([
    'label',
    'value',
    'hint' => null,
    'icon' => 'chart-bar',
    'tone' => 'primary',
    'animate' => false,
])

@php
    $tones = [
        'primary' => 'bg-primary-100 text-primary-700 dark:bg-primary-500/15 dark:text-primary-200',
        'gold' => 'bg-gold-100 text-gold-700 dark:bg-gold-400/15 dark:text-gold-300',
        'emerald' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300',
        'sky' => 'bg-sky-100 text-sky-700 dark:bg-sky-500/15 dark:text-sky-300',
        'rose' => 'bg-rose-100 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300',
        'slate' => 'bg-slate-100 text-slate-600 dark:bg-slate-500/15 dark:text-slate-300',
    ];

    $canAnimate = $animate && is_numeric($value);
@endphp

<div {{ $attributes->merge(['class' => 'card-pad']) }}>
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <p class="truncate text-sm font-medium text-slate-500 dark:text-slate-400">{{ $label }}</p>

            <p class="mt-1.5 text-2xl font-bold tracking-tight text-primary-900 dark:text-white">
                @if ($canAnimate)
                    <span x-data="countUp({{ (int) $value }})" x-text="display.toLocaleString('id-ID')">{{ $value }}</span>
                @else
                    {{ $value }}
                @endif
            </p>

            @if ($hint)
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $hint }}</p>
            @endif
        </div>

        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl {{ $tones[$tone] ?? $tones['primary'] }}">
            <x-icon :name="$icon" class="h-6 w-6" />
        </div>
    </div>
</div>
