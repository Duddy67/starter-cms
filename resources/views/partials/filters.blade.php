<form id="item-filters" action="{{ route('blog.category', $query) }}" method="get">
    <div class="row">
	<div class="col-sm mr-4">
	    <label for="search">@lang ('labels.button.search')</label>

	    <input id="search" type="text" class="form-control " name="search" placeholder="Search by name">

	    <button type="button" id="search-btn" class="btn btn-space btn-secondary">@lang ('labels.button.search')</button>
	    <button type="button" id="clear-search-btn" class="btn btn-space btn-secondary">@lang ('labels.button.clear')</button>
	</div>
    </div>
</form>

