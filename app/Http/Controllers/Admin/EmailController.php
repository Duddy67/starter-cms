<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Cms\Email;
use App\Models\User;
use App\Models\Cms\Setting;
use App\Traits\Form;
use App\Traits\CheckInCheckOut;
use App\Http\Requests\Email\StoreRequest;
use App\Http\Requests\Email\UpdateRequest;


class EmailController extends Controller
{
    use Form;

    /*
     * Instance of the model.
     */
    protected $model;

    /*
     * The item to edit in the form.
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
        $this->middleware('admin.emails');
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
        $this->setRowValues($rows, $columns, $items);
	$query = $request->query();
	$url = ['route' => 'admin.emails', 'item_name' => 'email', 'query' => $query];
        $message = __('messages.email.test_email_sending', ['email' => auth()->user()->email]);

        return view('admin.email.list', compact('items', 'columns', 'rows', 'actions', 'filters', 'url', 'message', 'query'));
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
        $locale = config('app.locale');

        return view('admin.email.form', compact('fields', 'actions', 'locale', 'query'));
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
        $locale = ($request->query('locale', null)) ? $request->query('locale') : config('app.locale');
        $email = $this->item = Email::getItem($id, $locale);

	if ($email->checked_out && $email->checked_out != auth()->user()->id && !$email->isUserSessionTimedOut()) {
	    return redirect()->route('admin.emails.index')->with('error',  __('messages.generic.checked_out'));
	}

	$email->checkOut();

        // Gather the needed data to build the form.
	
        $fields = $this->getFields();
	$this->setFieldValues($fields, $email);
	$except = (!auth()->user()->isSuperAdmin()) ? ['destroy'] : [];
        $actions = $this->getActions('form', $except);
	// Add the id parameter to the query.
	$query = array_merge($request->query(), ['email' => $id]);

        return view('admin.email.form', compact('email', 'fields', 'actions', 'locale', 'query'));
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

        return redirect()->route('admin.emails.index', \Arr::except($request->query(), ['locale']));
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

	return redirect()->route('admin.emails.index', $request->query())->with($messages);
    }

    /**
     * Update the specified email. (AJAX)
     *
     * @param  \App\Http\Requests\Email\UpdateRequest  $request
     * @param  \App\Models\Cms\Email  $email
     * @return JSON
     */
    public function update(UpdateRequest $request, Email $email)
    {
	$email->updated_by = auth()->user()->id;

	if (auth()->user()->isSuperAdmin()) {
	    $email->code = $request->input('code');
	    $email->plain_text = ($request->input('format') == 'plain_text') ? 1 : 0;
	}

	$email->save();

        $translation = $email->getOrCreateTranslation($request->input('locale'));
        $translation->setAttributes($request, ['subject', 'body_text']);
        // Replace the HTML entities set by the editor in the code placeholders (eg: {{ $data-&gt;name }}).
	$translation->body_html = preg_replace('#({{[\s\$a-zA-Z0-9_]+)-&gt;([a-zA-Z0-9_\s]+}})#', '$1->$2', $request->input('body_html'));
        $translation->save();

        $email->setViewFiles($request->input('locale'));

        if ($request->input('_close', null)) {
            $email->safeCheckIn();
            // Store the message to be displayed on the list view after the redirect.
            $request->session()->flash('success', __('messages.email.update_success'));
            return response()->json(['redirect' => route('admin.emails.index', $request->query())]);
        }

        $refresh = ['updated_at' => Setting::getFormattedDate($email->updated_at), 'updated_by' => auth()->user()->name];

        return response()->json(['success' => __('messages.email.update_success'), 'refresh' => $refresh]);
    }

    /**
     * Store a new email.
     *
     * @param  \App\Http\Requests\Email\StoreRequest  $request
     * @return JSON
     */
    public function store(StoreRequest $request)
    {
	$plainText = ($request->input('format') == 'plain_text') ? 1 : 0;

	$email = Email::create(['code' => $request->input('code'),
				'plain_text' => $plainText,
	]);

	$email->updated_by = auth()->user()->id;
        $email->save();

        // Store the very first translation as the default locale.
        $translation = $email->getOrCreateTranslation(config('app.locale'));
        $translation->setAttributes($request, ['subject', 'body_text']);
        // Replace the HTML entities set by the editor in the code placeholders (eg: {{ $data-&gt;name }}).
	$translation->body_html = preg_replace('#({{[\s\$a-zA-Z0-9_]+)-&gt;([a-zA-Z0-9_\s]+}})#', '$1->$2', $request->input('body_html'));
        $translation->save();

        $email->setViewFiles(config('app.locale'));

        $request->session()->flash('success', __('messages.email.create_success'));

        if ($request->input('_close', null)) {
            return response()->json(['redirect' => route('admin.emails.index', $request->query())]);
	}

        // Redirect to the edit form.
        return response()->json(['redirect' => route('admin.emails.edit', array_merge($request->query(), ['email' => $email->id]))]);
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
        $code = $email->getTranslation(config('app.locale'))->code;
	$email->delete();

	return redirect()->route('admin.emails.index', $request->query())->with('success', __('messages.email.delete_success', ['name' => $code]));
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

	return redirect()->route('admin.emails.index', $request->query())->with($messages);
    }

    public function test()
    {
        if (Email::sendTestEmail()) {
            return redirect()->route('admin.emails.index')->with('success', __('messages.email.test_email_sending_ok'));
        }

        return redirect()->route('admin.emails.index')->with('error', __('messages.email.test_email_sending_error'));
    }

    /*
     * Sets the row values specific to the Post model.
     *
     * @param  Array  $rows
     * @param  Array of stdClass Objects  $columns
     * @param  \Illuminate\Pagination\LengthAwarePaginator  $groups
     * @return void
     */
    private function setRowValues(&$rows, $columns, $items)
    {
        foreach ($items as $key => $item) {
            foreach ($columns as $column) {
                if ($column->name == 'locales') {
                    $locales = '';

                    foreach ($item->translations as $translation) {
                        $locales .= $translation->locale.', ';
                    }

                    $locales = substr($locales, 0, -2);

                    $rows[$key]->locales = $locales;
                }
            }
        }
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
