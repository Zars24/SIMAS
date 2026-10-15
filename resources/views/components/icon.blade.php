@props(['name', 'class' => 'h-5 w-5'])

<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
     aria-hidden="true" {{ $attributes->merge(['class' => $class]) }}>
    {!! \App\Support\Icons::get($name) !!}
</svg>
