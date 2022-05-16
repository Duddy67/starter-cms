<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Post;
use App\Models\User;
use App\Models\User\Group;
use App\Traits\Form;
use App\Traits\CheckInCheckOut;
use App\Http\Requests\Post\StoreRequest;
use App\Http\Requests\Post\UpdateRequest;
use Illuminate\Support\Str;
use App\Models\Cms\Document;
use Carbon\Carbon;


class PostController extends Controller
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
        $this->middleware('admin.posts');
        $this->model = new Post;
    }

    /**
     * Show the post list.
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
        $url = ['route' => 'admin.posts', 'item_name' => 'post', 'query' => $query];

        return view('admin.post.list', compact('items', 'columns', 'rows', 'actions', 'filters', 'url', 'query'));
    }

    /**
     * Show the form for creating a new post.
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

        return view('admin.post.form', compact('fields', 'actions', 'tab',  'query'));
    }

    /**
     * Show the form for editing the specified post.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function edit(Request $request, int $id, string $tab = null)
    {
        $post = $this->item = Post::select('posts.*', 'users.name as owner_name')
                                    ->selectRaw('IFNULL(users2.name, ?) as modifier_name', [__('labels.generic.unknown_user')])
                                    ->leftJoin('users', 'posts.owned_by', '=', 'users.id')
                                    ->leftJoin('users as users2', 'posts.updated_by', '=', 'users2.id')
                                    ->findOrFail($id);
                        
        if (!$post->canAccess()) {
            return redirect()->route('admin.posts.index')->with('error',  __('messages.generic.access_not_auth'));
        }

        if ($post->checked_out && $post->checked_out != auth()->user()->id) {
            return redirect()->route('admin.posts.index')->with('error',  __('messages.generic.checked_out'));
        }

        $post->checkOut();

        // Gather the needed data to build the form.
        
        $except = (auth()->user()->getRoleLevel() > $post->getOwnerRoleLevel() || $post->owned_by == auth()->user()->id) ? ['owner_name'] : ['owned_by'];

        if ($post->updated_by === null) {
            array_push($except, 'updated_by', 'updated_at');
        }

        $fields = $this->getFields($except);
        $this->setFieldValues($fields, $post);
        $except = (!$post->canEdit()) ? ['destroy', 'save', 'saveClose'] : [];
        $actions = $this->getActions('form', $except);
        // Add the id parameter to the query.
        $query = array_merge($request->query(), ['post' => $id]);
        $tab = ($tab) ? $tab : 'details';

        return view('admin.post.form', compact('post', 'fields', 'actions', 'tab', 'query'));
    }

    /**
     * Checks the record back in.
     *
     * @param  Request  $request
     * @param  \App\Models\Post  $post (optional)
     * @return Response
     */
    public function cancel(Request $request, Post $post = null)
    {
        if ($post) {
            $post->checkIn();
        }

        return redirect()->route('admin.posts.index', $request->query());
    }

    /**
     * Update the specified post.
     *
     * @param  \App\Http\Requests\Post\UpdateRequest  $request
     * @param  \App\Models\Post $post
     * @return Response
     */
    public function update(UpdateRequest $request, Post $post)
    {
        if ($post->checked_out != auth()->user()->id) {
            return redirect()->route('admin.posts.index', $request->query())->with('error',  __('messages.generic.user_id_does_not_match'));
        }

        if (!$post->canEdit()) {
            return redirect()->route('admin.posts.edit', array_merge($request->query(), ['post' => $post->id]))->with('error',  __('messages.generic.edit_not_auth'));
        }

        $post->title = $request->input('title');
        $post->slug = ($request->input('slug')) ? Str::slug($request->input('slug'), '-') : Str::slug($request->input('title'), '-');
        $post->content = $request->input('content');
        $post->excerpt = $request->input('excerpt');
        $post->settings = $request->input('settings');
        $post->updated_by = auth()->user()->id;

        if ($post->canChangeAccessLevel()) {
            $post->access_level = $request->input('access_level');

            // N.B: Get also the private groups (if any) that are not returned by the form as they're disabled.
            $groups = array_merge($request->input('groups', []), Group::getPrivateGroups($post));

            if (!empty($groups)) {
                $post->groups()->sync($groups);
            }
            else {
                // Remove all groups for this post.
                $post->groups()->sync([]);
            }
        }

        if ($post->canChangeAttachments()) {

            $post->owned_by = $request->input('owned_by');

            // N.B: Get also the private categories (if any) that are not returned by the form as they're disabled.
            $categories = array_merge($request->input('categories', []), $post->getPrivateCategories());

            if (!empty($categories)) {
                $post->categories()->sync($categories);
            }
            else {
                // Remove all categories for this post.
                $post->categories()->sync([]);
            }

            $post->main_cat_id = $request->input('main_cat_id');
        }

        if ($post->canChangeStatus()) {
            $post->status = $request->input('status');
        }

        $post->save();

        if ($image = $this->uploadImage($request)) {
            // Delete the previous post image if any.
            if ($post->image) {
                $post->image->delete();
            }

            $post->image()->save($image);
        }

        if ($request->input('_close', null)) {
            $post->checkIn();
            // Redirect to the list.
            return redirect()->route('admin.posts.index', $request->query())->with('success', __('messages.post.update_success'));
        }

        return redirect()->route('admin.posts.edit', array_merge($request->query(), ['post' => $post->id, 'tab' => $request->input('_tab')]))->with('success', __('messages.post.update_success'));
    }

    /**
     * Store a new post.
     *
     * @param  \App\Http\Requests\Post\StoreRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        $post = Post::create([
          'title' => $request->input('title'), 
          'slug' => ($request->input('slug')) ? Str::slug($request->input('slug'), '-') : Str::slug($request->input('title'), '-'),
          'status' => $request->input('status'), 
          'content' => $request->input('content'), 
          'access_level' => $request->input('access_level'), 
          'owned_by' => $request->input('owned_by'),
          'main_cat_id' => $request->input('main_cat_id'),
          'settings' => $request->input('settings'),
          'excerpt' => $request->input('excerpt'),
        ]);

        if ($request->input('groups') !== null) {
            $post->groups()->attach($request->input('groups'));
        }

        if ($request->input('categories') !== null) {
            $post->categories()->attach($request->input('categories'));
        }

        if ($image = $this->uploadImage($request)) {
            $post->image()->save($image);
        }

        if ($request->input('_close', null)) {
            return redirect()->route('admin.posts.index', $request->query())->with('success', __('messages.post.create_success'));
        }

        return redirect()->route('admin.posts.edit', array_merge($request->query(), ['post' => $post->id]))->with('success', __('messages.post.create_success'));
    }

    /**
     * Remove the specified post from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Post $post
     * @return Response
     */
    public function destroy(Request $request, Post $post)
    {
        if (!$post->canDelete()) {
            return redirect()->route('admin.posts.edit', array_merge($request->query(), ['post' => $post->id]))->with('error',  __('messages.generic.delete_not_auth'));
        }

        $name = $post->name;
        $post->delete();

        return redirect()->route('admin.posts.index', $request->query())->with('success', __('messages.post.delete_success', ['name' => $name]));
    }

    /**
     * Removes one or more posts from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return Response
     */
    public function massDestroy(Request $request)
    {
        $deleted = 0;

        // Remove the posts selected from the list.
        foreach ($request->input('ids') as $id) {
            $post = Post::findOrFail($id);

            if (!$post->canDelete()) {
              return redirect()->route('admin.posts.index', $request->query())->with(
                  [
                      'error' => __('messages.generic.delete_not_auth'), 
                      'success' => __('messages.post.delete_list_success', ['number' => $deleted])
                  ]);
            }

            $post->delete();

            $deleted++;
        }

        return redirect()->route('admin.posts.index', $request->query())->with('success', __('messages.post.delete_list_success', ['number' => $deleted]));
    }

    /**
     * Checks in one or more posts.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return Response
     */
    public function massCheckIn(Request $request)
    {
        $messages = CheckInCheckOut::checkInMultiple($request->input('ids'), '\\App\\Models\\Post');

        return redirect()->route('admin.posts.index', $request->query())->with($messages);
    }

    /**
     * Show the batch form (into an iframe).
     *
     * @param  Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function batch(Request $request)
    {
        $fields = $this->getSpecificFields(['access_level', 'owned_by', 'groups']);
        $actions = $this->getActions('batch');
        $query = $request->query();
        $route = 'admin.posts';

        return view('admin.share.batch', compact('fields', 'actions', 'query', 'route'));
    }

    /**
     * Updates the access_level and owned_by parameters of one or more posts.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return Response
     */
    public function massUpdate(Request $request)
    {
        $updates = 0;
        $messages = [];

        foreach ($request->input('ids') as $key => $id) {
            $post = Post::findOrFail($id);
            $updated = false;

            // Check for authorisation.
            if (!$post->canEdit()) {
                $messages['error'] = __('messages.generic.mass_update_not_auth');
                continue;
            }

            if ($request->input('owned_by') !== null && $post->canChangeAttachments()) {
                $post->owned_by = $request->input('owned_by');
                $updated = true;
            }

            if ($request->input('access_level') !== null && $post->canChangeAccessLevel()) {
                $post->access_level = $request->input('access_level');
                $updated = true;
            }

            if ($request->input('groups') !== null && $post->canChangeAccessLevel()) {
                if ($request->input('_selected_groups') == 'add') {
                    $post->groups()->syncWithoutDetaching($request->input('groups'));
                }
                else {
                    // Remove the selected groups from the current groups and get the remaining groups.
                    $groups = array_diff($post->getGroupIds(), $request->input('groups'));
                    $post->groups()->sync($groups);
                }

                $updated = true;
            }

            if ($updated) {
                $post->save();
                $updates++;
            }
        }

        if ($updates) {
            $messages['success'] = __('messages.generic.mass_update_success', ['number' => $updates]);
        }

        return redirect()->route('admin.posts.index')->with($messages);
    }

    /**
     * Publishes one or more posts.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return Response
     */
    public function massPublish(Request $request)
    {
        $published = 0;

        foreach ($request->input('ids') as $id) {
            $post = Post::findOrFail($id);

            if (!$post->canChangeStatus()) {
              return redirect()->route('admin.posts.index', $request->query())->with(
                  [
                      'error' => __('messages.generic.mass_publish_not_auth'), 
                      'success' => __('messages.post.publish_list_success', ['number' => $published])
                  ]);
            }

            $post->status = 'published';

            $post->save();

            $published++;
        }

        return redirect()->route('admin.posts.index', $request->query())->with('success', __('messages.post.publish_list_success', ['number' => $published]));
    }

    /**
     * Unpublishes one or more posts.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return Response
     */
    public function massUnpublish(Request $request)
    {
        $unpublished = 0;

        foreach ($request->input('ids') as $id) {
            $post = Post::findOrFail($id);

            if (!$post->canChangeStatus()) {
              return redirect()->route('admin.posts.index', $request->query())->with(
                  [
                      'error' => __('messages.generic.mass_unpublish_not_auth'), 
                      'success' => __('messages.post.unpublish_list_success', ['number' => $unpublished])
                  ]);
            }

            $post->status = 'unpublished';

            $post->save();

            $unpublished++;
        }

        return redirect()->route('admin.posts.index', $request->query())->with('success', __('messages.post.unpublish_list_success', ['number' => $unpublished]));
    }

    /*
     * Creates a Document associated with the uploaded image file.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \App\Models\Cms\Document
     */
    private function uploadImage(Request $request)
    {
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $image = new Document;
            $image->upload($request->file('image'), 'post', 'image');

            return $image;
        }

        return null;
    }

    /*
     * Sets field values specific to the Post model.
     *
     * @param  Array of stdClass Objects  $fields
     * @param  \App\Models\Post $post
     * @return void
     */
    private function setFieldValues(array &$fields, Post $post)
    {
        foreach ($fields as $field) {
            if (isset($field->group) && $field->group == 'settings') {
                $field->value = (isset($post->settings[$field->name])) ? $post->settings[$field->name] : null;
            }
        }
    }
}
