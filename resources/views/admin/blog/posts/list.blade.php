@extends ('layouts.admin')

@section ('header')
    <p class="h3">{{ __('labels.title.posts') }}</p>
@endsection

@section ('main')
    <div class="card">
        <div class="card-body">
            <x-toolbar :items=$actions />
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <x-filters :filters="$filters" :url="$url" />
        </div>
    </div>

    @if (!empty($rows)) 
        <x-item-list :columns="$columns" :rows="$rows" :url="$url" />
    @else
        <div class="alert alert-info" role="alert">
            {{ __('messages.generic.no_item_found') }}
        </div>
    @endif

    <x-pagination :items=$items />

    <input type="hidden" id="createItem" value="{{ route('admin.blog.posts.create', $query) }}">
    <input type="hidden" id="destroyItems" value="{{ route('admin.blog.posts.index', $query) }}">
    <input type="hidden" id="checkinItems" value="{{ route('admin.blog.posts.massCheckIn', $query) }}">
    <input type="hidden" id="publishItems" value="{{ route('admin.blog.posts.massPublish', $query) }}">
    <input type="hidden" id="unpublishItems" value="{{ route('admin.blog.posts.massUnpublish', $query) }}">

    <form id="selectedItems" action="{{ route('admin.blog.posts.index', $query) }}" method="post">
        @method('delete')
        @csrf
    </form>

    <div id="batch-window" class="modal">
        <div class="modal-content">
            <iframe src="{{ route('admin.blog.posts.batch', $query) }}" id="batchIframe" name="batch"></iframe>
        </div>
    </div>
@endsection

@push ('scripts')
    <script src="{{ asset('/js/admin/list.js') }}"></script>
@endpush
