<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Menu;
use App\Models\User\Group;
use App\Models\Cms\Setting;
use App\Traits\Form;
use App\Traits\CheckInCheckOut;
use App\Http\Requests\Menu\StoreRequest;
use App\Http\Requests\Menu\UpdateRequest;
use Illuminate\Support\Str;
use App\Models\Menu\MenuItem;


class MenuController extends Controller
{
    use Form;

    /*
     * Instance of the Menu model, (used in the Form trait).
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
        $this->middleware('admin.menus');
        $this->item = new Menu;
    }

    /**
     * Show the menu list.
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
        $items = Menu::getMenus($request);
        $rows = $this->getRows($columns, $items);
        $query = $request->query();
        $url = ['route' => 'admin.menus', 'item_name' => 'menu', 'query' => $query];

        return view('admin.menu.list', compact('items', 'columns', 'rows', 'actions', 'filters', 'url', 'query'));
    }

    /**
     * Show the form for creating a new menu.
     *
     * @param  Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function create(Request $request)
    {
        // Gather the needed data to build the form.

        $fields = $this->getFields(['updated_by', 'created_at', 'updated_at', 'owner_name']);
        $actions = $this->getActions('form', ['destroy']);
        $query = $request->query();

        return view('admin.menu.form', compact('fields', 'actions', 'query'));
    }

    /**
     * Show the form for editing the specified menu.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function edit(Request $request, $id)
    {
        $menu = $this->item = Menu::select('menus.*', 'users.name as owner_name', 'users2.name as modifier_name')
                                    ->leftJoin('users', 'menus.owned_by', '=', 'users.id')
                                    ->leftJoin('users as users2', 'menus.updated_by', '=', 'users2.id')
                                    ->findOrFail($id);

        if (!$menu->canAccess()) {
            return redirect()->route('admin.menus.index')->with('error',  __('messages.generic.access_not_auth'));
        }

        if ($menu->checked_out && $menu->checked_out != auth()->user()->id && !$menu->isUserSessionTimedOut()) {
            return redirect()->route('admin.menus.index')->with('error',  __('messages.generic.checked_out'));
        }

        $menu->checkOut();

        // Gather the needed data to build the form.
        
        $except = (auth()->user()->getRoleLevel() > $menu->getOwnerRoleLevel() || $menu->owned_by == auth()->user()->id) ? ['owner_name'] : ['owned_by'];

        $fields = $this->getFields($except);
        $this->setFieldValues($fields, $menu);
        $except = (!$menu->canEdit()) ? ['destroy', 'save', 'saveClose'] : [];
        $actions = $this->getActions('form', $except);
        // Add the id parameter to the query.
        $query = array_merge($request->query(), ['menu' => $id]);

        return view('admin.menu.form', compact('menu', 'fields', 'actions', 'query'));
    }

    /**
     * Checks the record back in.
     *
     * @param  Request  $request
     * @param  \App\Models\Menu  $menu (optional)
     * @return Response
     */
    public function cancel(Request $request, Menu $menu = null)
    {
        if ($menu) {
            $menu->safeCheckIn();
        }

        return redirect()->route('admin.menus.index', $request->query());
    }

    /**
     * Update the specified menu. (AJAX)
     *
     * @param  \App\Http\Requests\Menu\UpdateRequest  $request
     * @param  \App\Models\Menu  $menu
     * @return JSON
     */
    public function update(UpdateRequest $request, Menu $menu)
    {
        if ($menu->checked_out != auth()->user()->id) {
            $request->session()->flash('error', __('messages.generic.user_id_does_not_match'));
            return response()->json(['redirect' => route('admin.menus.index', $request->query())]);
        }

        if (!$menu->canEdit()) {
            $request->session()->flash('error', __('messages.generic.edit_not_auth'));
            return response()->json(['redirect' => route('admin.menus.index', $request->query())]);
        }

        $menu->title = $request->input('title');
        $menu->updated_by = auth()->user()->id;

        if ($menu->canChangeAccessLevel()) {
            $menu->access_level = $request->input('access_level');
        }

        if ($menu->canChangeAttachments()) {
            $menu->owned_by = $request->input('owned_by');

            // N.B: Get also the private groups (if any) that are not returned by the form (as they're not available).
            $groups = array_merge($request->input('groups', []), Group::getPrivateGroups($menu));

            if (!empty($groups)) {
                $menu->groups()->sync($groups);
            }
            else {
                // Remove all groups for this menu.
                $menu->groups()->sync([]);
            }
        }

        if ($menu->canChangeStatus()) {
            $menu->status = $request->input('status');
        }

        $menu->save();

        if ($request->input('_close', null)) {
            $menu->safeCheckIn();
            // Store the message to be displayed on the list view after the redirect.
            $request->session()->flash('success', __('messages.menu.update_success'));
            return response()->json(['redirect' => route('admin.menus.index', $request->query())]);
        }

        $refresh = ['updated_at' => Setting::getFormattedDate($menu->updated_at), 'updated_by' => auth()->user()->name];

        return response()->json(['success' => __('messages.menu.update_success'), 'refresh' => $refresh]);
    }

