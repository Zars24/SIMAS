@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navigasi halaman" class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm text-slate-500 dark:text-slate-400">
            Menampilkan
            <span class="font-semibold text-primary-800 dark:text-primary-100">{{ $paginator->firstItem() }}</span>–<span
                class="font-semibold text-primary-800 dark:text-primary-100">{{ $paginator->lastItem() }}</span>
            dari
            <span class="font-semibold text-primary-800 dark:text-primary-100">{{ $paginator->total() }}</span>
            hasil
        </p>

        <div class="flex items-center gap-1">
            {{-- Sebelumnya --}}
            @if ($paginator->onFirstPage())
                <span class="inline-flex h-9 w-9 cursor-not-allowed items-center justify-center rounded-xl text-slate-300 dark:text-slate-600" aria-disabled="true">
                    <x-icon name="chevron-left" class="h-4 w-4" />
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
                   class="inline-flex h-9 w-9 items-center justify-center rounded-xl text-primary-600 transition hover:bg-primary-100 dark:text-primary-200 dark:hover:bg-primary-900/60"
                   aria-label="Halaman sebelumnya">
                    <x-icon name="chevron-left" class="h-4 w-4" />
                </a>
            @endif

            {{-- Nomor halaman --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="inline-flex h-9 w-9 items-center justify-center text-sm text-slate-400">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page"
                                  class="inline-flex h-9 min-w-9 items-center justify-center rounded-xl bg-primary-500 px-3 text-sm font-semibold text-white">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $url }}"
                               class="inline-flex h-9 min-w-9 items-center justify-center rounded-xl px-3 text-sm font-medium text-primary-700 transition hover:bg-primary-100 dark:text-primary-200 dark:hover:bg-primary-900/60"
                               aria-label="Ke halaman {{ $page }}">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Berikutnya --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next"
                   class="inline-flex h-9 w-9 items-center justify-center rounded-xl text-primary-600 transition hover:bg-primary-100 dark:text-primary-200 dark:hover:bg-primary-900/60"
                   aria-label="Halaman berikutnya">
                    <x-icon name="chevron-right" class="h-4 w-4" />
                </a>
            @else
                <span class="inline-flex h-9 w-9 cursor-not-allowed items-center justify-center rounded-xl text-slate-300 dark:text-slate-600" aria-disabled="true">
                    <x-icon name="chevron-right" class="h-4 w-4" />
                </span>
            @endif
        </div>
    </nav>
@endif
