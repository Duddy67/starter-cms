<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Http\Requests\Post\StoreRequest;
use App\Http\Requests\Post\UpdateRequest;
use Illuminate\Support\Str;


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
        $query = Post::query();
        $query->select('id', 'title', 'content')->where('access_level', 'public_ro')->orWhere('access_level', 'public_ro');

        if (auth('api')->user()) {
            $query->orWhere('owned_by', auth('api')->user()->id);
        }

        return response()->json($query->get());
    }

    public function show($post)
    {
        if (!$post = Post::select('id', 'title', 'slug', 'access_level', 'owned_by', 'excerpt', 'content')->find($post)) {
            return response()->json([
                'message' => __('messages.generic.ressource_not_found')
            ], 404);
        }

        // Check for private posts.
        if ($post->access_level == 'private' && (!auth('api')->user() || auth('api')->user()->id != $post->owned_by)) {
            return response()->json([
                'message' => __('messages.generic.access_not_auth')
            ], 403);
        }

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
        if (!$post->canEdit()) {
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

        if ($post->canChangeAccessLevel()) {
            $post->access_level = $request->input('access_level');
        }

        $post->save();
        
        return response()->json([
            'message' => __('messages.post.update_success')
        ], 200);
    }

    public function destroy(Post $post)
    {
        if (!$post->canDelete()) {
            return response()->json([
                'message' => __('messages.generic.delete_not_auth')
            ], 403);
        }

        $name = $post->title;
        $post->delete();

        return response()->json([
            'message' => __('messages.post.delete_success', ['name' => $name])
        ], 200);
    }
}
