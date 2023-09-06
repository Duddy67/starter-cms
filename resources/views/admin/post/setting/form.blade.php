@extends ('admin.layouts.default')

@section ('main')
    <h3>@php echo __('labels.post.blog_global_settings'); @endphp</h3>

    @include('admin.partials.x-toolbar')

    <form method="post" action="{{ route('admin.posts.settings.update', $query ) }}" id="itemForm">
        @csrf
        @method('patch')

        <nav class="nav nav-tabs">
            <a class="nav-item nav-link active" href="#posts" data-toggle="tab">@php echo __('labels.title.posts'); @endphp</a>
            <a class="nav-item nav-link" href="#categories" data-toggle="tab">@php echo __('labels.title.categories'); @endphp</a>
            <a class="nav-item nav-link" href="#extra_field_aliases" data-toggle="tab">@php echo __('labels.generic.extra_field_aliases'); @endphp</a>
        </nav>

        <div class="tab-content">
            @foreach ($fields as $key => $field)
                @php if (isset($data[$field->group][$field->name])) { 
                         $value = old($field->name, $data[$field->group][$field->name]);
                     }
                     else {
                         $value = old($field->name);
                     }
                @endphp

                @php $dataTab = null; @endphp
                @if (isset($field->tab))
                    @php $active = ($field->tab == 'posts') ? ' active' : '';
                         $dataTab = $field->tab; @endphp
                    <div class="tab-pane{{ $active }}" id="{{ $field->tab }}">
                @endif

                @if (isset($field->dataset))
                    @php $field->dataset->tab = $dataTab; @endphp
                @else
                    @php $dataset = (object) ['tab' => $dataTab];
                         $field->dataset = $dataset; @endphp
                @endif

                @if ($field->name == 'alias_extra_field_1')
                    <h2>{{ __('labels.title.'.$field->group) }}</h2>
                @endif

                <x-input :field="$field" :value="$value" />

                @if (!next($fields) || isset($fields[$key + 1]->tab))
                    </div>
                @endif
            @endforeach
        </div>

    </form>
@endsection

@push ('style')
    <link rel="stylesheet" href="{{ asset('/vendor/adminlte/plugins/jquery-ui/jquery-ui.min.css') }}">
@endpush

@push ('scripts')
    <script type="text/javascript" src="{{ asset('/vendor/adminlte/plugins/jquery-ui/jquery-ui.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/vendor/codalia/c.ajax.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/js/admin/form.js') }}"></script>
@endpush
