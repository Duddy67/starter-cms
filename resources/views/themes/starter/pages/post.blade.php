@if ($post->layoutItems()->exists())
    @include('themes.starter.layouts.post.'.$post->layout)
@else
    <h1 class="h2"><a href="{{ url($post->getUrl()) }}">{{ $post->title }}</a></h1>

    @if ($settings['show_created_at'])
        <div>@date ($post->created_at->tz($timezone))</div>
    @endif

    @if ($settings['show_owner'])
        <div>{{ $post->owner_name }}</div>
    @endif

    @if ($settings['show_excerpt'])
        <div class="excerpt">
            {!! $post->excerpt !!}
        </div>
    @endif

    <div class="content">
        @if ($settings['show_image'] && $post->image)
            <img class="post-image img-fluid" src="{{ url('/').$post->image->getThumbnailUrl() }}" >
        @endif
        {!! $post->content !!}
    </div>

    @if ($settings['show_categories'] && count($post->categories))
        <p class="categories">
            <h6>Categories</h6>
            @php $categories = $post->getCategories($locale); @endphp
            @foreach ($categories as $category)
                <a href="{{ url('/'.$locale.'/'.$segments->plugin.$category->getUrl()) }}" class="btn btn-primary btn-sm active" role="button" aria-pressed="true">{{ $category->name }}</a>
            @endforeach
        </p>
    @endif
@endif
