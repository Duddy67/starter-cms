@if ($post->layoutItems()->exists())
    @include('themes.starter.pages.'.$post->page)
@else
    <h1 class="h2"><a href="{{ url('/'.$locale.$post->getUrl()) }}">{{ $post->title }}</a></h1>

    @if ($post->settings['show_created_at'])
        <div>@date ($post->created_at->tz($page['timezone']))</div>
    @endif

    @if ($post->settings['show_updated_at'])
        <div>@date ($post->updated_at->tz($page['timezone']))</div>
    @endif

    @if ($post->settings['show_owner'])
        <div>{{ $post->owner_name }}</div>
    @endif

    @if ($post->settings['show_excerpt'])
        <div class="excerpt">
            {!! $post->excerpt !!}
        </div>
    @endif

    <div class="content">
        @if ($post->settings['show_image'] && $post->image)
            <img class="post-image img-fluid" src="{{ url('/').$post->image->getThumbnailUrl() }}" >
        @endif
        {!! $post->content !!}
    </div>

    @if ($post->settings['show_categories'] && count($post->categories))
        <p class="categories">
            <h6>Categories</h6>
            @php $categories = $post->getCategories($locale); @endphp
            @foreach ($categories as $category)
                <a href="{{ url('/'.$locale.'/'.$segments['posts'].$category->getUrl()) }}" class="btn btn-primary btn-sm active" role="button" aria-pressed="true">{{ $category->name }}</a>
            @endforeach
        </p>
    @endif

    @if ($post->settings['allow_comments'])
        @include('themes.starter.partials.post.comments')
    @endif
@endif
