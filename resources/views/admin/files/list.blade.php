@extends ('admin.layouts.default')

@section ('header')
    <h3>@php echo __('labels.title.files'); @endphp</h3>
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

    <x-pagination :items=$items />

    <input type="hidden" id="destroyItems" value="{{ route('admin.files.index', $query) }}">

    <form id="selectedItems" action="{{ route('admin.files.index', $query) }}" method="post">
	@method('delete')
	@csrf
    </form>

    <div id="batch-window" class="modal">
	<div class="modal-content">
	    <iframe src="{{ route('admin.files.batch', $query) }}" id="batchIframe" name="batch"></iframe>
	</div>
    </div>
@endsection

@push ('scripts')
    <script src="{{ asset('/js/admin/list.js') }}"></script>
@endpush
