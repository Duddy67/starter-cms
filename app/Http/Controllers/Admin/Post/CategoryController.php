<?php

namespace App\Http\Controllers\Admin\Post;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post\Category;
use App\Models\User\Group;
use App\Models\User;
use App\Traits\Admin\Form;
use App\Traits\Admin\CheckInCheckOut;
use App\Http\Requests\Post\Category\StoreRequest;
use App\Http\Requests\Post\Category\UpdateRequest;
use Illuminate\Support\Str;


class CategoryController extends Controller
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
        $this->middleware('admin.post.categories');
        $this->model = new Category;
    }

    /**
     * Show the category list.
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
        $rows = $this->getRowTree($columns, $items);
        $query = $request->query();
        $url = ['route' => 'admin.post.categories', 'item_name' => 'category', 'query' => $query];

        return view('admin.post.category.list', compact('items', 'columns', 'rows', 'actions', 'filters', 'url', 'query'));
    }

    /**
     * Show the form for creating a new category.
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
        $tab = 'details';

        return view('admin.post.category.form', compact('fields', 'actions', 'tab', 'query'));
    }

    /**
     * Show the form for editing the specified category.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function edit(Request $request, $id, $tab = null)
    {
        $category = $this->item = Category::select('post_categories.*', 'users.name as owner_name')
                                            ->selectRaw('IFNULL(users2.name, ?) as modifier_name', [__('labels.generic.unknown_user')])
                                            ->leftJoin('users', 'post_categories.owned_by', '=', 'users.id')
                                            ->leftJoin('users as users2', 'post_categories.updated_by', '=', 'users2.id')
                                            ->findOrFail($id);

        if (!$category->canAccess()) {
            return redirect()->route('admin.post.categories.index')->with('error',  __('messages.generic.access_not_auth'));
        }

        if ($category->checked_out && $category->checked_out != auth()->user()->id) {
            return redirect()->route('admin.post.categories.index')->with('error',  __('messages.generic.checked_out'));
        }

        $category->checkOut();

        // Gather the needed data to build the form.

        $except = (auth()->user()->getRoleLevel() > $category->getOwnerRoleLevel() || $category->owned_by == auth()->user()->id) ? ['owner_name'] : ['owned_by'];

        if ($category->updated_by === null) {
            array_push($except, 'updated_by', 'updated_at');
        }

        $fields = $this->getFields($except);
        $this->setFieldValues($fields, $category);
        $except = (!$category->canEdit()) ? ['destroy', 'save', 'saveClose'] : [];
        $actions = $this->getActions('form', $except);
        // Add the id parameter to the query.
        $query = array_merge($request->query(), ['category' => $id]);
        $tab = ($tab) ? $tab : 'details';
        // Get the owner of the category in order to check (in the template) if they're still allowed to create categories.
        $owner = User::find($category->owned_by);

        return view('admin.post.category.form', compact('category', 'owner', 'fields', 'actions', 'tab', 'query'));
    }

    /**
     * Checks the record back in.
     *
     * @param  Request  $request
     * @param  \App\Models\Post\Category  $category (optional)
     * @return Response
     */
    public function cancel(Request $request, Category $category = null)
    {
        if ($category) {
            $category->checkIn();
        }

        return redirect()->route('admin.post.categories.index', $request->query());
    }

    /**
     * Update the specified category.
     *
     * @param  \App\Http\Requests\Post\Category\UpdateRequest  $request
     * @param  \App\Models\Post\Category $category
     * @return Response
     */
    public function update(UpdateRequest $request, Category $category)
    {
        if ($category->checked_out != auth()->user()->id) {
            return redirect()->route('admin.post.categories.index', $request->query())->with('error',  __('messages.generic.user_id_does_not_match'));
        }

        if (!$category->canEdit()) {
            return redirect()->route('admin.post.categories.index', $request->query())->with('error',  __('messages.generic.edit_not_auth'));
        }

        if ($request->input('parent_id')) {
            $parent = Category::findOrFail($request->input('parent_id'));

            // Check the selected parent is not the category itself or a descendant.
            if ($category->id == $request->input('parent_id') || $parent->isDescendantOf($category)) {
                return redirect()->route('admin.post.categories.edit', array_merge($request->query(), ['category' => $category->id]))->with('error',  __('messages.generic.must_not_be_descendant'));
            }

            if ($parent->access_level == 'private' && $parent->owned_by != auth()->user()->id) {
                return redirect()->route('admin.post.categories.create', $request->query())->with('error',  __('messages.generic.item_is_private', ['name' => $parent->name]));
            }
        }

        $category->name = $request->input('name');
        $category->slug = ($request->input('slug')) ? Str::slug($request->input('slug'), '-') : Str::slug($request->input('name'), '-');
        $category->description = $request->input('description');
        $category->settings = $request->input('settings');
        $category->updated_by = auth()->user()->id;

        if ($category->canChangeAttachments()) {

            if ($category->access_level != 'private') {
                $category->owned_by = $request->input('owned_by');
            }

            $groups = array_merge($request->input('groups', []), Group::getPrivateGroups($category));

            if (!empty($groups)) {
                $category->groups()->sync($groups);
            }
            else {
                // Remove all groups for this post.
                $category->groups()->sync([]);
            }
        }

        if ($category->canChangeAccessLevel()) {

            if ($category->access_level != 'private') {
                // The access level has just been set to private. Check first for descendants.
                if ($request->input('access_level') == 'private' && !$category->canDescendantsBePrivate()) {
                    return redirect()->route('admin.post.categories.edit', array_merge($request->query(), ['category' => $category->id]))->with('error',  __('messages.generic.descendants_cannot_be_private'));
                }

                if ($request->input('access_level') == 'private' && $category->anyDescendantCheckedOut()) {
                    return redirect()->route('admin.post.categories.edit', array_merge($request->query(), ['category' => $category->id]))->with('error',  __('messages.generic.descendants_checked_out'));
                }

                if ($request->input('access_level') == 'private') {
                    $category->setDescendantAccessToPrivate();
                }
            }

            if ($category->access_level != 'private' || ($category->access_level == 'private' && !$category->isParentPrivate())) {
                $category->access_level = $request->input('access_level');
                // N.B: The nested set model is updated automatically.
                $category->parent_id = $request->input('parent_id');
            }

            if ($category->access_level == 'private' && $category->isParentPrivate() && $category->owned_by == auth()->user()->id) {
                // Only the owner of the descendants private items can change their parents.
                $category->parent_id = $request->input('parent_id');
            }

            // The status has just been set to unpublished.
            if ($category->status != 'unpublished' && $request->input('status') == 'unpublished') {
                // All the descendants must be unpublished as well.
                foreach ($category->descendants as $descendant) {
                    $descendant->status = 'unpublished';
                    $descendant->save();
                }
            }

            $category->status = $request->input('status');
        }

        $category->save();

        if ($request->input('_close', null)) {
            $category->checkIn();
            // Redirect to the list.
            return redirect()->route('admin.post.categories.index', $request->query())->with('success', __('messages.category.update_success'));
        }

        return redirect()->route('admin.post.categories.edit', array_merge($request->query(), ['category' => $category->id, 'tab' => $request->input('_tab')]))->with('success', __('messages.category.update_success'));
    }

    /**
     * Store a new category.
     *
     * @param  \App\Http\Requests\Post\Category\StoreRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        // Check first for parent id.
        if ($request->input('parent_id')) {
            $parent = Category::findOrFail($request->input('parent_id'));

            if ($parent->access_level == 'private' && $parent->owned_by != auth()->user()->id) {
                return redirect()->route('admin.post.categories.create', $request->query())->with('error',  __('messages.generic.item_is_private', ['name' => $parent->name]));
            }

            if ($parent->access_level == 'private' && $request->input('access_level') != 'private') {
                return redirect()->route('admin.post.categories.create', $request->query())->with('error',  __('messages.generic.access_level_must_be_private'));
            }

            if ($parent->access_level == 'private' && $request->input('owned_by') != $parent->owned_by) {
                return redirect()->route('admin.post.categories.create', $request->query())->with('error',  __('messages.generic.owner_must_match_parent_category'));
            }
        }

        $category = Category::create([
            'name' => $request->input('name'), 
            'slug' => ($request->input('slug')) ? Str::slug($request->input('slug'), '-') : Str::slug($request->input('name'), '-'),
            'status' => $request->input('status'), 
            'description' => $request->input('description'), 
            'access_level' => $request->input('access_level'), 
            'owned_by' => $request->input('owned_by'),
            'parent_id' => (empty($request->input('parent_id'))) ? null : $request->input('parent_id'),
            'settings' => $request->input('settings'),
        ]);

        if ($category->parent_id) {
            $parent = Category::findOrFail($category->parent_id);
            $parent->appendNode($category);
        }

        if ($request->input('_close', null)) {
            return redirect()->route('admin.post.categories.index', $request->query())->with('success', __('messages.category.create_success'));
        }

        return redirect()->route('admin.post.categories.edit', array_merge($request->query(), ['category' => $category->id]))->with('success', __('messages.category.create_success'));
    }

    /**
     * Remove the specified category from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Post\Category $category
     * @return Response
     */
    public function destroy(Request $request, Category $category)
    {
        if (!$category->canDelete() || !$category->canDeleteDescendants()) {
            return redirect()->route('admin.post.categories.edit', array_merge($request->query(), ['category' => $category->id]))->with('error',  __('messages.generic.delete_not_auth'));
        }

        $name = $category->name;

        //$category->categories()->detach();
        //$category->delete();

        return redirect()->route('admin.post.categories.index', $request->query())->with('success', __('messages.category.delete_success', ['name' => $name]));
    }

    /**
     * Removes one or more categories from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return Response
     */
    public function massDestroy(Request $request)
    {
        $deleted = 0;
        // Remove the categories selected from the list.
        foreach ($request->input('ids') as $id) {
            $category = Category::findOrFail($id);

            if (!$category->canDelete() || !$category->canDeleteDescendants()) {
              return redirect()->route('admin.post.categories.index', $request->query())->with(
                  [
                      'error' => __('messages.generic.delete_not_auth'), 
                      'success' => __('messages.category.delete_list_success', ['number' => $deleted])
                  ]);
            }

            //$category->categories()->detach();
            $category->delete();

            $deleted++;
        }

        return redirect()->route('admin.post.categories.index', $request->query())->with('success', __('messages.category.delete_list_success', ['number' => $deleted]));
    }

    /**
     * Checks in one or more categories.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return Response
     */
    public function massCheckIn(Request $request)
    {
        $messages = CheckInCheckOut::checkInMultiple($request->input('ids'), '\\App\\Models\\Post\\Category');

        return redirect()->route('admin.post.categories.index', $request->query())->with($messages);
    }

    public function massPublish(Request $request)
    {
        $changed = 0;

        foreach ($request->input('ids') as $id) {
            $category = Category::findOrFail($id);
            // Cannot published a category if its parent is unpublished.
            if ($category->parent && $category->parent->status == 'unpublished') {
                continue;
            }

            if (!$category->canChangeStatus()) {
              $messages = ['error' => __('messages.generic.change_status_not_auth')];

              if ($changed) {
                  $messages['success'] = __('messages.category.change_status_list_success', ['number' => $changed]);
              }

              return redirect()->route('admin.post.categories.index', $request->query())->with($messages);
            }

            $category->status = 'published';
            $category->save();

            $changed++;
        }

        return redirect()->route('admin.post.categories.index', $request->query())->with('success', __('messages.category.change_status_list_success', ['number' => $changed]));
    }

    public function massUnpublish(Request $request)
    {
        $treated = [];
        $changed = 0;

        foreach ($request->input('ids') as $id) {
            //
            if (in_array($id, $treated)) {
                continue;
            }

            $category = Category::findOrFail($id);

            if (!$category->canChangeStatus()) {
              $messages = ['error' => __('messages.generic.change_status_not_auth')];

              if ($changed) {
                  $messages['success'] = __('messages.category.change_status_list_success', ['number' => $changed]);
              }

              return redirect()->route('admin.post.categories.index', $request->query())->with($messages);
            }

            $category->status = 'unpublished';
            $category->save();

            $changed++;

            // All the descendants must be unpublished as well.
            foreach ($category->descendants as $descendant) {
                $descendant->status = 'unpublished';
                $descendant->save();
                // Prevent this descendant to be treated twice.
                $treated[] = $descendant->id;
            }
        }

        return redirect()->route('admin.post.categories.index', $request->query())->with('success', __('messages.category.change_status_list_success', ['number' => $changed]));
    }

    /**
     * Reorders a given category a level above.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Post\Category $category
     * @return Response
     */
    public function up(Request $request, Category $category)
    {
        $category->up();
        return redirect()->route('admin.post.categories.index', $request->query());
    }

    /**
     * Reorders a given category a level below.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Post\Category $category
     * @return Response
     */
    public function down(Request $request, Category $category)
    {
        $category->down();
        return redirect()->route('admin.post.categories.index', $request->query());
    }

    /*
     * Sets field values specific to the Category model.
     *
     * @param  Array of stdClass Objects  $fields
     * @param  \App\Models\Post\Category $category
     * @return void
     */
    private function setFieldValues(&$fields, $category)
    {
        foreach ($fields as $field) {
            if ($field->name == 'parent_id') {
                foreach ($field->options as $key => $option) {
                    if ($option['value'] == $category->id) {
                        // Category cannot be its own children.
                        $field->options[$key]['extra'] = ['disabled'];
                    }
                }
            }

            if (isset($field->group) && $field->group == 'settings') {
                $field->value = (isset($category->settings[$field->name])) ? $category->settings[$field->name] : null;
            }
        }
    }
}
