@props([
    'icon' => 'clipboard-document-list',
    'title' => 'Belum ada data',
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'empty-state']) }}>
    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-primary-100 text-primary-500 dark:bg-primary-500/10 dark:text-primary-300">
        <x-icon :name="$icon" class="h-6 w-6" />
    </div>

    <div>
        <p class="section-title">{{ $title }}</p>

        @if ($description)
            <p class="mx-auto mt-1 max-w-sm text-sm text-slate-500 dark:text-slate-400">{{ $description }}</p>
        @endif
    </div>

    {{ $slot }}
</div>
