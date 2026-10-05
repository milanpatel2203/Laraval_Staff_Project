@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex flex-wrap items-center justify-end gap-1.5 select-none">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-400 bg-gray-50 border border-gray-200 rounded cursor-not-allowed select-none opacity-60">
                <i class="fas fa-chevron-left text-[9px]"></i>
                <span>Previous</span>
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-700 bg-white border border-gray-200 rounded hover:bg-gray-100 hover:text-[#2D2D2D] hover:border-gray-300 transition-colors shadow-xs">
                <i class="fas fa-chevron-left text-[9px]"></i>
                <span>Previous</span>
            </a>
        @endif

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-700 bg-white border border-gray-200 rounded hover:bg-gray-100 hover:text-[#2D2D2D] hover:border-gray-300 transition-colors shadow-xs">
                <span>Next</span>
                <i class="fas fa-chevron-right text-[9px]"></i>
            </a>
        @else
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-400 bg-gray-50 border border-gray-200 rounded cursor-not-allowed select-none opacity-60">
                <span>Next</span>
                <i class="fas fa-chevron-right text-[9px]"></i>
            </span>
        @endif
    </nav>
@endif
