@extends ('admin.layouts.default')

@section ('main')
    <h3>@php echo __('labels.post.blog_global_settings'); @endphp</h3>

    @include('admin.partials.x-toolbar')

    <form method="post" action="{{ route('admin.posts.settings.update', $query ) }}" id="itemForm">
        @csrf
        @method('patch')

        <ul class="nav nav-tabs mt-4" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="posts-tab" data-bs-toggle="tab" data-bs-target="#posts-tab-pane" type="button" role="tab" aria-controls="posts-tab-pane" aria-selected="true">@php echo __('labels.title.posts'); @endphp</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="categories-tab" data-bs-toggle="tab" data-bs-target="#categories-tab-pane" type="button" role="tab" aria-controls="categories-tab-pane" aria-selected="false">@php echo __('labels.title.categories'); @endphp</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="extra_field_aliases-tab" data-bs-toggle="tab" data-bs-target="#extra_field_aliases-tab-pane" type="button" role="tab" aria-controls="extra_field_aliases-tab-pane" aria-selected="false">@php echo __('labels.generic.extra_field_aliases'); @endphp</button>
            </li>
        </ul>

        <div class="tab-content" id="myTabContent">
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
                    @php $active = ($field->tab == 'posts') ? 'show active' : '';
                         $dataTab = $field->tab; @endphp
                    <div class="tab-pane fade {{ $active }}" id="{{ $field->tab }}-tab-pane" role="tab-panel" aria-labelledby="{{ $field->tab }}-tab" tabindex="0">
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
