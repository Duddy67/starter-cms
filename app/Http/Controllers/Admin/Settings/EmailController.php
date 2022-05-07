<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Settings\Email;
use App\Models\User;
use App\Traits\Admin\ItemConfig;
use App\Traits\Admin\CheckInCheckOut;
use App\Http\Requests\Settings\Email\StoreRequest;
use App\Http\Requests\Settings\Email\UpdateRequest;


class EmailController extends Controller
{
    use ItemConfig;

    /*
     * Instance of the model.
     */
    protected $model;

    /*
     * Name of the model.
     */
    protected $modelName = 'email';

    /*
     * Name of the plugin.
     */
    protected $pluginName = 'settings';


    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin.settings.emails');
	$this->model = new Email;
    }

    /**
     * Show the email list.
     *
     * @param  Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        // Gather the needed data to build the item list.
        $columns = $this->getColumns();
        $actions = $this->getActions('list');
        $filters = $this->getFilters($request);
	$items = $this->model->getItems($request);
	$rows = $this->getRows($columns, $items);
	$query = $request->query();
	$url = ['route' => 'admin.settings.emails', 'item_name' => 'email', 'query' => $query];

        return view('admin.settings.emails.list', compact('items', 'columns', 'rows', 'actions', 'filters', 'url', 'query'));
    }

    /**
     * Show the form for creating a new email.
     *
     * @param  Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function create(Request $request)
    {
        // Gather the needed data to build the form.
        $fields = $this->getFields(null, ['updated_by', 'owner_name']);
        $actions = $this->getActions('form', ['destroy']);
	$query = $request->query();

        return view('admin.settings.emails.form', compact('fields', 'actions', 'query'));
    }

    /**
     * Show the form for editing the specified group.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function edit(Request $request, $id)
    {
        // Gather the needed data to build the form.
        $email = Email::select('emails.*', 'users.name as modifier_name')
			->leftJoin('users', 'emails.updated_by', '=', 'users.id')
			->findOrFail($id);

	if ($email->checked_out && $email->checked_out != auth()->user()->id) {
	    return redirect()->route('admin.settings.emails.index')->with('error',  __('messages.generic.checked_out'));
	}

	$email->checkOut();

        // Gather the needed data to build the form.
	
	$except = ($email->updated_by === null) ? ['updated_by', 'updated_at'] : [];

        $fields = $this->getFields($email, $except);
	$this->setFieldValues($fields, $email);
	$except = (!auth()->user()->isSuperAdmin()) ? ['destroy'] : [];
        $actions = $this->getActions('form', $except);
	// Add the id parameter to the query.
	$query = array_merge($request->query(), ['email' => $id]);

        return view('admin.settings.emails.form', compact('email', 'fields', 'actions', 'query'));
    }

    /**
     * Checks the record back in.
     *
     * @param  Request  $request
     * @param  \App\Models\Settings\Email  $email
     * @return Response
     */
    public function cancel(Request $request, Email $email = null)
    {
        if ($email) {
	    $email->checkIn();
	}

	return redirect()->route('admin.settings.emails.index', $request->query());
    }

    /**
     * Checks in one or more emails.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return Response
     */
    public function massCheckIn(Request $request)
    {
        $messages = CheckInCheckOut::checkInMultiple($request->input('ids'), '\\App\\Models\\Settings\\Email');

	return redirect()->route('admin.settings.emails.index', $request->query())->with($messages);
    }

    /**
     * Update the specified email.
     *
     * @param  \App\Http\Requests\Settings\Email\UpdateRequest  $request
     * @param  \App\Models\Settings\Email  $email
     * @return Response
     */
    public function update(UpdateRequest $request, Email $email)
    {
	$email->subject = $request->input('subject');
	$email->body_html = $request->input('body_html');
	$email->body_text = $request->input('body_text');
	$email->updated_by = auth()->user()->id;

	if (auth()->user()->isSuperAdmin()) {
	    $email->code = $request->input('code');
	    $email->plain_text = ($request->input('format') == 'plain_text') ? 1 : 0;
	}

	$email->save();

        if ($request->input('_close', null)) {
	    $email->checkIn();
	    return redirect()->route('admin.settings.emails.index', $request->query())->with('success', __('messages.email.update_success'));
	}

	return redirect()->route('admin.settings.emails.edit', array_merge($request->query(), ['email' => $email->id]))->with('success', __('messages.email.update_success'));
    }

    /**
     * Store a new email.
     *
     * @param  \App\Http\Requests\Settings\Email\StoreRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
	$plainText = ($request->input('format') == 'plain_text') ? 1 : 0;

	$email = Email::create(['code' => $request->input('code'),
				'subject' => $request->input('subject'),
				'body_html' => $request->input('body_html'),
				'body_text' => $request->input('body_text'),
				'plain_text' => $plainText,
	]);

        if ($request->input('_close', null)) {
	    return redirect()->route('admin.settings.emails.index', $request->query())->with('success', __('messages.email.create_success'));
	}

	return redirect()->route('admin.settings.emails.edit', array_merge($request->query(), ['email' => $email->id]))->with('success', __('messages.email.create_success'));
    }

    /**
     * Remove the specified email from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Settings\Email  $email
     * @return Response
     */
    public function destroy(Request $request, Email $email)
    {
	$code = $email->code;
	$email->delete();

	return redirect()->route('admin.settings.emails.index', $request->query())->with('success', __('messages.email.delete_success', ['name' => $code]));
    }

    /**
     * Remove one or more emails from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return Response
     */
    public function massDestroy(Request $request)
    {
        $messages = [];
	$deleted = 0;

        foreach ($request->input('ids') as $id) {
	    $email = Email::findOrFail($id);
	    $email->delete();
	    $deleted++;
	}

	if ($deleted) {
	    $messages['success'] = __('messages.generic.mass_delete_success', ['number' => $deleted]);
	}

	return redirect()->route('admin.settings.emails.index', $request->query())->with($messages);
    }

    /*
     * Sets field values specific to the Email model.
     *
     * @param  Array of stdClass Objects  $fields
     * @param  \App\Models\Settings\Email  $email
     * @return void
     */
    private function setFieldValues(&$fields, $email)
    {
        $restricted = (auth()->user()->isSuperAdmin()) ? false : true;

        foreach ($fields as $field) {
	    if ($restricted && $field->name != 'subject' && $field->name != 'body_html' && $field->name != 'body_text') {
		$field = $this->setExtraAttributes($field, ['disabled']);
	    }
	}
    }
}
