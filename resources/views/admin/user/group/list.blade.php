@extends ('admin.layouts.default')

@section ('header')
    <p class="h3">{{ __('labels.title.groups') }}</p>
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

    <input type="hidden" id="createItem" value="{{ route('admin.user.groups.create', $query) }}">
    <input type="hidden" id="destroyItems" value="{{ route('admin.user.groups.index', $query) }}">
    <input type="hidden" id="checkinItems" value="{{ route('admin.user.groups.massCheckIn', $query) }}">

    <form id="selectedItems" action="{{ route('admin.user.groups.index', $query) }}" method="post">
        @method('delete')
        @csrf
    </form>

    <div id="batch-window" class="modal">
        <div class="modal-content">
            <iframe src="{{ route('admin.user.groups.batch', $query) }}" id="batchIframe" name="batch"></iframe>
        </div>
    </div>
@endsection

@push ('scripts')
    <script src="{{ asset('/js/admin/list.js') }}"></script>
@endpush
