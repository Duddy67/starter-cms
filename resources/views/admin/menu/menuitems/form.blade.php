@extends ('layouts.admin')

@section ('main')
    <h3>@php echo (isset($menuItem)) ? __('labels.menuitems.edit_menu_item') : __('labels.menuitems.create_menu_item'); @endphp</h3>

    @php $action = (isset($menuItem)) ? route('admin.menu.menuitems.update', $query) : route('admin.menu.menuitems.store', $query) @endphp
    <form method="post" action="{{ $action }}" id="itemForm">
        @csrf

        @if (isset($menuItem))
            @method('put')
        @endif

        @foreach ($fields as $field)
            @php $value = (isset($menuItem)) ? old($field->name, $field->value) : old($field->name); @endphp
            <x-input :field="$field" :value="$value" />
        @endforeach

        <input type="hidden" id="cancelEdit" value="{{ route('admin.menu.menuitems.cancel', $query) }}">
        <input type="hidden" id="close" name="_close" value="0">
    </form>
    <x-toolbar :items=$actions />

    @if (isset($menuItem))
        <form id="deleteItem" action="{{ route('admin.menu.menuitems.destroy', $query) }}" method="post">
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
    <script type="text/javascript" src="{{ asset('/js/admin/daterangepicker.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/js/admin/form.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/js/admin/disable.toolbars.js') }}"></script>
@endpush
