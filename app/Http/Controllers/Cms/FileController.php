<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cms\Document;
use App\Traits\Form;
use App\Models\User;


class FileController extends Controller
{
    use Form;

    /*
     * Instance of the model.
     */
    protected $model;


    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
	$this->model = new Document;
    }

    /**
     * Show the document list.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        $columns = $this->getColumns();
        $actions = $this->getActions('list');
        $filters = $this->getFilters($request);
	$items = $this->model->getAllFileManagerItems($request);
	$rows = $this->getRows($columns, $items, ['preview']);
	$this->setRowValues($rows, $columns, $items);
	$query = $request->query();

	$url = ['route' => 'admin.files', 'item_name' => 'document', 'query' => $query];

        return view('admin.files.list', compact('items', 'columns', 'actions', 'rows', 'query', 'url', 'filters'));
    }

    /**
     * Show the batch form (into an iframe).
     *
     * @param  Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function batch(Request $request)
    {
        $fields = $this->getSpecificFields(['owned_by']);
        $actions = $this->getActions('batch');
	$query = $request->query();
	$route = 'admin.files';

        return view('admin.share.batch', compact('fields', 'actions', 'query', 'route'));
    }

    /**
     * Updates the owned_by parameter of one or more documents.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return Response
     */
    public function massUpdate(Request $request)
    {
        $updates = 0;
	$messages = [];

        foreach ($request->input('ids') as $key => $id) {
	    $document = Document::findOrFail($id);

	    if (!$owner = User::find($document->owned_by)) {
		$messages['error'] = __('messages.users.unknown_user');
		break;
	    }

	    if (auth()->user()->getRoleLevel() < $owner->getRoleLevel()) {
		$messages['error'] = __('messages.generic.edit_not_auth');
		break;
	    }

	    $document->owned_by = $request->input('owned_by');
	    $document->save();

	    $updates++;
	}

	if ($updates) {
	    $messages['success'] = __('messages.generic.mass_update_success', ['number' => $updates]);
	}

	return redirect()->route('admin.files.index')->with($messages);
    }

    /**
     * Removes one or more documents from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return Response
     */
    public function massDestroy(Request $request)
    {
        $deleted = 0;

        // Remove the documents selected from the list.
        foreach ($request->input('ids') as $id) {
	    $document = Document::findOrFail($id);
	    $owner = User::find($document->owned_by);

	    if ($owner && auth()->user()->getRoleLevel() < $owner->getRoleLevel()) {
		return redirect()->route('admin.files.index', $request->query())->with(
		    [
			'error' => __('messages.generic.delete_not_auth'), 
			'success' => __('messages.generic.mass_delete_success', ['number' => $deleted])
		    ]);
	    }

	    $document->delete();

	    $deleted++;
	}

	return redirect()->route('admin.files.index', $request->query())->with('success', __('messages.generic.mass_delete_success', ['number' => $deleted]));
    }

    /*
     * Sets the row values specific to the Document model.
     *
     * @param  Array  $rows
     * @param  Array of stdClass Objects  $columns
     * @param  \Illuminate\Pagination\LengthAwarePaginator  $groups
     * @return void
     */
    private function setRowValues(&$rows, $columns, $documents)
    {
        foreach ($documents as $key => $document) {
	    foreach ($columns as $column) {
	        if ($column->name == 'file_name') {
		    $rows[$key]->file_name = '<a href="'.url('/').$document->getUrl().'" target="_blank">'.$document->file_name.'</a>';
		}

	        if ($column->name == 'preview') {
		    $rows[$key]->preview = view('partials.documents.preview', compact('documents', 'key'));
		}
	    }
	}
    }
}
