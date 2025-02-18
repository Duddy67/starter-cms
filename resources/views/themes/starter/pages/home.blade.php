
@if (isset($category) && $category)

    @if ($category->settings['show_name'])
        <h3 class="pb-2">{{ $category->name }}</h3>
    @endif

    @if ($category->settings['show_description'])
        <div>{!! $category->description !!}</div>
    @endif

    <ul class="post-list">
	@if (isset($posts) && count($posts))
            @php $selections = $posts->filterPostsById([1,2], 'title'); @endphp
            @if (!$selections->isEmpty())
                <h2 class="mt-5">Our Selection</h2>
                <ul class="list-group list-group-horizontal">
                    @foreach ($selections as $post)
                        @include ('themes.starter.partials.post')
                    @endforeach
                </ul>
            @endif

	    @foreach ($posts as $post)
		@include ('themes.starter.partials.post')
	    @endforeach

            @php $favourites = $posts->filterPostsByCategory(8, 'created_at'); @endphp
            @if (!$favourites->isEmpty())
                <h2 class="mt-5">Favourites</h2>
                <ul class="list-group list-group-horizontal">
                    @foreach ($favourites as $post)
                        @include ('themes.starter.partials.post')
                    @endforeach
                </ul>
            @endif
	@else
	    <div>No post</div>
	@endif
    </ul>
@endif

@push ('scripts')
    <script type="text/javascript" src="{{ $public }}/js/post/category.js"></script>
@endpush
