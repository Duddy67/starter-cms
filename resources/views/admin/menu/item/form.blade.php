@extends ('admin.layouts.default')

@section ('main')
    <h3>@php echo (isset($item)) ? __('labels.menuitem.edit_menu_item') : __('labels.menuitem.create_menu_item'); @endphp</h3>

    @php $action = (isset($item)) ? route('admin.menu.items.update', $query) : route('admin.menu.items.store', $query) @endphp
    <form method="post" action="{{ $action }}" id="itemForm">
        @csrf

        @if (isset($item))
            @method('put')
        @endif

        @foreach ($fields as $field)
            @php $value = (isset($item)) ? old($field->name, $field->value) : old($field->name); @endphp

            @if ($field->name == 'locale')
                @php $value = $locale; @endphp
            @endif

            <x-input :field="$field" :value="$value" />
        @endforeach

        <input type="hidden" id="currentLocale" value="{{ $locale }}">
        <input type="hidden" id="cancelChangeLocale" value="0">
        <input type="hidden" id="cancelEdit" value="{{ route('admin.menu.items.cancel', $query) }}">
        <input type="hidden" id="close" name="_close" value="0">
    </form>
    <x-toolbar :items=$actions />

    @if (isset($item))
        <form id="deleteItem" action="{{ route('admin.menu.items.destroy', $query) }}" method="post">
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
    <script type="text/javascript" src="{{ asset('/js/admin/daterangepicker.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/js/admin/locale.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/js/admin/form.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/js/admin/disable.toolbars.js') }}"></script>
@endpush
