@extends ('admin.layouts.default')

@section ('main')
    <h3>@php echo __('labels.title.settings'); @endphp</h3>

    @include('admin.partials.x-toolbar')

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
@endsection

@push ('scripts')
    <script type="text/javascript" src="{{ asset('/vendor/codalia/c.ajax.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/js/admin/form.js') }}"></script>
@endpush
