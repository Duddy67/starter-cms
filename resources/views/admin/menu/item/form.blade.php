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

            @if ($field->name == 'locale')
                @php $value = $locale; @endphp
            @endif

            <x-input :field="$field" :value="$value" />
        @endforeach

        <input type="hidden" id="currentLocale" value="{{ $locale }}">
        <input type="hidden" id="cancelChangeLocale" value="0">
        <input type="hidden" id="cancelEdit" value="{{ route('admin.menus.items.cancel', $query) }}">
        <input type="hidden" id="close" name="_close" value="0">
        <x-js-messages />

        @if (isset($item))
            <input type="hidden" id="_dateFormat" value="{{ $dateFormat }}">
            <input type="hidden" id="_locale" value="{{ config('app.locale') }}">
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
    <link rel="stylesheet" href="{{ asset('/vendor/codalia/css/c.datepicker.css') }}">
@endpush

@push ('scripts')
    <script src="https://cdn.jsdelivr.net/npm/dayjs@1/dayjs.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/dayjs@1/plugin/localeData.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/dayjs@1/locale/{{ config('app.locale') }}.js"></script>
    <script type="text/javascript" src="{{ asset('/vendor/codalia/lang/'.config('app.locale').'.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/vendor/codalia/c.datepicker.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/js/datepicker.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/vendor/codalia/c.ajax.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/js/admin/locale.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/js/admin/form.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/js/admin/disable.toolbars.js') }}"></script>
@endpush
