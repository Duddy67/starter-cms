<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>File manager</title>

	<!-- Google Font: Source Sans Pro -->
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha2/dist/css/bootstrap.min.css">
        <link rel="stylesheet" href="{{ asset('/vendor/codalia/css/c.select.css') }}">
	<!-- Custom style -->
	<link rel="stylesheet" href="{{ asset('/css/admin/style.css') }}">
    </head>
    <body>


    <div class="container-fluid">
        <h4>{{ __('labels.generic.batch_title') }}</h4>
	<form method="post" action="{{ route($route.'.massUpdate', $query) }}" id="batchForm" target="_parent">
	    @csrf
	    @method('put')

	    @foreach ($fields as $field)
		@php $value = null; @endphp
		<div class="form-group">
		    <x-input :field="$field" :value="$value" />

		    @if ($field->name == 'groups')
			<div class="form-check-inline mt-4">
			    <label class="form-check-label">
				<input type="radio" class="form-check-input" name="_selected_groups" value="add" checked>{{ __('labels.group.add_selected_groups') }}
			    </label>
			</div>
			<div class="form-check-inline">
			    <label class="form-check-label">
				<input type="radio" class="form-check-input" name="_selected_groups" value="remove">{{ __('labels.group.remove_selected_groups') }}
			    </label>
			</div>
		    @endif
		</div>
	    @endforeach

	    <input type="hidden" id="itemList" value="{{ route($route.'.index', $query) }}">
	</form>

	<div class="form-group batch-actions mt-4">
	    <x-toolbar :items=$actions />
	</div>
    </div>

    <!-- Bootstrap 5 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha2/dist/js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript" src="{{ asset('/vendor/codalia/c.select.js') }}"></script>
    <!-- Starter CMS script -->
    <script type="text/javascript" src="{{ asset('/js/admin/batch.js') }}"></script>
    </body>
</html>

