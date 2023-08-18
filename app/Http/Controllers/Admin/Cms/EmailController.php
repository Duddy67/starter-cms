<?php

namespace App\Http\Controllers\Admin\Cms;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Cms\Email;
use App\Models\User;
use App\Models\Cms\Setting;
use App\Traits\Form;
use App\Traits\CheckInCheckOut;
use App\Http\Requests\Cms\Email\StoreRequest;
use App\Http\Requests\Cms\Email\UpdateRequest;


class EmailController extends Controller
{
    use Form;

    /*
     * Instance of the Email model, (used in the Form trait).
     */
    protected $item = null;


    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin.cms.emails');
	$this->item = new Email;
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
	$items = Email::getEmails($request);
	$rows = $this->getRows($columns, $items);
	$query = $request->query();
	$url = ['route' => 'admin.cms.emails', 'item_name' => 'email', 'query' => $query];
        $message = __('messages.email.test_email_sending', ['email' => auth()->user()->email]);

        return view('admin.cms.email.list', compact('items', 'columns', 'rows', 'actions', 'filters', 'url', 'message', 'query'));
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
        $fields = $this->getFields(['updated_by', 'owner_name']);
        $actions = $this->getActions('form', ['destroy']);
	$query = $request->query();

        return view('admin.cms.email.form', compact('fields', 'actions', 'query'));
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
        $email = $this->item = Email::select('emails.*', 'users.name as modifier_name')
                                      ->leftJoin('users', 'emails.updated_by', '=', 'users.id')
                                      ->findOrFail($id);

	if ($email->checked_out && $email->checked_out != auth()->user()->id && !$email->isUserSessionTimedOut()) {
	    return redirect()->route('admin.cms.emails.index')->with('error',  __('messages.generic.checked_out'));
	}

	$email->checkOut();

        // Gather the needed data to build the form.
	
        $fields = $this->getFields();
	$this->setFieldValues($fields, $email);
	$except = (!auth()->user()->isSuperAdmin()) ? ['destroy'] : [];
        $actions = $this->getActions('form', $except);
	// Add the id parameter to the query.
	$query = array_merge($request->query(), ['email' => $id]);

        return view('admin.cms.email.form', compact('email', 'fields', 'actions', 'query'));
    }

    /**
     * Checks the record back in.
     *
     * @param  Request  $request
     * @param  \App\Models\Cms\Email  $email
     * @return Response
     */
    public function cancel(Request $request, Email $email = null)
    {
        if ($email) {
	    $email->safeCheckIn();
	}

	return redirect()->route('admin.cms.emails.index', $request->query());
    }

    /**
     * Checks in one or more emails.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return Response
     */
    public function massCheckIn(Request $request)
    {
        $messages = CheckInCheckOut::checkInMultiple($request->input('ids'), '\\App\\Models\\Email');

	return redirect()->route('admin.cms.emails.index', $request->query())->with($messages);
    }

    /**
     * Update the specified email. (AJAX)
     *
     * @param  \App\Http\Requests\Cms\Email\UpdateRequest  $request
     * @param  \App\Models\Cms\Email  $email
     * @return JSON
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
            $email->safeCheckIn();
            // Store the message to be displayed on the list view after the redirect.
            $request->session()->flash('success', __('messages.email.update_success'));
            return response()->json(['redirect' => route('admin.cms.emails.index', $request->query())]);
        }

        $this->item = $email;

        return response()->json(['success' => __('messages.email.update_success'), 'refresh' => $this->getFieldsToRefresh($request)]);
    }

    /**
     * Store a new email.
     *
     * @param  \App\Http\Requests\Cms\Email\StoreRequest  $request
     * @return JSON
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

        $email->save();

        $request->session()->flash('success', __('messages.email.create_success'));

        if ($request->input('_close', null)) {
            return response()->json(['redirect' => route('admin.cms.emails.index', $request->query())]);
	}

        // Redirect to the edit form.
        return response()->json(['redirect' => route('admin.cms.emails.edit', array_merge($request->query(), ['email' => $email->id]))]);
    }

    /**
     * Remove the specified email from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Cms\Email  $email
     * @return Response
     */
    public function destroy(Request $request, Email $email)
    {
	$code = $email->code;
	$email->delete();

	return redirect()->route('admin.cms.emails.index', $request->query())->with('success', __('messages.email.delete_success', ['name' => $code]));
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

	return redirect()->route('admin.cms.emails.index', $request->query())->with($messages);
    }

    public function test()
    {
        if (Email::sendTestEmail()) {
            return redirect()->route('admin.cms.emails.index')->with('success', __('messages.email.test_email_sending_ok'));
        }

        return redirect()->route('admin.cms.emails.index')->with('error', __('messages.email.test_email_sending_error'));
    }

    /*
     * Sets field values specific to the Email model.
     *
     * @param  Array of stdClass Objects  $fields
     * @param  \App\Models\Cms\Email  $email
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
