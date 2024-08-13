@extends ('admin.layouts.default')

@section ('main')
    <h3>@php echo (isset($category)) ? __('labels.category.edit_category') : __('labels.category.create_category'); @endphp</h3>

    @include('admin.partials.x-toolbar')

    @if (isset($category) && $owner->getRoleType() != 'super-admin' && !$owner->hasPermissionTo('create-'.$category->collection_type.'-categories'))
        <div class="alert alert-warning alert-block">
        <strong>{{ __('messages.generic.can_no_longer_create_item', ['name' => $owner->name]) }}</strong>
        <button type="button" class="btn-close float-end" data-dismiss="alert"></button>        
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

        <ul class="nav nav-tabs mt-4" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="details-tab" data-bs-toggle="tab" data-bs-target="#details-tab-pane" type="button" role="tab" aria-controls="details-tab-pane" aria-selected="true">@php echo __('labels.generic.details'); @endphp</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="settings-tab" data-bs-toggle="tab" data-bs-target="#settings-tab-pane" type="button" role="tab" aria-controls="settings-tab-pane" aria-selected="false">@php echo __('labels.title.settings'); @endphp</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="meta_data-tab" data-bs-toggle="tab" data-bs-target="#meta_data-tab-pane" type="button" role="tab" aria-controls="meta_data-tab-pane" aria-selected="false">@php echo __('labels.generic.meta_data'); @endphp</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="extra_fields-tab" data-bs-toggle="tab" data-bs-target="#extra_fields-tab-pane" type="button" role="tab" aria-controls="extra_fields-tab-pane" aria-selected="false">@php echo __('labels.generic.extra_fields'); @endphp</button>
            </li>
        </ul>

        <div class="tab-content" id="myTabContent">
            @php $dataTab = null; @endphp
            @foreach ($fields as $key => $field)
                @if (isset($field->tab))
                    @php $active = ($field->tab == 'details') ? 'show active' : '';
                         $dataTab = $field->tab; @endphp
                    <div class="tab-pane fade {{ $active }}" id="{{ $field->tab }}-tab-pane" role="tab-panel" aria-labelledby="{{ $field->tab }}-tab" tabindex="0">
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
                    <div class="col category-image mt-4">
                        @php $path = (isset($category) && $category->image) ? url('/').$category->image->getThumbnailUrl() : asset('/images/camera.png'); @endphp
                        <img src="{{ $path }}" id="category-image" />
                        <button type="button" id="deleteDocumentBtn" data-form-id="deleteImage" class="btn btn-danger float-end">Delete image</button>
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
        <input type="hidden" id="collectionType" value="{{ Str::plural($category->collection_type) }}">
        <x-js-messages />

        @if (isset($category))
            <input type="hidden" id="canEdit" value="{{ $category->canEdit() }}">
            <input type="hidden" id="_dateFormat" value="{{ $dateFormat }}">
            <input type="hidden" id="_locale" value="{{ config('app.locale') }}">
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
    <link rel="stylesheet" href="{{ asset('/vendor/codalia/css/c.datepicker.css') }}">
@endpush

@push ('scripts')
    <script src="https://cdn.jsdelivr.net/npm/dayjs@1/dayjs.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/dayjs@1/plugin/localeData.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/dayjs@1/locale/{{ config('app.locale') }}.js"></script>
    <script type="text/javascript" src="{{ asset('/vendor/codalia/lang/'.config('app.locale').'.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/vendor/codalia/c.datepicker.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/js/datepicker.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/vendor/tinymce/tinymce.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/vendor/codalia/c.ajax.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/js/admin/form.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/js/admin/disable.toolbars.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/js/admin/category/customized.items.per.page.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/js/tinymce/filemanager.js') }}"></script>
@endpush
