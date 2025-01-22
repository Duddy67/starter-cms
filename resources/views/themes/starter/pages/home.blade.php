
@if (isset($category) && $category)

    @if ($category->settings['show_name'])
        <h3 class="pb-2">{{ $category->name }}</h3>
    @endif

    @if ($category->settings['show_description'])
        <div>{!! $category->description !!}</div>
    @endif

    <ul class="post-list">
	@if (isset($posts) && count($posts))
	    @foreach ($posts as $post)
		@include ('themes.starter.partials.post')
	    @endforeach
	@else
	    <div>No post</div>
	@endif
    </ul>
@endif

@push ('scripts')
    <script type="text/javascript" src="{{ $public }}/js/post/category.js"></script>
@endpush
