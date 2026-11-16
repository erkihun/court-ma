{{-- Shared "previous / next" pagination for simplePaginate() lists. --}}
@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('pagination.navigation') }}" class="ui-pagination mt-4">
        <span></span>
        <div class="flex items-center gap-1.5">
            @if ($paginator->onFirstPage())
                <span class="ui-page-link" aria-disabled="true">{{ __('pagination.previous_page') }}</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="ui-page-link">{{ __('pagination.previous_page') }}</a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="ui-page-link">{{ __('pagination.next_page') }}</a>
            @else
                <span class="ui-page-link" aria-disabled="true">{{ __('pagination.next_page') }}</span>
            @endif
        </div>
    </nav>
@endif
