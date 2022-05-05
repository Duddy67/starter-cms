<?php

namespace App\Http\Controllers\Admin\Menu;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Menu\MenuItem;
use App\Models\Menu\Menu;
use App\Models\User\Group;
use App\Traits\Admin\ItemConfig;
use App\Traits\Admin\CheckInCheckOut;
use App\Http\Requests\Menu\MenuItems\StoreRequest;
use App\Http\Requests\Menu\MenuItems\UpdateRequest;
use Illuminate\Support\Str;

class MenuItemController extends Controller
{
    use ItemConfig;

    /*
     * Instance of the model.
     */
    protected $model;

    /*
     * Name of the model.
     */
    protected $modelName = 'menuitem';

    /*
     * Name of the plugin.
     */
    protected $pluginName = 'menu';

    /*
     * The parent menu.
     */
    protected $menu;


    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->middleware('auth');
        $this->middleware('admin.menu.menuitems');
        $this->model = new MenuItem;
        // Rely on the parent menu for authorisations (NB: A valid menu code is checked in advance in the middleware).
        $this->menu = ($request->route()) ? Menu::where('code', $request->route()->parameter('code'))->first() : null; 
    }

    /**
     * Show the menu item list.
     *
     * @param  Request  $request
     * @param  string   $code
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request, $code)
    {
        // Gather the needed data to build the item list.
        $columns = $this->getColumns();
        $except = (!$this->menu->canEdit()) ? ['create', 'publish', 'unpublish'] : [];

        if (!$this->menu->canDelete()) {
            $except[] = 'massDestroy';
        }

        $actions = $this->getActions('list', $except);
        $filters = $this->getFilters($request);
        $items = $this->model->getItems($request, $code);
        $rows = $this->getRowTree($columns, $items);
        $query = $request->query();
        $query['code'] = $code;

        $url = ['route' => 'admin.menu.menuitems', 'item_name' => 'menuItem', 'query' => $query];

        return view('admin.menu.menuitems.list', compact('items', 'columns', 'rows', 'actions', 'filters', 'url', 'query'));
    }

    /**
     * Show the form for creating a new item menu.
     *
     * @param  Request  $request
     * @param  string   $code
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function create(Request $request, $code)
    {
        // Gather the needed data to build the form.

        $fields = $this->getFields(null, ['updated_by', 'created_at', 'updated_at', 'owner_name']);
        $actions = $this->getActions('form', ['destroy']);
        $query = array_merge($request->query(), ['code' => $code]);

        return view('admin.menu.menuitems.form', compact('fields', 'actions', 'query'));
    }

    /**
     * Show the form for editing the specified menu item.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function edit(Request $request, $code, $id)
    {
        $menuItem = MenuItem::select('menu_items.*')
                              ->selectRaw('IFNULL(users.name, ?) as modifier_name', [__('labels.generic.unknown_user')])
                              ->leftJoin('users as users', 'menu_items.updated_by', '=', 'users.id')
                              ->findOrFail($id);

        if ($menuItem->checked_out && $menuItem->checked_out != auth()->user()->id) {
            return redirect()->route('admin.menu.menuitems.index', array_merge($request->query(), ['code' => $code]))->with('error',  __('messages.generic.checked_out'));
        }

        $menuItem->checkOut();

        // Gather the needed data to build the form.
        
        $except = [];

        if ($menuItem->updated_by === null) {
            array_push($except, 'updated_by', 'updated_at');
        }

        $fields = $this->getFields($menuItem, $except);
        $this->setFieldValues($fields, $menuItem);
        $except = (!$this->menu->canEdit()) ? ['destroy', 'save', 'saveClose'] : [];

        if (!$this->menu->canDelete()) {
            $except[] = 'destroy';
        }

        $actions = $this->getActions('form', $except);
        // Add the id parameter to the query.
        $query = array_merge($request->query(), ['code' => $code, 'menuItem' => $id]);

        return view('admin.menu.menuitems.form', compact('menuItem', 'fields', 'actions', 'query'));
    }

    /**
     * Checks the record back in.
     *
     * @param  Request  $request
     * @param  \App\Models\Menu\MenuItem $menuItem (optional)
     * @return Response
     */
    public function cancel(Request $request, $code, MenuItem $menuItem = null)
    {
        if ($menuItem) {
            $menuItem->checkIn();
        }

        return redirect()->route('admin.menu.menuitems.index', array_merge($request->query(), ['code' => $code]));
    }

    /**
     * Update the specified menu item.
     *
     * @param  \App\Http\Requests\Menu\MenuItem\UpdateRequest  $request
     * @param  string  $code
     * @param  \App\Models\Menu\MenuItem  $menuItem
     * @return Response
     */
    public function update(UpdateRequest $request, $code, MenuItem $menuItem)
    {
        if ($menuItem->checked_out != auth()->user()->id) {
            return redirect()->route('admin.menu.menuitems.index', array_merge($request->query(), ['code' => $code]))->with('error',  __('messages.generic.user_id_does_not_match'));
        }

        if (!$this->menu->canEdit()) {
            return redirect()->route('admin.menu.menuitems.index', array_merge($request->query(), ['code' => $code]))->with('error',  __('messages.generic.edit_not_auth'));
        }

        $query = array_merge($request->query(), ['code' => $code, 'menuItem' => $menuItem->id]);

        $parent = MenuItem::findOrFail($request->input('parent_id'));

        // Check the selected parent is not the menu item itself or a descendant.
        if ($menuItem->id == $request->input('parent_id') || $parent->isDescendantOf($menuItem)) {
            return redirect()->route('admin.menu.menuitems.edit', $query)->with('error',  __('messages.generic.must_not_be_descendant'));
        }

        $menuItem->title = $request->input('title');
        $menuItem->url = $request->input('url');
        $menuItem->updated_by = auth()->user()->id;

        $menuItem->save();

        if ($request->input('_close', null)) {
            $menuItem->checkIn();
            // Redirect to the list.
            return redirect()->route('admin.menu.menuitems.index', array_merge($request->query(), ['code' => $code]))->with('success', __('messages.menuitems.update_success'));
        }

        return redirect()->route('admin.menu.menuitems.edit', $query)->with('success', __('messages.menuitems.update_success'));
    }

    /**
     * Store a new menu item.
     *
     * @param  \App\Http\Requests\Menu\MenuItem\StoreRequest  $request
     * @param  string  $code
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request, $code)
    {
        // The user cannot create an item if he cannot edit it.
        if (!$this->menu->canEdit()) {
            return redirect()->route('admin.menu.menuitems.index', array_merge($request->query(), ['code' => $code]))->with('error',  __('messages.generic.create_not_auth'));
        }

        // Check first for parent id. (N.B: menu items cannot be null as they have a root parent id by default).
        $parent = MenuItem::findOrFail($request->input('parent_id'));

        $menuItem = MenuItem::create([
            'title' => $request->input('title'), 
            'url' => $request->input('url'), 
            'status' => ($parent->status == 'unpublished') ? 'unpublished' : $request->input('status'), 
            'parent_id' => $request->input('parent_id'),
        ]);

        $parent->appendNode($menuItem);
        $menuItem->menu_code = $code;

        $menuItem->save();

        if ($request->input('_close', null)) {
            return redirect()->route('admin.menu.menuitems.index', array_merge($request->query(), ['code' => $code]))->with('success', __('messages.menuitems.create_success'));
        }

        return redirect()->route('admin.menu.menuitems.edit', array_merge($request->query(), ['code' => $code, 'menuItem' => $menuItem->id]))->with('success', __('messages.menuitems.create_success'));
    }

    /**
     * Remove the specified menu item from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $code
     * @param  \App\Models\Menu\MenuItem $menuItem
     * @return Response
     */
    public function destroy(Request $request, $code, MenuItem $menuItem)
    {
        if (!$this->menu->canDelete()) {
            return redirect()->route('admin.menu.menuitems.index', array_merge($request->query(), ['code' => $code]))->with('error',  __('messages.generic.delete_not_auth'));
        }

        $name = $menuItem->name;

        $menuItem->delete();

        return redirect()->route('admin.menu.menuitems.index', array_merge($request->query(), ['code' => $code]))->with('success', __('messages.menuitems.delete_success', ['name' => $name]));
    }

    /**
     * Removes one or more menu items from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $code
     * @return Response
     */
    public function massDestroy(Request $request, $code)
    {
        if (!$this->menu->canDelete()) {
            return redirect()->route('admin.menu.menuitems.index', array_merge($request->query(), ['code' => $code]))->with('error',  __('messages.generic.delete_not_auth'));
        }

        $deleted = 0;
        // Remove the menu items selected from the list.
        foreach ($request->input('ids') as $id) {
            $menuItem = MenuItem::findOrFail($id);

            $menuItem->delete();
            $deleted++;
        }

        return redirect()->route('admin.menu.menuitems.index', array_merge($request->query(), ['code' => $code]))->with('success', __('messages.menuitems.delete_list_success', ['number' => $deleted]));
    }

    /**
     * Checks in one or more menu items.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $code
     * @return Response
     */
    public function massCheckIn(Request $request, $code)
    {
        $messages = CheckInCheckOut::checkInMultiple($request->input('ids'), '\\App\\Models\\Menu\\MenuItem');

        return redirect()->route('admin.menu.menuitems.index', array_merge($request->query(), ['code' => $code]))->with($messages);
    }

    public function massPublish(Request $request, $code)
    {
        if (!$this->menu->canChangeStatus()) {
            return redirect()->route('admin.menu.menuitems.index', array_merge($request->query(), ['code' => $code]))->with('error',  __('messages.generic.change_status_not_auth'));
        }

        $changed = 0;

        foreach ($request->input('ids') as $id) {
            $menuItem = MenuItem::findOrFail($id);
            // Cannot published a menu item if its parent is unpublished.
            if ($menuItem->parent && $menuItem->parent->status == 'unpublished') {
                continue;
            }

            $menuItem->status = 'published';
            $menuItem->save();

            $changed++;
        }

        return redirect()->route('admin.menu.menuitems.index', array_merge($request->query(), ['code' => $code]))->with('success', __('messages.menuitems.change_status_list_success', ['number' => $changed]));
    }

    public function massUnpublish(Request $request, $code)
    {
        if (!$this->menu->canChangeStatus()) {
            return redirect()->route('admin.menu.menuitems.index', array_merge($request->query(), ['code' => $code]))->with('error',  __('messages.generic.change_status_not_auth'));
        }

        $treated = [];
        $changed = 0;

        foreach ($request->input('ids') as $id) {
            //
            if (in_array($id, $treated)) {
                continue;
            }

            $menuItem = MenuItem::findOrFail($id);

            $menuItem->status = 'unpublished';
            $menuItem->save();

            $changed++;

            // All the descendants must be unpublished as well.
            foreach ($menuItem->descendants as $descendant) {
                $descendant->status = 'unpublished';
                $descendant->save();
                // Prevent this descendant to be treated twice.
                $treated[] = $descendant->id;
            }
        }

        return redirect()->route('admin.menu.menuitems.index', array_merge($request->query(), ['code' => $code]))->with('success', __('messages.menuitems.change_status_list_success', ['number' => $changed]));
    }

    /**
     * Reorders a given menu item a level above.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Menu\MenuItem $menuItem
     * @return Response
     */
    public function up(Request $request, $code, MenuItem $menuItem)
    {
        if (!$this->menu->canChangeStatus()) {
            return redirect()->route('admin.menu.menuitems.index', array_merge($request->query(), ['code' => $code]))->with('error',  __('messages.generic.change_order_not_auth'));
        }

        $menuItem->up();
        return redirect()->route('admin.menu.menuitems.index', array_merge($request->query(), ['code' => $code]));
    }

    /**
     * Reorders a given menu item a level below.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Menu\MenuItem $menuItem
     * @return Response
     */
    public function down(Request $request, $code, MenuItem $menuItem)
    {
        if (!$this->menu->canChangeStatus()) {
            return redirect()->route('admin.menu.menuitems.index', array_merge($request->query(), ['code' => $code]))->with('error',  __('messages.generic.change_order_not_auth'));
        }

        $menuItem->down();
        return redirect()->route('admin.menu.menuitems.index', array_merge($request->query(), ['code' => $code]));
    }

    /*
     * Sets field values specific to the MenuItem model.
     *
     * @param  Array of stdClass Objects  $fields
     * @param  \App\Models\Menu\MenuItem $menuItem
     * @return void
     */
    private function setFieldValues(&$fields, $menuItem)
    {
        foreach ($fields as $field) {
            if ($field->name == 'parent_id') {
                foreach ($field->options as $key => $option) {
                    if ($option['value'] == $menuItem->id) {
                        // Menu item cannot be its own children.
                        $field->options[$key]['extra'] = ['disabled'];
                    }
                }
            }
        }
    }
}
