<h3>{{ __('labels.post.comments') }}</h3>

@guest
    <div class="alert alert-info" role="alert">
        {{ __('messages.post.comments_authentication_required') }}
    </div>
@endguest

@auth
    <div class="alert alert-success alert-block d-none" id="ajax-message-alert-0">
        <button type="button" class="btn-close" onclick="document.getElementById('ajax-message-alert-0').classList.add('d-none');"></button>	
        <strong id="ajax-message-0"></strong>
    </div>

    <form method="post" id="createComment" action="{{ route('post.comment', $query) }}">
        @csrf
        <textarea name="comment-0" id="tiny-comment-0" data-comment-id="0" class="tinymce-texteditor"></textarea>
        <div class="text-danger mt-2" id="comment-0Error"></div>
        <button type="button" id="create-btn" class="btn btn-space btn-success mt-2 mb-4">@lang ('labels.generic.submit')</button>
    </form>
@endauth

<div class="row" id="comment-counter">
    <div class="col-12 mb-2">
        <span class="float-end" id="nb-comments">{{ ($post->comments()->exists()) ? $post->comments->count() : 0 }}</span>
        <span class="float-end">@lang ('labels.post.comments'):&nbsp;</span>
    </div>
</div>

@if ($post->comments()->exists())
    @foreach ($post->comments as $comment)
        @include('themes.starter.partials.post.comment')
    @endforeach
@endif

@push('scripts')
    <script type="text/javascript" src="{{ asset('/vendor/tinymce/tinymce.min.js') }}"></script>
    <script src="{{ asset('/vendor/codalia/c.ajax.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/js/post/comment.js') }}"></script>
@endpush

