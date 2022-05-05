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
	@include ('layouts.flash-message')

	<div class="card">
	    <div class="card-body">
		<x-filters :filters="$filters" :url="$url" />
	    </div>
	</div>

	<form method="post" action="{{ route('cms.filemanager.index') }}" id="itemForm" enctype="multipart/form-data">
	    @csrf
	    @method('post')
	    <input type="file" name="upload">
	    <input type="submit" value="Upload file">
	</form>

	@if (!empty($rows)) 
	    <table id="item-list" class="table table-hover table-striped">
		<thead class="table-success">
		    @foreach ($columns as $key => $column)
			<th scope="col">
			    @lang ($column->label)
			</th>
		    @endforeach
		    <th scope="col">
		    </th>
		</thead>
		<tbody>
		    @foreach ($rows as $i => $row)
			<tr class="" >
			    @foreach ($columns as $column)
				@if ($column->name == 'file_name')
				    <td>
					<a href="#" onClick="selectFile(this);" data-content-type="{{ $items[$i]->content_type }}" data-file-name="{{ $items[$i]->file_name }}" data-file-url="{{ $items[$i]->url }}">
					{{ $row->{$column->name} }}</a>
				    </td>
				@else
				    <td>{{ $row->{$column->name} }}</td>
				@endif
			    @endforeach
			    <td>
				<a href="#" onClick="deleteDocument(this)" data-document-id="{{ $items[$i]->id }}">
				<i class="nav-icon fas fa-trash"></i></a>
			    </td>
			</tr>
		    @endforeach
		</tbody>

	    </table>
	@else
	    <div class="alert alert-info" role="alert">
		No item has been found.
	    </div>
	@endif

	<x-pagination :items=$items />

	<form id="deleteDocument" action="{{ route('cms.filemanager.index', $query) }}" method="post">
	    @method('delete')
	    @csrf
	    <input type="hidden" id="documentId" name="document_id" value="">
	</form>
    </div>

    <!-- jQuery -->
    <script type="text/javascript" src="{{ asset('/vendor/adminlte/plugins/jquery/jquery.min.js') }}"></script>
    <!-- Bootstrap 4 -->
    <script type="text/javascript" src="{{ asset('/vendor/adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <!-- Select2 Plugin -->
    <script type="text/javascript" src="{{ asset('/vendor/adminlte/plugins/select2/js/select2.min.js') }}"></script>
    <!-- Starter CMS script -->
    <script type="text/javascript" src="{{ asset('/js/admin/list.js') }}"></script>
    </body>
</html>


<script>
function selectFile(element)
{
    var value = {
	content_type: element.dataset.contentType,
	file_name: element.dataset.fileName,
	file_url: element.dataset.fileUrl
    };

    window.parent.postMessage({
        mceAction: 'execCommand',
	cmd: 'iframeCommand',
	value
    }, origin);

    window.parent.postMessage({
        mceAction: 'close'
    });
}

function deleteDocument(element)
{
    if (confirm('Are you sure ?')) {
	document.getElementById('documentId').value = element.dataset.documentId;
	document.getElementById('deleteDocument').submit();
    }
}
</script>
