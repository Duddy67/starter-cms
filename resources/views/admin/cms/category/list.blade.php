@extends ('admin.layouts.default')

@section ('header')
    <p class="h3">{{ __('labels.title.categories') }}</p>
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

    <input type="hidden" id="createItem" value="{{ route('admin.'.$collection.'.categories.create', $query) }}">
    <input type="hidden" id="destroyItems" value="{{ route('admin.'.$collection.'.categories.index', $query) }}">
    <input type="hidden" id="checkinItems" value="{{ route('admin.'.$collection.'.categories.massCheckIn', $query) }}">
    <input type="hidden" id="publishItems" value="{{ route('admin.'.$collection.'.categories.massPublish', $query) }}">
    <input type="hidden" id="unpublishItems" value="{{ route('admin.'.$collection.'.categories.massUnpublish', $query) }}">
    <x-js-messages />

    <form id="selectedItems" action="{{ route('admin.'.$collection.'.categories.index', $query) }}" method="post">
        @method('delete')
        @csrf
    </form>
@endsection

@push ('scripts')
    <script src="{{ asset('/js/admin/list.js') }}"></script>
@endpush
