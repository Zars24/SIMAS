@props(['status', 'label' => null])

@php
    $map = [
        'hadir' => ['badge-hadir', 'Hadir'],
        'izin' => ['badge-izin', 'Izin'],
        'sakit' => ['badge-sakit', 'Sakit'],
        'alpa' => ['badge-alpa', 'Alpa'],
        'belum' => ['badge-belum', 'Belum diisi'],
    ];

    [$class, $defaultLabel] = $map[$status] ?? ['badge-belum', ucfirst((string) $status)];
@endphp

<span {{ $attributes->merge(['class' => $class]) }}>
    <span class="h-1.5 w-1.5 rounded-full bg-current" aria-hidden="true"></span>
    {{ $label ?? $defaultLabel }}
</span>
