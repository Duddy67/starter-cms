<h3>{{ __('labels.post.comments') }}</h3>

@include('themes.starter.layouts.flash-message')

@guest
    <div class="alert alert-info" role="alert">
        {{ __('messages.post.comments_authentication_required') }}
    </div>
@endguest

@auth
    <form method="post" action="{{ route('post.comment', $query) }}">
        @csrf

        <textarea name="comment" class="tinymce-texteditor"></textarea>
        @error('comment')
            <div class="alert alert-danger">{{ $message }}</div>
        @enderror

        <input type="submit" value="{{ __('labels.generic.submit') }}" class="btn btn-success mt-2 mb-4">
    </form>
@endauth

@if ($post->comments()->exists())
    @foreach ($post->comments as $comment)
        <div class="card mb-4">
            <div class="card-header">
                {{ __('labels.post.posted_by', ['author' => $comment->author]) }} at @date ($comment->created_at->tz($timezone))
            </div>
            <div class="card-body">
                {!! $comment->text !!}
            </div>
        </div>
    @endforeach
@else
    <div class="alert alert-info" role="alert">
        {{ __('messages.generic.no_item_found') }}
    </div>
@endif

