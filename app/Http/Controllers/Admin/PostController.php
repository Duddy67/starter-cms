<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Post;
use App\Models\User;
use App\Models\Cms\Setting;
use App\Models\User\Group;
use App\Traits\Form;
use App\Traits\CheckInCheckOut;
use App\Http\Requests\Post\StoreRequest;
use App\Http\Requests\Post\UpdateRequest;
use Illuminate\Support\Str;
use App\Models\Cms\Document;
use Carbon\Carbon;
use App\Models\Cms\Order;
use App\Models\Cms\LayoutItem;


class PostController extends Controller
{
    use Form;

    /*
     * Instance of the Post model, (used in the Form trait).
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
        $this->item = new Post;
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

        // Check if posts can be numerically ordered by category.
        $canOrderBy = Setting::canOrderBy('categories', Post::getOrderByExcludedFilters());
        // Make sure the sorting filter is set to order before showing the order column.
        $except = ($canOrderBy && Setting::isSortedByOrder()) ? [] : ['ordering'];
        $columns = $this->getColumns($except);
        $actions = $this->getActions('list');
        $filters = $this->getFilters($request);
        $items = Post::getPosts($request);
        $rows = $this->getRows($columns, $items);
        $query = $request->query();
        $url = ['route' => 'admin.posts', 'item_name' => 'post', 'query' => $query];

        return view('admin.post.list', compact('items', 'columns', 'rows', 'actions', 'filters', 'url', 'canOrderBy', 'query'));
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
        $this->setFieldValues($fields, $this->item);
        $actions = $this->getActions('form', ['destroy']);
        $query = $request->query();

        return view('admin.post.form', compact('fields', 'actions', 'query'));
    }

    /**
     * Show the form for editing the specified post.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function edit(Request $request, int $id)
    {
        $post = $this->item = Post::select('posts.*', 'users.name as owner_name', 'users2.name as modifier_name')
                                    ->leftJoin('users', 'posts.owned_by', '=', 'users.id')
                                    ->leftJoin('users as users2', 'posts.updated_by', '=', 'users2.id')
                                    ->findOrFail($id);
                        
        if (!$post->canAccess()) {
            return redirect()->route('admin.posts.index')->with('error',  __('messages.generic.access_not_auth'));
        }

        if ($post->checked_out && $post->checked_out != auth()->user()->id && !$post->isUserSessionTimedOut()) {
            return redirect()->route('admin.posts.index')->with('error',  __('messages.generic.checked_out'));
        }

        $post->checkOut();

        // Gather the needed data to build the form.
        $except = (auth()->user()->getRoleLevel() > $post->getOwnerRoleLevel() || $post->owned_by == auth()->user()->id) ? ['owner_name'] : ['owned_by'];
        $fields = $this->getFields($except);
        $this->setFieldValues($fields, $post);
        $except = (!$post->canEdit()) ? ['destroy', 'save', 'saveClose'] : [];
        $actions = $this->getActions('form', $except);
        $dateFormat = Setting::getValue('app', 'date_format');
        // Add the id parameter to the query.
        $query = array_merge($request->query(), ['post' => $id]);

        return view('admin.post.form', compact('post', 'fields', 'actions', 'dateFormat', 'query'));
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
            $post->safeCheckIn();
        }

        return redirect()->route('admin.posts.index', $request->query());
    }

    /**
     * Update the specified post. (AJAX)
     *
     * @param  \App\Http\Requests\Post\UpdateRequest  $request
     * @param  \App\Models\Post $post
     * @return JSON 
     */
    public function update(UpdateRequest $request, Post $post)
    {
        if ($post->checked_out != auth()->user()->id) {
            $request->session()->flash('error', __('messages.generic.user_id_does_not_match'));
            return response()->json(['redirect' => route('admin.posts.index', $request->query())]);
        }

        if (!$post->canEdit()) {
            $request->session()->flash('error', __('messages.generic.edit_not_auth'));
            return response()->json(['redirect' => route('admin.posts.index', $request->query())]);
        }

        $post->title = $request->input('title');
        $post->slug = ($request->input('slug')) ? Str::slug($request->input('slug'), '-') : Str::slug($request->input('title'), '-');
        $post->content = $request->input('content');
        $post->excerpt = $request->input('excerpt');
        $post->alt_img = $request->input('alt_img');
        $post->page = $request->input('page');
        $post->meta_data = $request->input('meta_data');
        $post->extra_fields = $request->input('extra_fields');
        $post->settings = $request->input('settings');
        $post->updated_by = auth()->user()->id;
        LayoutItem::storeItems($post);
        // Prioritize layout items over regular content when storing raw content.
        $post->raw_content = ($post->layoutItems()->exists()) ? $post->getLayoutRawContent() : strip_tags($request->input('content'));

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

            // Empty categories when the post has no main category. 
            // INFO: An erratic behaviour (JS ?) adds a category in the array even after
	    // being emptied. 
	    $categories = ($request->input('main_cat_id', null)) ? $request->input('categories', []) : [];

            // N.B: Get also the private categories (if any) that are not returned by the form as they're disabled.
            $categories = array_merge($categories, $post->getPrivateCategories());

            if (!empty($categories)) {
                Order::sync($post, $categories);
                $post->categories()->sync($categories);
            }
            else {
                // Remove all orderings and categories for this post.
                Order::sync($post, []);
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
            // Update the image.
            $post->image = $image;
        }

        if ($request->input('_close', null)) {
            $post->safeCheckIn();
            // Store the message to be displayed on the list view after the redirect.
            $request->session()->flash('success', __('messages.post.update_success'));
            return response()->json(['redirect' => route('admin.posts.index', $request->query())]);
        }

        // Used in the Form trait.
        $this->item = $post;

        return response()->json(['success' => __('messages.post.update_success'), 'updates' => $this->getFieldValuesToUpdate($request)]);
    }

