@extends ('admin.layouts.default')

@section ('main')
    <h3>@php echo (isset($post)) ? __('labels.post.edit_post') : __('labels.post.create_post'); @endphp</h3>

    @include('admin.partials.x-toolbar')

    @php $action = (isset($post)) ? route('admin.posts.update', $query) : route('admin.posts.store', $query); @endphp
    <form method="post" action="{{ $action }}" id="itemForm" enctype="multipart/form-data">
        @csrf

        @if (isset($post))
            @method('put')
        @endif

        @php $value = (isset($post)) ? old('title', $fields[0]->value) : old('title'); @endphp
        <x-input :field="$fields[0]" :value="$value" />
        @php array_shift($fields); // Remove the very first field (ie: title) from the array. @endphp

        <ul class="nav nav-tabs mt-4" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="details-tab" data-bs-toggle="tab" data-bs-target="#details-tab-pane" type="button" role="tab" aria-controls="details-tab-pane" aria-selected="true">@php echo __('labels.generic.details'); @endphp</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="extra-tab" data-bs-toggle="tab" data-bs-target="#extra-tab-pane" type="button" role="tab" aria-controls="extra-tab-pane" aria-selected="false">@php echo __('labels.generic.extra'); @endphp</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="layout_items-tab" data-bs-toggle="tab" data-bs-target="#layout_items-tab-pane" type="button" role="tab" aria-controls="layout_items-tab-pane" aria-selected="false">@php echo __('labels.generic.layout_items'); @endphp</button>
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
            @php
                    $dataTab = null;
                    $dateFormats = [];
            @endphp
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

                @php $value = (isset($post) || str_starts_with($field->name, 'alias_extra_field_')) ? old($field->name, $field->value) : old($field->name); @endphp
                <x-input :field="$field" :value="$value" />

                @if ($field->name == 'image')
                    <div class="col post-image mt-4">
                        @php $path = (isset($post) && $post->image) ? url('/').$post->image->getThumbnailUrl() : asset('/images/camera.png'); @endphp
                        <img src="{{ $path }}" id="post-image" />
                        <button type="button" id="deleteDocumentBtn" data-form-id="deleteImage" class="btn btn-danger float-end">Delete image</button>
                    </div>
                @endif

                @if ($field->name == 'page')
                    <div class="layout-items" id="layout-items">
                    </div>
                @endif

                @if ($field->type == 'date' && isset($field->dataset->format) && $field->name == 'updated_at')
                     @php $dateFormats[$field->name] = $field->dataset->format; @endphp
                @endif

                @if (!next($fields) || isset($fields[$key + 1]->tab))
                    </div>
                @endif
            @endforeach
        </div>

        <input type="hidden" id="cancelEdit" value="{{ route('admin.posts.cancel', $query) }}">
        <input type="hidden" id="close" name="_close" value="0">
        <input type="hidden" id="siteUrl" value="{{ url('/') }}">
        <input type="hidden" id="postLayout" value="{{ isset($post) ? route('admin.posts.layout', $query) : '' }}">
        <x-js-messages />

        @if (isset($post))
            <input type="hidden" id="_dateFormat" value="{{ $dateFormat }}">
            <input type="hidden" id="_locale" value="{{ config('app.locale') }}">
        @endif

        @foreach ($dateFormats as $key => $value)
            <input type="hidden" name="_date_formats[{{ $key }}]" value="{{ $value }}">
        @endforeach
    </form>

    @if (isset($post))
        <form id="deleteItem" action="{{ route('admin.posts.destroy', $query) }}" method="post">
            @method('delete')
            @csrf
        </form>

        <form id="deleteImage" action="{{ route('admin.posts.deleteImage', $query) }}" method="post">
            @method('delete')
            @csrf
        </form>

        <form id="deleteLayoutItem" action="{{ route('admin.posts.deleteLayoutItem', $query) }}" method="post">
            @method('delete')
            @csrf
            <input type="hidden" name="id_nb" id="_idNb" value="">
        </form>
    @endif
@endsection

@push ('style')
    <link rel="stylesheet" href="{{ asset('/vendor/codalia/css/c.layout.css') }}">
    <link rel="stylesheet" href="{{ asset('/vendor/codalia/css/c.datepicker.css') }}">
@endpush

@push ('scripts')
    <script src="https://cdn.jsdelivr.net/npm/dayjs@1/dayjs.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/dayjs@1/plugin/localeData.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/dayjs@1/locale/{{ config('app.locale') }}.js"></script>
    <script type="text/javascript" src="{{ asset('/vendor/codalia/lang/'.config('app.locale').'.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/vendor/codalia/c.datepicker.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/vendor/tinymce/tinymce.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/js/datepicker.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/vendor/codalia/c.ajax.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/vendor/codalia/c.layout.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/js/admin/post/layout.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/js/admin/form.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/js/admin/post/set.main.category.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/js/admin/disable.toolbars.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/js/tinymce/filemanager.js') }}"></script>
@endpush
