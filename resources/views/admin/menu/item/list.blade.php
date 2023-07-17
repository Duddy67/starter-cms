@extends ('admin.layouts.default')

@section ('header')
    <p class="h3">Menu items</p>
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
            No item has been found.
        </div>
    @endif

    <input type="hidden" id="createItem" value="{{ route('admin.menus.items.create', $query) }}">
    <input type="hidden" id="destroyItems" value="{{ route('admin.menus.items.index', $query) }}">
    <input type="hidden" id="checkinItems" value="{{ route('admin.menus.items.massCheckIn', $query) }}">
    <input type="hidden" id="publishItems" value="{{ route('admin.menus.items.massPublish', $query) }}">
    <input type="hidden" id="unpublishItems" value="{{ route('admin.menus.items.massUnpublish', $query) }}">

    <form id="selectedItems" action="{{ route('admin.menus.items.index', $query) }}" method="post">
        @method('delete')
        @csrf
    </form>
@endsection

@push ('scripts')
    <script src="{{ asset('/js/admin/list.js') }}"></script>
@endpush
