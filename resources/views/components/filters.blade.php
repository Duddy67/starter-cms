<form id="item-filters" action="{{ route($url['route'].'.index', $url['query']) }}" method="get">
    <div class="row">
        @php $total = count($filters) @endphp
	@foreach ($filters as $key => $filter)
	    <div class="col-sm mr-4">
		<x-input :field="$filter" :value="$filter->value" />

		@if ($filter->name == 'search') 
		    <button type="button" id="search-btn" class="btn btn-space btn-secondary">@lang ($filter->label)</button>
		    <button type="button" id="clear-search-btn" class="btn btn-space btn-secondary">@lang ('labels.button.clear')</button>
		@endif

		@if ($total == ($key + 1) && $key > 1) 
		    <button type="button" id="clear-all-btn" class="btn btn-space btn-secondary">@lang ('labels.button.clear_all')</button>
		@endif
	    </div>
	@endforeach
    </div>

    @if (isset($url['query']['page'])) 
	<input type="hidden" id="filters-pagination" name="page" value="{{ $url['query']['page'] }}">
    @endif
</form>
