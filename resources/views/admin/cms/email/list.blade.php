@extends('admin.layouts.default')

@section('header')
    <p class="h3">@php echo __('labels.title.emails'); @endphp</p>
@endsection

@section('main')
    @superadmin()
        <div class="card">
            <div class="card-body">
                <x-toolbar :items=$actions />
            </div>
        </div>
    @endsuperadmin

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

    <input type="hidden" id="createItem" value="{{ route('admin.cms.emails.create', $query) }}">
    <input type="hidden" id="destroyItems" value="{{ route('admin.cms.emails.index', $query) }}">
    <input type="hidden" id="checkinItems" value="{{ route('admin.cms.emails.massCheckIn', $query) }}">
    <input type="hidden" id="testEmailMessage" value="{{ $message }}">
    <x-js-messages />

    <form id="selectedItems" action="{{ route('admin.cms.emails.index', $query) }}" method="post">
        @method('delete')
        @csrf
    </form>

    <form id="sendTestEmail" action="{{ route('admin.cms.emails.test') }}" method="post">
        @method('get')
        @csrf
    </form>
@endsection

@push ('scripts')
    <script src="{{ asset('/js/admin/list.js') }}"></script>
    <script src="{{ asset('/js/admin/email/send.test.email.js') }}"></script>
@endpush
