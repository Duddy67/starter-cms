
@if (isset($category) && $category)
    <ul class="post-list">
	@if (count($posts))
	    @foreach ($posts as $post)
		@include ('themes.starter.partials.post')
	    @endforeach
	@else
	    <div>No post</div>
	@endif
    </ul>

    @if ($category->getExtraFieldByAlias('library'))
        <span>Library: </span>{{ $category->getExtraFieldByAlias('library') }}
    @endif
@endif

@push ('scripts')
    <script type="text/javascript" src="{{ $public }}/js/post/category.js"></script>
@endpush