    /**
     * Store a new post. (AJAX)
     *
     * @param  \App\Http\Requests\Post\StoreRequest  $request
     * @return JSON 
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
            'alt_img' => $request->input('alt_img'),
            'page' => $request->input('page'),
            'meta_data' => $request->input('meta_data'),
            'extra_fields' => $request->input('extra_fields'),
            'settings' => $request->input('settings'),
            'excerpt' => $request->input('excerpt'),
        ]);

        LayoutItem::storeItems($post);
        // Prioritize layout items over regular content when storing raw content.
        $post->raw_content = ($post->layoutItems()->exists()) ? $post->getLayoutRawContent() : strip_tags($request->input('content'));

        $post->save();

        if ($request->input('groups') !== null) {
            $post->groups()->attach($request->input('groups'));
        }

        if ($request->input('categories') !== null) {
            $post->categories()->attach($request->input('categories'));
            Order::sync($post, $request->input('categories'));
        }

        if ($image = $this->uploadImage($request)) {
            $post->image()->save($image);
        }

        $request->session()->flash('success', __('messages.post.create_success'));

        if ($request->input('_close', null)) {
            return response()->json(['redirect' => route('admin.posts.index', $request->query())]);
        }

        // Redirect to the edit form.
        return response()->json(['redirect' => route('admin.posts.edit', array_merge($request->query(), ['post' => $post->id]))]);
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
     * Returns all the items linked to the post's layout.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Post $post
     * @return JSON 
     */
    public function layout(Request $request, Post $post)
    {
        $data = [];

        foreach ($post->layoutItems as $item) {
            $data[] = ['id_nb' => $item->id_nb, 'type' => $item->type, 'text' => $item->text, 'data' => $item->data, 'order' => $item->order];
        }

        return response()->json($data);
    }

    /*
     * Delete the image Document linked to the item.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Post $post
     * @return JSON 
     */
    public function deleteImage(Request $request, Post $post)
    {
        if ($post->image) {
            $post->image->delete();
        }
        else {
            return response()->json(['info' => __('messages.generic.no_document_to_delete')]);
        }

        $updates = ['post-image' => asset('/images/camera.png'), 'image' => ''];

        return response()->json(['success' => __('messages.generic.image_deleted'), 'updates' => $updates]);
    }

    /*
     * Delete the given layout item linked to the post.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Post $post
     * @return JSON 
     */
    public function deleteLayoutItem(Request $request, Post $post)
    {
        $idNb = $request->input('id_nb');

        foreach ($post->layoutItems as $item) {
            if ($item->id_nb == $idNb) {
                $item->delete();
                break;
            }
        }

        return response()->json(['success' => __('messages.generic.layout_item_deleted')]);
    }

    public function up(Request $request, Post $post)
    {
        $order = $post->orders->first(function($order) use($request) {
            return $order->category_id == $request->input('categories')[0];
        });

        $order->moveOrderUp();

        return redirect()->route('admin.posts.index', $request->query());
    }

    public function down(Request $request, Post $post)
    {
        $order = $post->orders->first(function($order) use($request) {
            return $order->category_id == $request->input('categories')[0];
        });

        $order->moveOrderDown();

        return redirect()->route('admin.posts.index', $request->query());
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
            $image->upload($request->file('image'), 'image');

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
        $globalSettings = Setting::getDataByGroup('posts', $post);
        foreach ($globalSettings as $key => $value) {
            if (str_starts_with($key, 'alias_extra_field_')) {
                foreach ($fields as $field) {
                    if ($field->name == $key) {
                        $field->value = ($value) ? $value : __('labels.generic.none');
                    }
                }
            }
        }
    }
}
