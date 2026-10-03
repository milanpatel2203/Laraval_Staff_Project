@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex flex-wrap items-center justify-end gap-1.5 select-none">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-400 bg-gray-100/70 border border-gray-200 rounded cursor-not-allowed select-none">
                <i class="fas fa-chevron-left text-[9px]"></i>
                <span>Previous</span>
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-[#2D2D2D] bg-white border border-gray-300 rounded hover:bg-gray-100 hover:text-black transition-colors shadow-xs">
                <i class="fas fa-chevron-left text-[9px]"></i>
                <span>Previous</span>
            </a>
        @endif

        {{-- Pagination Elements (Page Numbers) --}}
        <div class="flex items-center gap-1">
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <span class="inline-flex items-center justify-center min-w-[26px] h-7 text-xs text-gray-400 select-none">
                        {{ $element }}
                    </span>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page" class="inline-flex items-center justify-center min-w-[30px] h-7 px-2 text-xs font-bold text-white bg-[#2D2D2D] border border-[#2D2D2D] rounded shadow-xs select-none">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $url }}" class="inline-flex items-center justify-center min-w-[30px] h-7 px-2 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-100 hover:text-[#2D2D2D] transition-colors shadow-xs" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach
        </div>

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-[#2D2D2D] bg-white border border-gray-300 rounded hover:bg-gray-100 hover:text-black transition-colors shadow-xs">
                <span>Next</span>
                <i class="fas fa-chevron-right text-[9px]"></i>
            </a>
        @else
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-400 bg-gray-100/70 border border-gray-200 rounded cursor-not-allowed select-none">
                <span>Next</span>
                <i class="fas fa-chevron-right text-[9px]"></i>
            </span>
        @endif
    </nav>
@endif
