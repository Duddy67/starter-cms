<!DOCTYPE html>
<html lang="en">
    <head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>File manager</title>

	<!-- Google Font: Source Sans Pro -->
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
	<!-- Font Awesome Icons -->
	<link rel="stylesheet" href="{{ asset('/vendor/adminlte/plugins/fontawesome-free/css/all.min.css') }}">
	<!-- Theme style -->
	<link rel="stylesheet" href="{{ asset('/vendor/adminlte/dist/css/adminlte.min.css') }}">
	<!-- Select2 plugin style -->
	<link rel="stylesheet" href="{{ asset('/vendor/adminlte/plugins/select2/css/select2.min.css') }}"></script>
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
			<div class="form-check-inline">
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

	<div class="form-group batch-actions">
	    <x-toolbar :items=$actions />
	</div>
    </div>


    <!-- jQuery -->
    <script type="text/javascript" src="{{ asset('/vendor/adminlte/plugins/jquery/jquery.min.js') }}"></script>
    <!-- Bootstrap 4 -->
    <script type="text/javascript" src="{{ asset('/vendor/adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <!-- Select2 Plugin -->
    <script type="text/javascript" src="{{ asset('/vendor/adminlte/plugins/select2/js/select2.min.js') }}"></script>
    <!-- Starter CMS script -->
    <script type="text/javascript" src="{{ asset('/js/admin/batch.js') }}"></script>
    </body>
</html>

