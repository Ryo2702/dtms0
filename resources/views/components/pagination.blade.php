@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-between">
        <div class="flex justify-between flex-1 sm:hidden">
            {{-- Previous Page Link (Mobile) --}}
            @if ($paginator->onFirstPage())
                <span class="btn btn-disabled">Previous</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="btn btn-primary">Previous</a>
            @endif

            {{-- Next Page Link (Mobile) --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="btn btn-primary">Next</a>
            @else
                <span class="btn btn-disabled">Next</span>
            @endif
        </div>

        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-gray-700">
                    Showing <span class="font-medium">{{ $paginator->firstItem() }}</span>
                    to <span class="font-medium">{{ $paginator->lastItem() }}</span>
                    of <span class="font-medium">{{ $paginator->total() }}</span> results
                </p>
            </div>

            <div>
                <div class="join">
                    {{-- Previous Page --}}
                    @if ($paginator->onFirstPage())
                        <span class="join-item btn btn-disabled">
                            <i data-lucide="chevron-left" class="w-4 h-4"></i>
                        </span>
                    @else
                        <a href="{{ $paginator->previousPageUrl() }}" class="join-item btn btn-ghost">
                            <i data-lucide="chevron-left" class="w-4 h-4"></i>
                        </a>
                    @endif
                    {{-- Pagination Elements --}}
                    @foreach ($elements as $element)
                        {{-- "Three Dots" Separator --}}
                        @if (is_string($element))
                            <span class="join-item btn btn-disabled">{{ $element }}</span>
                        @endif

                        {{-- Array Of Links --}}
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <span class="join-item btn btn-active">{{ $page }}</span>
                                @else
                                    <a href="{{ $url }}"
                                        class="join-item btn btn-ghost">{{ $page }}</a>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <a href="{{ $paginator->nextPageUrl() }}" class="join-item btn btn-ghost">
                            <i data-lucide="chevron-right" class="w-4 h-4"></i>
                        </a>
                    @else
                        <span class="join-item btn btn-disabled">
                            <i data-lucide="chevron-right" class="w-4 h-4"></i>
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </nav>
@endif
