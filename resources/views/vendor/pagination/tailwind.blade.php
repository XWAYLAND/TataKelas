@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-xs text-base-content/50">
            Showing
            <span class="font-semibold text-base-content/70">{{ $paginator->firstItem() }}</span>
            to
            <span class="font-semibold text-base-content/70">{{ $paginator->lastItem() }}</span>
            of
            <span class="font-semibold text-base-content/70">{{ $paginator->total() }}</span>
            results
        </p>

        <div class="max-w-full overflow-x-auto">
            <div class="join">
                @if ($paginator->onFirstPage())
                    <span class="join-item btn btn-sm btn-disabled" aria-disabled="true" aria-label="Previous page">
                        <i class="fas fa-chevron-left text-[10px]"></i>
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="join-item btn btn-sm" aria-label="Previous page">
                        <i class="fas fa-chevron-left text-[10px]"></i>
                    </a>
                @endif

                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="join-item btn btn-sm btn-disabled">{{ $element }}</span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span class="join-item btn btn-sm btn-active" aria-current="page">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="join-item btn btn-sm">{{ $page }}</a>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="join-item btn btn-sm" aria-label="Next page">
                        <i class="fas fa-chevron-right text-[10px]"></i>
                    </a>
                @else
                    <span class="join-item btn btn-sm btn-disabled" aria-disabled="true" aria-label="Next page">
                        <i class="fas fa-chevron-right text-[10px]"></i>
                    </span>
                @endif
            </div>
        </div>
    </nav>
@endif
