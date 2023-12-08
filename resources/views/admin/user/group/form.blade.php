@extends ('admin.layouts.default')

@section ('main')
    <h3>@php echo (isset($group)) ? __('labels.group.edit_group') : __('labels.group.create_group'); @endphp</h3>

    @include('admin.partials.x-toolbar')

    @php $action = (isset($group)) ? route('admin.users.groups.update', $query) : route('admin.users.groups.store', $query) @endphp
    <form method="post" action="{{ $action }}" id="itemForm">
        @csrf

        @if (isset($group))
            @method('put')
        @endif

        @foreach ($fields as $field)
            @php $value = (isset($group)) ? old($field->name, $field->value) : old($field->name); @endphp
            <x-input :field="$field" :value="$value" />
        @endforeach

        <input type="hidden" id="cancelEdit" value="{{ route('admin.users.groups.cancel', $query) }}">
        <input type="hidden" name="_close" id="close" value="0">

        @if (isset($group))
            <input type="hidden" id="_dateFormat" value="{{ $dateFormat }}">
            <input type="hidden" id="_locale" value="{{ config('app.locale') }}">
        @endif
    </form>

    @if (isset($group))
        <form id="deleteItem" action="{{ route('admin.users.groups.destroy', $query) }}" method="post">
            @method('delete')
            @csrf
        </form>
    @endif
@endsection

@push ('style')
    <link rel="stylesheet" href="{{ asset('/vendor/codalia/css/c.datepicker.css') }}">
@endpush

@push ('scripts')
    <script src="https://cdn.jsdelivr.net/npm/dayjs@1/dayjs.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/dayjs@1/plugin/localeData.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/dayjs@1/locale/{{ config('app.locale') }}.js"></script>
    <script type="text/javascript" src="{{ asset('/vendor/codalia/lang/'.config('app.locale').'.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/vendor/codalia/c.datepicker.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/js/datepicker.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/vendor/tinymce/tinymce.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/vendor/codalia/c.ajax.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/js/admin/form.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/js/admin/disable.toolbars.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/js/tinymce/filemanager.js') }}"></script>
@endpush
