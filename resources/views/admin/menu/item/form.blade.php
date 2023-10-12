@extends ('admin.layouts.default')

@section ('main')
    <h3>@php echo (isset($item)) ? __('labels.menuitem.edit_menu_item') : __('labels.menuitem.create_menu_item'); @endphp</h3>

    @include('admin.partials.x-toolbar')

    @php $action = (isset($item)) ? route('admin.menus.items.update', $query) : route('admin.menus.items.store', $query) @endphp
    <form method="post" action="{{ $action }}" id="itemForm">
        @csrf

        @if (isset($item))
            @method('put')
        @endif

        @foreach ($fields as $field)
            @php $value = (isset($item)) ? old($field->name, $field->value) : old($field->name); @endphp
            <x-input :field="$field" :value="$value" />
        @endforeach

        <input type="hidden" id="cancelEdit" value="{{ route('admin.menus.items.cancel', $query) }}">
        <input type="hidden" id="close" name="_close" value="0">

        @if (isset($item))
            <input type="hidden" id="_dateFormat" value="{{ $dateFormat }}">
        @endif
    </form>

    @if (isset($item))
        <form id="deleteItem" action="{{ route('admin.menus.items.destroy', $query) }}" method="post">
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
    <script type="text/javascript" src="{{ asset('/vendor/codalia/c.ajax.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/js/daterangepicker.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/js/admin/form.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/js/admin/disable.toolbars.js') }}"></script>
@endpush
