<?php

namespace App\Http\Controllers\Admin\Menu;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Menu\Item;
use App\Models\Menu;
use App\Models\User\Group;
use App\Traits\Admin\Form;
use App\Traits\Admin\CheckInCheckOut;
use App\Http\Requests\Menu\Item\StoreRequest;
use App\Http\Requests\Menu\Item\UpdateRequest;
use Illuminate\Support\Str;

class ItemController extends Controller
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
        $this->middleware('admin.menu.items');
        $this->model = new Item;
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

        $url = ['route' => 'admin.menu.items', 'item_name' => 'item', 'query' => $query];

        return view('admin.menu.item.list', compact('items', 'columns', 'rows', 'actions', 'filters', 'url', 'query'));
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

        $fields = $this->getFields(['updated_by', 'created_at', 'updated_at', 'owner_name']);
        $actions = $this->getActions('form', ['destroy']);
        $query = array_merge($request->query(), ['code' => $code]);

        return view('admin.menu.item.form', compact('fields', 'actions', 'query'));
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
        $item = $this->item = Item::select('menu_items.*')
                                    ->selectRaw('IFNULL(users.name, ?) as modifier_name', [__('labels.generic.unknown_user')])
                                    ->leftJoin('users as users', 'menu_items.updated_by', '=', 'users.id')
                                    ->findOrFail($id);

        if ($item->checked_out && $item->checked_out != auth()->user()->id) {
            return redirect()->route('admin.menu.items.index', array_merge($request->query(), ['code' => $code]))->with('error',  __('messages.generic.checked_out'));
        }

        $item->checkOut();

        // Gather the needed data to build the form.
        
        $except = [];

        if ($item->updated_by === null) {
            array_push($except, 'updated_by', 'updated_at');
        }

        $fields = $this->getFields($except);
        $this->setFieldValues($fields, $item);
        $except = (!$this->menu->canEdit()) ? ['destroy', 'save', 'saveClose'] : [];

        if (!$this->menu->canDelete()) {
            $except[] = 'destroy';
        }

        $actions = $this->getActions('form', $except);
        // Add the id parameter to the query.
        $query = array_merge($request->query(), ['code' => $code, 'item' => $id]);

        return view('admin.menu.item.form', compact('item', 'fields', 'actions', 'query'));
    }

    /**
     * Checks the record back in.
     *
     * @param  Request  $request
     * @param  \App\Models\Menu\Item $item (optional)
     * @return Response
     */
    public function cancel(Request $request, $code, Item $item = null)
    {
        if ($item) {
            $item->checkIn();
        }

        return redirect()->route('admin.menu.items.index', array_merge($request->query(), ['code' => $code]));
    }

    /**
     * Update the specified menu item.
     *
     * @param  \App\Http\Requests\Menu\Item\UpdateRequest  $request
     * @param  string  $code
     * @param  \App\Models\Menu\Item  $item
     * @return Response
     */
    public function update(UpdateRequest $request, $code, Item $item)
    {
        if ($item->checked_out != auth()->user()->id) {
            return redirect()->route('admin.menu.items.index', array_merge($request->query(), ['code' => $code]))->with('error',  __('messages.generic.user_id_does_not_match'));
        }

        if (!$this->menu->canEdit()) {
            return redirect()->route('admin.menu.items.index', array_merge($request->query(), ['code' => $code]))->with('error',  __('messages.generic.edit_not_auth'));
        }

        $query = array_merge($request->query(), ['code' => $code, 'item' => $item->id]);

        $parent = Item::findOrFail($request->input('parent_id'));

        // Check the selected parent is not the menu item itself or a descendant.
        if ($item->id == $request->input('parent_id') || $parent->isDescendantOf($item)) {
            return redirect()->route('admin.menu.items.edit', $query)->with('error',  __('messages.generic.must_not_be_descendant'));
        }

        $item->title = $request->input('title');
        $item->url = $request->input('url');
        $item->updated_by = auth()->user()->id;

        $item->save();

        if ($request->input('_close', null)) {
            $item->checkIn();
            // Redirect to the list.
            return redirect()->route('admin.menu.items.index', array_merge($request->query(), ['code' => $code]))->with('success', __('messages.menuitems.update_success'));
        }

        return redirect()->route('admin.menu.items.edit', $query)->with('success', __('messages.menuitems.update_success'));
    }

    /**
     * Store a new menu item.
     *
     * @param  \App\Http\Requests\Menu\Item\StoreRequest  $request
     * @param  string  $code
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request, $code)
    {
        // The user cannot create an item if he cannot edit it.
        if (!$this->menu->canEdit()) {
            return redirect()->route('admin.menu.items.index', array_merge($request->query(), ['code' => $code]))->with('error',  __('messages.generic.create_not_auth'));
        }

        // Check first for parent id. (N.B: menu items cannot be null as they have a root parent id by default).
        $parent = Item::findOrFail($request->input('parent_id'));

        $item = Item::create([
            'title' => $request->input('title'), 
            'url' => $request->input('url'), 
            'status' => ($parent->status == 'unpublished') ? 'unpublished' : $request->input('status'), 
            'parent_id' => $request->input('parent_id'),
        ]);

        $parent->appendNode($item);
        $item->menu_code = $code;

        $item->save();

        if ($request->input('_close', null)) {
            return redirect()->route('admin.menu.items.index', array_merge($request->query(), ['code' => $code]))->with('success', __('messages.menuitems.create_success'));
        }

        return redirect()->route('admin.menu.items.edit', array_merge($request->query(), ['code' => $code, 'item' => $item->id]))->with('success', __('messages.menuitems.create_success'));
    }

    /**
     * Remove the specified menu item from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $code
     * @param  \App\Models\Menu\Item $item
     * @return Response
     */
    public function destroy(Request $request, $code, Item $item)
    {
        if (!$this->menu->canDelete()) {
            return redirect()->route('admin.menu.items.index', array_merge($request->query(), ['code' => $code]))->with('error',  __('messages.generic.delete_not_auth'));
        }

        $name = $item->name;

        $item->delete();

        return redirect()->route('admin.menu.items.index', array_merge($request->query(), ['code' => $code]))->with('success', __('messages.menuitems.delete_success', ['name' => $name]));
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
            return redirect()->route('admin.menu.items.index', array_merge($request->query(), ['code' => $code]))->with('error',  __('messages.generic.delete_not_auth'));
        }

        $deleted = 0;
        // Remove the menu items selected from the list.
        foreach ($request->input('ids') as $id) {
            $item = Item::findOrFail($id);

            $item->delete();
            $deleted++;
        }

        return redirect()->route('admin.menu.items.index', array_merge($request->query(), ['code' => $code]))->with('success', __('messages.menuitems.delete_list_success', ['number' => $deleted]));
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
        $messages = CheckInCheckOut::checkInMultiple($request->input('ids'), '\\App\\Models\\Menu\\Item');

        return redirect()->route('admin.menu.items.index', array_merge($request->query(), ['code' => $code]))->with($messages);
    }

    public function massPublish(Request $request, $code)
    {
        if (!$this->menu->canChangeStatus()) {
            return redirect()->route('admin.menu.items.index', array_merge($request->query(), ['code' => $code]))->with('error',  __('messages.generic.change_status_not_auth'));
        }

        $changed = 0;

        foreach ($request->input('ids') as $id) {
            $item = Item::findOrFail($id);
            // Cannot published a menu item if its parent is unpublished.
            if ($item->parent && $item->parent->status == 'unpublished') {
                continue;
            }

            $item->status = 'published';
            $item->save();

            $changed++;
        }

        return redirect()->route('admin.menu.items.index', array_merge($request->query(), ['code' => $code]))->with('success', __('messages.menuitems.change_status_list_success', ['number' => $changed]));
    }

    public function massUnpublish(Request $request, $code)
    {
        if (!$this->menu->canChangeStatus()) {
            return redirect()->route('admin.menu.items.index', array_merge($request->query(), ['code' => $code]))->with('error',  __('messages.generic.change_status_not_auth'));
        }

        $treated = [];
        $changed = 0;

        foreach ($request->input('ids') as $id) {
            //
            if (in_array($id, $treated)) {
                continue;
            }

            $item = Item::findOrFail($id);

            $item->status = 'unpublished';
            $item->save();

            $changed++;

            // All the descendants must be unpublished as well.
            foreach ($item->descendants as $descendant) {
                $descendant->status = 'unpublished';
                $descendant->save();
                // Prevent this descendant to be treated twice.
                $treated[] = $descendant->id;
            }
        }

        return redirect()->route('admin.menu.items.index', array_merge($request->query(), ['code' => $code]))->with('success', __('messages.menuitems.change_status_list_success', ['number' => $changed]));
    }

    /**
     * Reorders a given menu item a level above.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Menu\Item $item
     * @return Response
     */
    public function up(Request $request, $code, Item $item)
    {
        if (!$this->menu->canChangeStatus()) {
            return redirect()->route('admin.menu.items.index', array_merge($request->query(), ['code' => $code]))->with('error',  __('messages.generic.change_order_not_auth'));
        }

        $item->up();
        return redirect()->route('admin.menu.items.index', array_merge($request->query(), ['code' => $code]));
    }

    /**
     * Reorders a given menu item a level below.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Menu\Item $item
     * @return Response
     */
    public function down(Request $request, $code, Item $item)
    {
        if (!$this->menu->canChangeStatus()) {
            return redirect()->route('admin.menu.items.index', array_merge($request->query(), ['code' => $code]))->with('error',  __('messages.generic.change_order_not_auth'));
        }

        $item->down();
        return redirect()->route('admin.menu.items.index', array_merge($request->query(), ['code' => $code]));
    }

    /*
     * Sets field values specific to the Item model.
     *
     * @param  Array of stdClass Objects  $fields
     * @param  \App\Models\Menu\Item $item
     * @return void
     */
    private function setFieldValues(&$fields, $item)
    {
        foreach ($fields as $field) {
            if ($field->name == 'parent_id') {
                foreach ($field->options as $key => $option) {
                    if ($option['value'] == $item->id) {
                        // Menu item cannot be its own children.
                        $field->options[$key]['extra'] = ['disabled'];
                    }
                }
            }
        }
    }
}
