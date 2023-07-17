@extends ('admin.layouts.default')

@section ('header')
    <p class="h3">{{ __('labels.title.roles') }}</p>
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

    <input type="hidden" id="createItem" value="{{ route('admin.users.roles.create', $query) }}">
    <input type="hidden" id="destroyItems" value="{{ route('admin.users.roles.index', $query) }}">
    <input type="hidden" id="checkinItems" value="{{ route('admin.users.roles.massCheckIn', $query) }}">

    <form id="selectedItems" action="{{ route('admin.users.roles.index', $query) }}" method="post">
        @method('delete')
        @csrf
    </form>
@endsection

@push ('scripts')
    <script src="{{ asset('/js/admin/list.js') }}"></script>
@endpush
