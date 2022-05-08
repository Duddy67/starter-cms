@if ($settings['show_name'])
    <h3 class="pb-2">{{ $category->name }}</h3>
@endif

@if ($settings['show_description'])
    <div>{!! $category->description !!}</div>
@endif

@if ($settings['show_search'])
    <div class="card">
	<div class="card-body">
	    @include('partials.filters')
	</div>
    </div>
@endif

<ul class="post-list pt-4">
    @if (count($posts))
	@foreach ($posts as $post)
	    @include ('partials.blog.post')
	@endforeach
    @else
	<div>No post</div>
    @endif
</ul>

<x-pagination :items=$posts />

@if ($settings['show_subcategories'])
    @include ('partials.blog.subcategories')
@endif

@push ('scripts')
    <script type="text/javascript" src="{{ $public }}/js/post/category.js"></script>
@endpush
