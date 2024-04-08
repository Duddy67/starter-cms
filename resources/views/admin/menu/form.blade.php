@extends ('admin.layouts.default')

@section ('main')
    <h3>@php echo (isset($menu)) ? __('labels.menu.edit_menu') : __('labels.menu.create_menu'); @endphp</h3>

    @include('admin.partials.x-toolbar')

    @php $action = (isset($menu)) ? route('admin.menus.update', $query) : route('admin.menus.store', $query) @endphp
    <form method="post" action="{{ $action }}" id="itemForm">
        @csrf

        @if (isset($menu))
            @method('put')
        @endif

        @foreach ($fields as $field)
            @php $value = (isset($menu)) ? old($field->name, $field->value) : old($field->name); @endphp
            <x-input :field="$field" :value="$value" />
        @endforeach

        <input type="hidden" id="cancelEdit" value="{{ route('admin.menus.cancel', $query) }}">
        <input type="hidden" id="close" name="_close" value="0">
        <x-js-messages />

        @if (isset($menu))
            <input type="hidden" id="_dateFormat" value="{{ $dateFormat }}">
            <input type="hidden" id="_locale" value="{{ config('app.locale') }}">
        @endif
    </form>

    @if (isset($menu))
        <form id="deleteItem" action="{{ route('admin.menus.destroy', $query) }}" method="post">
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
    <script type="text/javascript" src="{{ asset('/js/admin/form.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/js/admin/disable.toolbars.js') }}"></script>
@endpush
