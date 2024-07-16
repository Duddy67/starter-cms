@if ($category->settings['show_name'])
    <h3 class="pb-2">{{ $category->name }}</h3>
@endif

@if ($category->settings['show_description'])
    <div>{!! $category->description !!}</div>
@endif

@if ($category->settings['show_image'] && $category->image)
    <img class="post-image img-fluid" src="{{ url('/').$category->image->getThumbnailUrl() }}" >
@endif

@if ($category->settings['show_search'])
    <div class="card">
	<div class="card-body">
	    @include('themes.starter.partials.filters')
	</div>
    </div>
@endif

<ul class="post-list pt-4">
    @if (count($posts))
	@foreach ($posts as $post)
	    @include ('themes.starter.partials.post')
	@endforeach
    @else
	<div>No post</div>
    @endif
</ul>

@if (!empty($category->settings['posts_per_page']))
    <x-pagination :items=$posts />
@endif


@if ($category->getExtraFieldByAlias('library'))
    <span>Library: </span>{{ $category->getExtraFieldByAlias('library') }}
@endif

@if ($category->settings['show_subcategories'])
    @include ('themes.starter.partials.post.subcategories')
@endif

@push ('scripts')
    <script type="text/javascript" src="{{ $public }}/js/post/category.js"></script>
@endpush
