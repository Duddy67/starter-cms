@extends ('layouts.admin')

@section ('main')
    <h3>@php echo (isset($email)) ? __('labels.email.edit_email') : __('labels.email.create_email'); @endphp</h3>

    @php $action = (isset($email)) ? route('admin.settings.emails.update', $query) : route('admin.settings.emails.store', $query) @endphp
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

	    <x-input :field="$field" :value="$value" />

	    @if ($field->name == 'body_html')
	        </div>
	    @endif

	    @if ($field->name == 'body_text')
	        </div>
	        </div>
	    @endif
        @endforeach

	<input type="hidden" id="cancelEdit" value="{{ route('admin.settings.emails.cancel', $query) }}">
	<input type="hidden" id="close" name="_close" value="0">
    </form>
    <x-toolbar :items=$actions />

    @if (isset($email))
	<form id="deleteItem" action="{{ route('admin.settings.emails.destroy', $query) }}" method="post">
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
    <script type="text/javascript" src="{{ asset('/js/admin/disable.toolbars.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/js/tinymce/filemanager.js') }}"></script>
@endpush
