
@if (isset($category) && $category)
    @if (isset($posts) && count($posts))
        @php $selections = $posts->filterPostsBySlug(['lolita', '1984'], 'created_at', true); @endphp 
        @if (!$selections->isEmpty())
            <h2 class="mt-5">Our Selection</h2>
            <ul class="list-group list-group-horizontal">
                @foreach ($selections as $post)
                    @include ('themes.starter.partials.post')
                @endforeach
            </ul>
        @endif

        <ul class="post-list mt-5">
	    @foreach ($posts as $post)
		@include ('themes.starter.partials.post')
	    @endforeach
        </ul>

        @php $discounts = $posts->filterPostsByCategory('discount', 'created_at'); @endphp 
        @if (!$discounts->isEmpty())
            <h2 class="mt-5">Discounts</h2>
            <ul class="list-group list-group-horizontal">
                @foreach ($discounts as $post)
                    @include ('themes.starter.partials.post')
                @endforeach
            </ul>
        @endif
    @else
        <div>No post</div>
    @endif

    @if ($category->getExtraFieldByAlias('library'))
        <span>Library: </span>{{ $category->getExtraFieldByAlias('library') }}
    @endif
@endif

@push ('scripts')
    <script type="text/javascript" src="{{ $public }}/js/post/category.js"></script>
@endpush
