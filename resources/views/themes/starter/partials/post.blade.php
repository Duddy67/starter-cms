<li>
    <h3><a href="{{ url($locale.$post->getUrl()) }}">{{ $post->title }}</a></h3>

    <div>
	@if ($settings['show_post_image'] && $post->image)
	    <img class="post-image" src="{{ url('/').$post->image->getThumbnailUrl() }}" >
	@endif

	@if ($settings['show_post_excerpt'])
	    {!! $post->excerpt !!}
	@else
	    {!! $post->content !!}
	@endif
    </div>

    @if ($settings['show_post_categories'])
	<p class="categories">
	    <h6>Categories</h6>
            @php $categories = $post->getCategories($locale); @endphp
	    @foreach ($categories as $category)
		<a href="{{ url('/'.$locale.'/'.$segments['plugin'].$category->getUrl()) }}" class="btn btn-primary btn-sm active" role="button" aria-pressed="true">{{ $category->name }}</a>
	    @endforeach
	</p>
    @endif
</li>
