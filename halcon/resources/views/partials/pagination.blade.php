{{-- Simple pagination without Tailwind: « Previous | page X of Y | Next » --}}
@if ($paginator->hasPages())
    <div style="display:flex; justify-content:space-between; align-items:center; margin-top:16px; font-size:14px">
        <span class="muted">Showing {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} of {{ $paginator->total() }}</span>
        <div class="actions">
            @if ($paginator->onFirstPage())
                <span class="btn btn-secondary btn-small" style="opacity:.5">« Previous</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="btn btn-secondary btn-small">« Previous</a>
            @endif
            <span class="btn btn-small">Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}</span>
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="btn btn-secondary btn-small">Next »</a>
            @else
                <span class="btn btn-secondary btn-small" style="opacity:.5">Next »</span>
            @endif
        </div>
    </div>
@endif