    /**
     * Store a new menu. (AJAX)
     *
     * @param  \App\Http\Requests\Menu\StoreRequest  $request
     * @return JSON
     */
    public function store(StoreRequest $request)
    {
        $menu = Menu::create([
          'title' => $request->input('title'), 
          'code' => $request->input('code'), 
          'status' => $request->input('status'), 
          'access_level' => $request->input('access_level'), 
          'owned_by' => $request->input('owned_by'),
        ]);

        if ($request->input('groups') !== null) {
            $menu->groups()->attach($request->input('groups'));
        }

        $menu->updated_by = auth()->user()->id;
        $menu->save();

        $request->session()->flash('success', __('messages.menu.create_success'));

        if ($request->input('_close', null)) {
            return response()->json(['redirect' => route('admin.menus.index', $request->query())]);
        }

        // Redirect to the edit form.
        return response()->json(['redirect' => route('admin.menus.edit', array_merge($request->query(), ['menu' => $menu->id]))]);
    }

    /**
     * Remove the specified menu from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Menu  $menu
     * @return Response
     */
    public function destroy(Request $request, Menu $menu)
    {
        // Prevent the main menu to be deleted. 
        if (!$menu->canDelete() || $menu->code == 'main-menu') {
            return redirect()->route('admin.menus.edit', array_merge($request->query(), ['menu' => $menu->id]))->with('error',  __('messages.generic.delete_not_auth'));
        }

        $name = $menu->name;

        $menu->delete();

        return redirect()->route('admin.menus.index', $request->query())->with('success', __('messages.menu.delete_success', ['name' => $name]));
    }

    /**
     * Removes one or more menus from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return Response
     */
    public function massDestroy(Request $request)
    {
        $deleted = 0;
        $messages = [];

        // Remove the menus selected from the list.
        foreach ($request->input('ids') as $id) {
            $menu = Menu::findOrFail($id);

            // Prevent the main menu to be deleted. 
            if (!$menu->canDelete() || $menu->code == 'main-menu') {

                $messages['error'] = __('messages.generic.delete_not_auth'); 

                if ($deleted) {
                    $messages['success'] = __('messages.menu.mass_delete_success', ['number' => $deleted]);
                }

                return redirect()->route('admin.menus.index', $request->query())->with($messages);
            }

            $menu->delete();

            $deleted++;
        }

        return redirect()->route('admin.menus.index', $request->query())->with('success', __('messages.menu.delete_list_success', ['number' => $deleted]));
    }

    /**
     * Checks in one or more menus.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return Response
     */
    public function massCheckIn(Request $request)
    {
        $messages = CheckInCheckOut::checkInMultiple($request->input('ids'), '\\App\\Models\\Menu');

        return redirect()->route('admin.menus.index', $request->query())->with($messages);
    }

    /**
     * Publishes one or more menus.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return Response
     */
    public function massPublish(Request $request)
    {
        $published = 0;

        foreach ($request->input('ids') as $id) {
            $menu = Menu::findOrFail($id);

            if (!$menu->canChangeStatus()) {
              return redirect()->route('admin.menus.index', $request->query())->with(
                  [
                      'error' => __('messages.generic.mass_publish_not_auth'), 
                      'success' => __('messages.menu.publish_list_success', ['number' => $published])
                  ]);
            }

            $menu->status = 'published';

            $menu->save();

            $published++;
        }

        return redirect()->route('admin.menus.index', $request->query())->with('success', __('messages.menu.publish_list_success', ['number' => $published]));
    }

    /**
     * Unpublishes one or more menus.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return Response
     */
    public function massUnpublish(Request $request)
    {
        $unpublished = 0;

        foreach ($request->input('ids') as $id) {
            $menu = Menu::findOrFail($id);

            if (!$menu->canChangeStatus()) {
              return redirect()->route('admin.menus.index', $request->query())->with(
                  [
                      'error' => __('messages.generic.mass_unpublish_not_auth'), 
                      'success' => __('messages.menu.unpublish_list_success', ['number' => $unpublished])
                  ]);
            }

            $menu->status = 'unpublished';

            $menu->save();

            $unpublished++;
        }

        return redirect()->route('admin.menus.index', $request->query())->with('success', __('messages.menu.unpublish_list_success', ['number' => $unpublished]));
    }

    /*
     * Sets field values specific to the Menu model.
     *
     * @param  Array of stdClass Objects  $fields
     * @param  \App\Models\Menu $menu
     * @return void
     */
    private function setFieldValues(&$fields, $menu)
    {
        foreach ($fields as $field) {
            if ($field->name == 'code') {
                $field->extra = ['disabled'];
            }
        }
    }
}
