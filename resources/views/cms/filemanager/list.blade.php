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
	@include ('admin.partials.flash-message')

	<div class="card">
	    <div class="card-body">
		<x-filters :filters="$filters" :url="$url" />
	    </div>
	</div>

	<form method="post" action="{{ route('cms.filemanager.index') }}" id="itemForm" enctype="multipart/form-data">
	    @csrf
	    @method('post')
	    <input type="file" id="upload" name="upload">
	    <input type="submit" value="Upload file">

            @foreach ($errors->all() as $error)
                <div class="text-danger" id="uploadError">{{ $error }}</div>
            @endforeach
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

    <!-- Bootstrap 5 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery (required for some scripts) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script type="text/javascript" src="{{ asset('/vendor/codalia/c.select.js') }}"></script>
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
