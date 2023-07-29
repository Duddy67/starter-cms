<div class="card mb-4" id="card-comment-{{ $comment->id }}">
    <div class="card-header">
        <div class="row">
            <div class="col-12">
            {{ __('labels.post.posted_by', ['author' => $comment->author]) }} at @date ($comment->created_at->tz($page['timezone']))

            @if (auth()->check() && Auth::user()->id == $comment->owned_by)
                <button type="button" id="delete-btn-{{ $comment->id }}" data-comment-id="{{ $comment->id }}" class="btn btn-space btn-danger float-end deleteButton">@lang ('labels.button.delete')</button>
                <span class="float-end">&nbsp;</span>
                <button type="button" id="edit-btn-{{ $comment->id }}" data-comment-id="{{ $comment->id }}" class="btn btn-space btn-primary float-end">@lang ('labels.button.edit')</button>
            @endif
            </div>
        </div>

        @if (auth()->check() && Auth::user()->id == $comment->owned_by)
            <div class="alert alert-success alert-block mt-2 flash-message d-none" id="ajax-message-alert-{{ $comment->id }}">
                <button type="button" class="btn-close" onclick="document.getElementById('ajax-message-alert-{{ $comment->id }}').classList.add('d-none');"></button>
                <strong id="ajax-message-{{ $comment->id }}"></strong>
            </div>

            <form id="updateComment-{{ $comment->id }}" action="{{ route('posts.comments.update', ['comment' => $comment]) }}" style="display:none;" method="post">
                @method('put')
                @csrf

                <textarea name="comment-{{ $comment->id }}" id="tiny-comment-{{ $comment->id }}" data-comment-id="{{ $comment->id }}" class="tinymce-texteditor">{{ $comment->text }}</textarea>
                <button type="button" id="update-btn-{{ $comment->id }}" data-comment-id="{{ $comment->id }}" class="btn btn-space btn-success mt-2">@lang ('labels.button.update')</button>
                <button type="button" id="cancel-btn-{{ $comment->id }}" data-comment-id="{{ $comment->id }}" class="btn btn-space btn-info mt-2">@lang ('labels.button.cancel')</button>
                <div class="text-danger mt-2" id="comment-{{ $comment->id }}Error"></div>
            </form>

            <form id="deleteComment-{{ $comment->id }}" action="{{ route('posts.comments.delete', ['comment' => $comment]) }}" method="post">
                @method('delete')
                @csrf
            </form>
        @endif
    </div>
    <div id="comment-{{ $comment->id }}" class="card-body">
        {!! $comment->text !!}
    </div>
</div>

