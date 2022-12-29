<h3>{{ __('labels.post.comments') }}</h3>

<form method="post" action="">
    @csrf

    <textarea></textarea>
</form>

@if ($post->comments()->exists())
    @foreach ($post->comments() as $comment)
        <div>COMMENTS</div>
    @endforeach
@else
    <div class="alert alert-info" role="alert">
        {{ __('messages.generic.no_item_found') }}
    </div>
@endif

