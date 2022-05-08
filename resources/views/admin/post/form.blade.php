@extends ('layouts.admin')

@section ('main')
    <h3>@php echo (isset($post)) ? __('labels.post.edit_post') : __('labels.post.create_post'); @endphp</h3>

    @php $action = (isset($post)) ? route('admin.posts.update', $query) : route('admin.posts.store', $query) @endphp
    <form method="post" action="{{ $action }}" id="itemForm" enctype="multipart/form-data">
        @csrf

        @if (isset($post))
            @method('put')
        @endif

         @php $value = (isset($post)) ? old('title', $fields[0]->value) : old('title'); @endphp
         <x-input :field="$fields[0]" :value="$value" />
         @php array_shift($fields); // Remove the very first field (ie: title) from the array. @endphp

        <nav class="nav nav-tabs">
            <a class="nav-item nav-link" href="#details" data-toggle="tab">@php echo __('labels.generic.details'); @endphp</a>
            <a class="nav-item nav-link" href="#extra" data-toggle="tab">@php echo __('labels.generic.extra'); @endphp</a>
            <a class="nav-item nav-link" href="#settings" data-toggle="tab">@php echo __('labels.title.settings'); @endphp</a>
        </nav>

        <div class="tab-content">
            @foreach ($fields as $key => $field)
                @if (isset($field->tab))
                    @php $active = ($field->tab == $tab) ? ' active' : ''; @endphp
                    <div class="tab-pane{{ $active }}" id="{{ $field->tab }}">
                @endif

                @php $value = (isset($post)) ? old($field->name, $field->value) : old($field->name); @endphp
                <x-input :field="$field" :value="$value" />

                @if ($field->name == 'image')
                    <div class="col post-image">
                    @if (isset($post) && $post->image) 
                        <img src="{{ url('/').$post->image->getThumbnailUrl() }}" >
                    @else
                        <img src="{{ asset('/images/camera.png') }}" height="100" width="100" alt="post-photo">
                    @endif
                    </div>
                @endif

                @if (!next($fields) || isset($fields[$key + 1]->tab))
                    </div>
                @endif
            @endforeach
        </div>

        <input type="hidden" id="cancelEdit" value="{{ route('admin.posts.cancel', $query) }}">
        <input type="hidden" id="close" name="_close" value="0">
        <input type="hidden" id="siteUrl" name="_siteUrl" value="{{ url('/') }}">
        <input type="hidden" id="activeTab" name="_tab" value="{{ $tab }}">

        @if (isset($post))
            <input type="hidden" id="canEdit" value="{{ $post->canEdit() }}">
        @endif
    </form>
    <x-toolbar :items=$actions />

    @if (isset($post))
        <form id="deleteItem" action="{{ route('admin.posts.destroy', $query) }}" method="post">
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
    <script type="text/javascript" src="{{ asset('/js/admin/daterangepicker.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/js/admin/form.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/js/admin/post/set.main.category.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/js/admin/set.private.groups.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/js/admin/disable.toolbars.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/js/tinymce/filemanager.js') }}"></script>
@endpush
