<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Http\Requests\Post\StoreRequest;
use App\Http\Requests\Post\UpdateRequest;
use Illuminate\Support\Str;
use App\Models\Cms\Setting;


class PostController extends Controller
{
    /**
     * The default Post settings.
     *
     * @return array
     */
    protected $settings = [
        'show_image' => 'global_setting',
        'show_owner' => 1,
        'show_excerpt' => 1,
        'show_categories' => 1,
        'show_created_at' => 1
    ];


    public function index(Request $request)
    {
        $query = Post::query()->select('posts.id', 'title', 'slug', 'posts.access_level', 'excerpt', 'content', 'users.name as owner_name')
                              ->join('users', 'posts.owned_by', '=', 'users.id');
        // Join the role tables to get the owner's role level.
        $query->join('model_has_roles', 'posts.owned_by', '=', 'model_id')
              ->join('roles', 'roles.id', '=', 'role_id');

        if (auth('api')->check()) {
            // N.B: Put the following part of the query into brackets.
            $query->where(function($query) {

                // Check for access levels.
                $query->where(function($query) {
                    $query->where('roles.role_level', '<', auth('api')->user()->getRoleLevel())
                          ->orWhereIn('posts.access_level', ['public_ro', 'public_rw'])
                          ->orWhere('posts.owned_by', auth('api')->user()->id);
                });

                $groupIds = auth('api')->user()->getGroupIds();

                if (!empty($groupIds)) {
                    // Check for access through groups.
                    $query->orWhereHas('groups', function ($query)  use ($groupIds) {
                        $query->whereIn('id', $groupIds);
                    });
                }
            });
        }
        else {
            $query->whereIn('posts.access_level', ['public_ro', 'public_rw']);
        }

        $perPage = $request->input('per_page', Setting::getValue('pagination', 'per_page'));

        // Return all of the results or the paginated result according to the $perPage value.
        return ($perPage == -1) ? response()->json($query->paginate($query->count())) : response()->json($query->paginate($perPage));
    }

    public function show($post)
    {
        if (!$post = Post::select('id', 'title', 'slug', 'access_level', 'owned_by', 'excerpt', 'content')->find($post)) {
            return response()->json([
                'message' => __('messages.generic.ressource_not_found')
            ], 404);
        }

        // Check for private posts.
        if (!$post->canAccess('api')) {
            return response()->json([
                'message' => __('messages.generic.access_not_auth')
            ], 403);
        }

        // Work on a cloned object to prevent extra data in the response whenever
        // we access to a relationship: (eg: $post->image, $post->layoutItems).
        $cPost = clone $post;
        $post->image_url = ($cPost->image) ? url('/').$cPost->image->getUrl() : '';
        $post->thumbnail_url = ($cPost->image) ? url('/').$cPost->image->getThumbnailUrl() : '';
        $layout = [];

        // Loop through the layout items if any.
        foreach ($cPost->layoutItems as $layoutItem) {
            $item = new \stdClass;
            $item->type = $layoutItem->type;
            $item->text = $layoutItem->text;
            $item->data = $layoutItem->data;
            $item->order = $layoutItem->order;
            $layout[] = $item;
        }

        $post->layout_items = $layout;

        return response()->json($post);
    }

    public function store(StoreRequest $request)
    {
        Post::create([
            'title' => $request->input('title'), 
            'slug' => ($request->input('slug', null)) ? Str::slug($request->input('slug'), '-') : Str::slug($request->input('title'), '-'),
            'status' => 'unpublished',
            'content' => $request->input('content'), 
            'access_level' => $request->input('access_level'), 
            'owned_by' => auth('api')->user()->id,
            'main_cat_id' => $request->input('main_cat_id', null),
            'settings' => $request->input('settings', $this->settings),
            'excerpt' => $request->input('excerpt', null),
        ]);
        
        return response()->json([
            'message' => __('messages.post.create_success')
        ], 201);
    }

    public function update(UpdateRequest $request, Post $post)
    {
        if (!$post->canEdit('api')) {
            return response()->json([
                'message' => __('messages.generic.edit_not_auth')
            ], 403);
        }

        $post->title = $request->input('title');
        $post->slug = ($request->input('slug')) ? Str::slug($request->input('slug'), '-') : Str::slug($request->input('title'), '-');
        $post->content = $request->input('content');
        $post->excerpt = $request->input('excerpt', null);
        $post->settings = $request->input('settings', $this->settings);
        $post->updated_by = auth('api')->user()->id;

        if ($post->canChangeAccessLevel('api')) {
            $post->access_level = $request->input('access_level');
        }

        $post->save();
        
        return response()->json([
            'message' => __('messages.post.update_success')
        ], 200);
    }

    public function destroy(Post $post)
    {
        if (!$post->canDelete('api')) {
            return response()->json([
                'message' => __('messages.generic.delete_not_auth')
            ], 403);
        }

        $title = $post->title;
        $post->delete();

        return response()->json([
            'message' => __('messages.post.delete_success', ['title' => $title])
        ], 200);
    }
}
