<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cms\Document;
use App\Traits\Form;

class FileManagerController extends Controller
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
        $columns = $this->getColumns(['owned_by']);
        $filters = $this->getFilters($request, ['owned_by']);
	$items = $this->model->getFileManagerItems($request);
	$rows = $this->getRows($columns, $items);
	$this->setRowValues($rows, $columns, $items);
	$query = $request->query();

	$url = ['route' => 'cms.filemanager', 'item_name' => 'document', 'query' => $query];

        return view('cms.filemanager.list', compact('items', 'columns', 'rows', 'query', 'url', 'filters'));
    }

    public function upload(Request $request)
    {
        if ($request->hasFile('upload') && $request->file('upload')->isValid()) {
	    $document = new Document;
	    $document->upload($request->file('upload'), 'user', 'file_manager');
	    auth()->user()->documents()->save($document);
	}

	return redirect()->route('cms.filemanager.index')->with('success', __('messages.document.create_success'));
    }

    public function destroy(Request $request)
    {
	$document = Document::findOrFail($request->input('document_id', null));

	$name = $document->file_name;
	$document->delete();
	$query = $request->query();

        if (isset($query['page'])) {
	    // Reset pagination to the first page.
	    $query['page'] = 1;
	}

	return redirect()->route('cms.filemanager.index', $query)->with('success', __('messages.document.delete_success', ['name' => $name]));
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
	        /*if ($column->name == 'file_name') {
		    $rows[$key]->file_name = '<a href="'.url('/').$document->getUrl().'" target="_blank">'.$document->file_name.'</a>';
	          }*/

	        if ($column->name == 'preview') {
		    $rows[$key]->preview = view('partials.documents.preview', compact('documents', 'key'));
		}
	    }
	}
    }
}
