@extends ('admin.layouts.default')

@section ('main')

    <h3>@php echo (isset($user)) ? __('labels.user.edit_user') : __('labels.user.create_user'); @endphp</h3>

    @include('admin.partials.x-toolbar')

    @php $action = (isset($user)) ? route('admin.users.update', $query) : route('admin.users.store', $query) @endphp

    <form method="post" action="{{ $action }}" id="itemForm" enctype="multipart/form-data">
        @csrf

        @if (isset($user))
            @method('put')
        @endif

        @foreach ($fields as $field)
            @php if (isset($user)) { 
                     $value = old($field->name, $field->value);
                     // The current user is editing their own account or the role is private.
                     if ($field->name == 'role' && (auth()->user()->id == $user->id || $user->isRolePrivate())) {
                         $field->extra = ['disabled'];
                     }
                 }
                 else {
                     if ($field->name == 'created_at' || $field->name == 'updated_at') {
                         continue;
                     }

                     $value = old($field->name);
                 }
            @endphp

            <div class="form-group">
                <x-input :field="$field" :value="$value" />
            </div>

            @if ($field->name == 'photo')
                <div class="col post-image mt-4">
                    @php $path = (isset($user) && $user->photo) ?  url('/').$user->photo->getThumbnailUrl() : asset('/images/user.png'); @endphp
                    <img src="{{ $path }}" id="user-photo" />
                    <button type="button" id="deleteDocumentBtn" data-form-id="deletePhoto" class="btn btn-danger float-end">Delete photo</button>
                </div>
            @endif
        @endforeach

        <input type="hidden" id="cancelEdit" value="{{ route('admin.users.cancel', $query) }}">
        <input type="hidden" id="close" name="_close" value="0">
        <x-js-messages />

        @if (isset($user))
            <input type="hidden" id="_dateFormat" value="{{ $dateFormat }}">
            <input type="hidden" id="_locale" value="{{ config('app.locale') }}">
        @endif
    </form>

    @if (isset($user))
        <form id="deleteItem" action="{{ route('admin.users.destroy', $query) }}" method="post">
            @method('delete')
            @csrf
        </form>

        <form id="deletePhoto" action="{{ route('admin.users.deletePhoto', $query) }}" method="post">
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

