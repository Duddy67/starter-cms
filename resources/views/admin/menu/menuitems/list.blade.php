@extends ('layouts.admin')

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

    <input type="hidden" id="createItem" value="{{ route('admin.menu.menuitems.create', $query) }}">
    <input type="hidden" id="destroyItems" value="{{ route('admin.menu.menuitems.index', $query) }}">
    <input type="hidden" id="checkinItems" value="{{ route('admin.menu.menuitems.massCheckIn', $query) }}">
    <input type="hidden" id="publishItems" value="{{ route('admin.menu.menuitems.massPublish', $query) }}">
    <input type="hidden" id="unpublishItems" value="{{ route('admin.menu.menuitems.massUnpublish', $query) }}">

    <form id="selectedItems" action="{{ route('admin.menu.menuitems.index', $query) }}" method="post">
        @method('delete')
        @csrf
    </form>
@endsection

@push ('scripts')
    <script src="{{ asset('/js/admin/list.js') }}"></script>
@endpush
