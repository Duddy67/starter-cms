@extends ('admin.layouts.default')

@section ('main')
    <form method="post" action="{{ route('admin.cms.settings.update', $query ) }}" id="itemForm">
        @csrf
	@method('patch')

        @foreach ($fields as $field)
	    @php if (isset($data[$field->group][$field->name])) { 
		     $value = old($field->name, $data[$field->group][$field->name]);
		 }
		 else {
		     $value = old($field->name);
		 }
	    @endphp
	    <x-input :field="$field" :value="$value" />
        @endforeach
    </form>

    <div class="form-group">
	<x-toolbar :items=$actions />
    </div>
@endsection

@push ('style')
    <link rel="stylesheet" href="{{ asset('/vendor/adminlte/plugins/jquery-ui/jquery-ui.min.css') }}">
@endpush

@push ('scripts')
    <script type="text/javascript" src="{{ asset('/vendor/adminlte/plugins/jquery-ui/jquery-ui.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/vendor/codalia/c.ajax.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/js/admin/datepicker.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/js/admin/form.js') }}"></script>
@endpush
