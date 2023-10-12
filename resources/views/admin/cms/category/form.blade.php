@extends ('admin.layouts.default')

@section ('main')
    <h3>@php echo (isset($category)) ? __('labels.category.edit_category') : __('labels.category.create_category'); @endphp</h3>

    @include('admin.partials.x-toolbar')

    @if (isset($category) && $owner->getRoleType() != 'super-admin' && !$owner->hasPermissionTo('create-'.$category->collection_type.'-categories'))
        <div class="alert alert-warning alert-block">
        <button type="button" class="close" data-dismiss="alert">×</button>        
        <strong>{{ __('messages.generic.can_no_longer_create_item', ['name' => $owner->name]) }}</strong>
        </div>
    @endif

    @php $action = (isset($category)) ? route('admin.'.$collection.'.categories.update', $query) : route('admin.'.$collection.'.categories.store', $query) @endphp
    <form method="post" action="{{ $action }}" id="itemForm">
        @csrf

        @if (isset($category))
            @method('put')
        @endif

         @php $value = (isset($category)) ? old('name', $fields[0]->value) : old('name'); @endphp
         <x-input :field="$fields[0]" :value="$value" />
         @php array_shift($fields); // Remove the very first field (ie: name) from the array. @endphp

        <nav class="nav nav-tabs">
            <a class="nav-item nav-link active" href="#details" data-toggle="tab">@php echo __('labels.generic.details'); @endphp</a>
            <a class="nav-item nav-link" href="#settings" data-toggle="tab">@php echo __('labels.title.settings'); @endphp</a>
            <a class="nav-item nav-link" href="#meta_data" data-toggle="tab">@php echo __('labels.generic.meta_data'); @endphp</a>
            <a class="nav-item nav-link" href="#extra_fields" data-toggle="tab">@php echo __('labels.generic.extra_fields'); @endphp</a>
        </nav>

        <div class="tab-content">
            @php $dataTab = null; @endphp
            @foreach ($fields as $key => $field)
                @if (isset($field->tab))
                    @php $active = ($field->tab == 'details') ? ' active' : '';
                         $dataTab = $field->tab; @endphp
                    <div class="tab-pane{{ $active }}" id="{{ $field->tab }}">
                @endif

                @if (isset($field->dataset))
                    @php $field->dataset->tab = $dataTab; @endphp
                @else
                    @php $dataset = (object) ['tab' => $dataTab];
                         $field->dataset = $dataset; @endphp
                @endif

                @php $value = (isset($category) || str_starts_with($field->name, 'alias_extra_field_')) ? old($field->name, $field->value) : old($field->name); @endphp
                <x-input :field="$field" :value="$value" />

                @if ($field->name == 'image')
                    <div class="col category-image">
                        @php $path = (isset($category) && $category->image) ? url('/').$category->image->getThumbnailUrl() : asset('/images/camera.png'); @endphp
                        <img src="{{ $path }}" id="category-image" />
                        <button type="button" id="deleteDocumentBtn" data-form-id="deleteImage" class="btn btn-danger float-right">Delete image</button>
                    </div>
                @endif

                @if (!next($fields) || isset($fields[$key + 1]->tab))
                    </div>
                @endif
            @endforeach
        </div>

        <input type="hidden" id="cancelEdit" value="{{ route('admin.'.$collection.'.categories.cancel', $query) }}">
        <input type="hidden" id="siteUrl" value="{{ url('/') }}">
        <input type="hidden" id="close" name="_close" value="0">

        @if (isset($category))
            <input type="hidden" id="canEdit" value="{{ $category->canEdit() }}">
            <input type="hidden" id="_dateFormat" value="{{ $dateFormat }}">
        @endif
    </form>

    @if (isset($category))
        <form id="deleteItem" action="{{ route('admin.'.$collection.'.categories.destroy', $query) }}" method="post">
            @method('delete')
            @csrf
        </form>

        <form id="deleteImage" action="{{ route('admin.'.$collection.'.categories.deleteImage', $query) }}" method="post">
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
    <script type="text/javascript" src="{{ asset('/vendor/codalia/c.ajax.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/js/daterangepicker.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/js/admin/form.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/js/admin/set.private.groups.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/js/admin/disable.toolbars.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/js/tinymce/filemanager.js') }}"></script>
@endpush
