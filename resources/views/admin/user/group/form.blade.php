@extends ('layouts.admin')

@section ('main')
    <h3>@php echo (isset($group)) ? __('labels.group.edit_group') : __('labels.group.create_group'); @endphp</h3>

    @php $action = (isset($group)) ? route('admin.user.groups.update', $query) : route('admin.user.groups.store', $query) @endphp
    <form method="post" action="{{ $action }}" id="itemForm">
        @csrf

        @if (isset($group))
            @method('put')
        @endif

        @foreach ($fields as $field)
            @php $value = (isset($group)) ? old($field->name, $field->value) : old($field->name); @endphp
            <x-input :field="$field" :value="$value" />
        @endforeach

        <input type="hidden" id="cancelEdit" value="{{ route('admin.user.groups.cancel', $query) }}">
        <input type="hidden" id="close" class="_ajax" name="_close" value="0">
        <input type="hidden" id="listUrl" value="{{ route('admin.user.groups.index', $query) }}">
    </form>
    <x-toolbar :items=$actions />

    @if (isset($group))
        <form id="deleteItem" action="{{ route('admin.user.groups.destroy', $query) }}" method="post">
            @method('delete')
            @csrf
        </form>
    @endif
@endsection

@push ('style')
    <link rel="stylesheet" href="{{ asset('/vendor/adminlte/plugins/daterangepicker/daterangepicker.css') }}">
@endpush

@push ('scripts')
    <script type="text/javascript" src="{{ asset('/vendor/adminlte/plugins/moment/moment.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/vendor/adminlte/plugins/daterangepicker/daterangepicker.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/vendor/tinymce/tinymce.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/js/admin/daterangepicker.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/js/admin/form.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/js/admin/disable.toolbars.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/js/tinymce/filemanager.js') }}"></script>
@endpush
