@if ($items->count())
    <div class="d-flex justify-content-center pt-5">
	{{ $items->appends(request()->except(['page']))->links('pagination::bootstrap-4') }}
    </div>

    <div class="d-flex justify-content-center">
	<span>
            {{ __('labels.pagination.results', ['first' => $items->firstItem(), 'last' => $items->lastItem(), 'total' => $items->total()]) }}
	</span>
    </div>
@endif
