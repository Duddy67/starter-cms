<h3>{{ __('labels.post.comments') }}</h3>

@include('themes.starter.layouts.flash-message', ['alertId' => 'ajax-message-alert-0', 'messageId' => 'ajax-message-0'])

@guest
    <div class="alert alert-info" role="alert">
        {{ __('messages.post.comments_authentication_required') }}
    </div>
@endguest

@auth
    <form method="post" id="createComment" action="{{ route('post.comment', $query) }}">
        @csrf
        <textarea name="comment-0" id="tiny-comment-0" data-comment-id="0" class="tinymce-texteditor"></textarea>
        <button type="button" id="create-btn" class="btn btn-space btn-success mt-2 mb-4">@lang ('labels.generic.submit')</button>
        <div class="text-danger mt-2" id="comment-0Error"></div>
    </form>
@endauth

@if ($post->comments()->exists())
    @foreach ($post->comments as $comment)
        @include('themes.starter.partials.post.comment')
    @endforeach
@else
    <div class="alert alert-info" role="alert">
        {{ __('messages.generic.no_item_found') }}
    </div>
@endif

@push('scripts')
    <script type="text/javascript" src="{{ asset('/vendor/tinymce/tinymce.min.js') }}"></script>
    <script src="{{ asset('/vendor/codalia/c.ajax.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/js/post/comment.js') }}"></script>
@endpush

