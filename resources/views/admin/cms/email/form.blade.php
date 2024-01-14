@extends ('admin.layouts.default')

@section ('main')
    <h3>@php echo (isset($email)) ? __('labels.email.edit_email') : __('labels.email.create_email'); @endphp</h3>

    @include('admin.partials.x-toolbar')

    @php $action = (isset($email)) ? route('admin.cms.emails.update', $query) : route('admin.cms.emails.store', $query) @endphp
    <form method="post" action="{{ $action }}" id="itemForm">
        @csrf

	@if (isset($email))
	    @method('put')
	@endif

        @foreach ($fields as $field)
	    @php if (isset($email)) { 
		     $value = old($field->name, $field->value);

                     if ($field->name == 'access_level' && $email->role_level > auth()->user()->getRoleLevel()) {
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

	    @if ($field->name == 'body_html')
	        <ul class="nav nav-tabs" id="myTab" role="tablist">
		    <li class="nav-item">
			<a  class="nav-link active" id="html-tab" href="#html" data-toggle="tab" aria-controls="html" aria-selected="true">HTML</a>
		    </li>
		    <li class="nav-item">
                        <a class="nav-link" id="text-tab" href="#text" data-toggle="tab" aria-controls="text" aria-selected="false">Plain text</a>
                    </li>
                </ul>

		<div class="tab-content" id="myTabContent">
		    <div class="tab-pane active" id="html" role="tabpanel" aria-labelledby="html-tab">
	    @endif

	    @if ($field->name == 'body_text')
	        <div class="tab-pane" id="text" role="tabpanel" aria-labelledby="text-tab">
	    @endif

            @if ($field->name == 'locale')
                @php $value = $locale; @endphp
            @endif

	    <x-input :field="$field" :value="$value" />

	    @if ($field->name == 'body_html')
	        </div>
	    @endif

	    @if ($field->name == 'body_text')
	        </div>
	        </div>
	    @endif
        @endforeach

        <input type="hidden" id="currentLocale" value="{{ $locale }}">
        <input type="hidden" id="cancelChangeLocale" value="0">
	<input type="hidden" id="cancelEdit" value="{{ route('admin.cms.emails.cancel', $query) }}">
	<input type="hidden" id="close" name="_close" value="0">
        <x-js-messages />

        @if (isset($email))
            <input type="hidden" id="_dateFormat" value="{{ $dateFormat }}">
            <input type="hidden" id="_locale" value="{{ config('app.locale') }}">
        @endif
    </form>

    @if (isset($email))
	<form id="deleteItem" action="{{ route('admin.cms.emails.destroy', $query) }}" method="post">
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
    <script type="text/javascript" src="{{ asset('/js/admin/locale.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/js/admin/form.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/js/admin/disable.toolbars.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/js/tinymce/filemanager.js') }}"></script>
@endpush
