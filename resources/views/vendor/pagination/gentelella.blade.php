{{-- Tampilan paginasi Gentelella: {{ $paginator->withQueryString()->links('vendor.pagination.gentelella') }} --}}
<nav class="pagination" role="navigation" aria-label="Paginasi">
    <span class="page-info">
        @if ($paginator->total() > 0)
            Menampilkan {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} dari {{ $paginator->total() }} data
        @else
            Tidak ada data
        @endif
    </span>

    @if ($paginator->hasPages())
        @if ($paginator->onFirstPage())
            <span class="page-link disabled" aria-disabled="true" aria-label="Sebelumnya">&lsaquo;</span>
        @else
            <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Sebelumnya">&lsaquo;</a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="page-link disabled">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="page-link active" aria-current="page">{{ $page }}</span>
                    @else
                        <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Berikutnya">&rsaquo;</a>
        @else
            <span class="page-link disabled" aria-disabled="true" aria-label="Berikutnya">&rsaquo;</span>
        @endif
    @endif
</nav>
