@extends ('admin.layouts.default')

@section ('main')
    <h3>@php echo (isset($role)) ? __('labels.role.edit_role') : __('labels.role.create_role'); @endphp</h3>

    @include('admin.partials.x-toolbar')

    @php $action = (isset($role)) ? route('admin.users.roles.update', $query) : route('admin.users.roles.store', $query) @endphp
    <form method="post" action="{{ $action }}" id="itemForm">
        @csrf

        @if (isset($role))
            @method('put')
        @endif

        @foreach ($fields as $field)
            @php $value = (isset($role)) ? old($field->name, $field->value) : old($field->name); @endphp

            <div class="form-group">
                <x-input :field="$field" :value="$value" />
            </div>
        @endforeach

        <h4 class="pt-3">{{ __('labels.title.permissions') }}</h4>
        @foreach ($board as $section => $checkboxes)
            <h5 class="font-weight-bold">{{ $section }}</h5>
            <table class="table table-striped">
                <tbody>
                    @foreach ($checkboxes as $key => $checkbox)

                        @if (is_array(old('permissions')) && in_array($checkbox->value, old('permissions')))
                            @php $checkbox->checked = true; @endphp
                        @elseif (is_array(old('permissions')) && !in_array($checkbox->value, old('permissions')))
                            @php $checkbox->checked = false; @endphp
                        @endif

                        <tr>
                            <td>
                                <div class="form-check">
                                    <x-input :field="$checkbox" :value="$checkbox->value" />
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endforeach

        <input type="hidden" id="cancelEdit" value="{{ route('admin.users.roles.cancel', $query) }}">
        <input type="hidden" id="close" name="_close" value="0">
        <input type="hidden" id="reloaded" value="{{ is_array(old('permissions')) ? 1 : 0 }}">
        <x-js-messages />

        @if (isset($role))
            <input type="hidden" id="_dateFormat" value="{{ $dateFormat }}">
            <input type="hidden" id="_locale" value="{{ config('app.locale') }}">
        @endif

        @if (!isset($role))
            <input type="hidden" id="permissions" name="_permissions" value="{{ $permissions }}">
        @endif
    </form>

    @if (isset($role))
        <form id="deleteItem" action="{{ route('admin.users.roles.destroy', $query) }}" method="post">
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
    <script type="text/javascript" src="{{ asset('/js/admin/user/role/settings.js') }}"></script>
@endpush
