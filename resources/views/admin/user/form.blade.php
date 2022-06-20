@extends ('admin.layouts.default')

@section ('main')

    <h3>@php echo (isset($user)) ? __('labels.user.edit_user') : __('labels.user.create_user'); @endphp</h3>

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
                <div class="col post-image">
                    @php $path = (isset($user) && $user->photo) ?  url('/').$user->photo->getThumbnailUrl() : asset('/images/user.png'); @endphp
                    @php $deletePhotoUrl = (isset($user) && $user->photo) ?  route('admin.users.deletePhoto', $query) : ''; @endphp
                    <img src="{{ $path }}" id="user-photo" />
                    <button type="button" id="deleteDocumentBtn" class="btn btn-danger float-right">Delete photo</button>
                    <input type="hidden" id="deleteDocumentUrl" value="{{ $deletePhotoUrl }}">
                </div>
            @endif
        @endforeach

        <input type="hidden" id="cancelEdit" value="{{ route('admin.users.cancel', $query) }}">
        <input type="hidden" id="close" name="_close" value="0">
    </form>

    <div class="form-group">
        <x-toolbar :items=$actions />
    </div>

    @if (isset($user))
        <form id="deleteItem" action="{{ route('admin.users.destroy', $query) }}" method="post">
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
    <script type="text/javascript" src="{{ asset('/js/admin/delete.document.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/js/admin/set.private.groups.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/js/admin/disable.toolbars.js') }}"></script>
@endpush

