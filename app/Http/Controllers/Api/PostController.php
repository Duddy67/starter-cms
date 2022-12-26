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
        // Note: segment(1) is "api"
        $locale = ($request->segment(2)) ? $request->segment(2) : config('app.locale');

        $query = Post::query()->selectRaw('posts.id, users.name as owner_name,'.
                                           Post::getFallbackCoalesce(['title', 'slug', 'excerpt', 'alt_img']))
              ->leftJoin('users', 'posts.owned_by', '=', 'users.id');
        // Join the role tables to get the owner's role level.
        $query->join('model_has_roles', 'posts.owned_by', '=', 'model_id')->join('roles', 'roles.id', '=', 'role_id');

        $query->leftJoin('translations AS locale', function ($join) use($locale) {
            $join->on('posts.id', '=', 'locale.translatable_id')
                 ->where('locale.translatable_type', Post::class)
                 ->where('locale.locale', $locale);
        });
        // Switch to the fallback locale in case locale is not found.
        $query->leftJoin('translations AS fallback', function ($join) {
            $join->on('posts.id', '=', 'fallback.translatable_id')
                 ->where('fallback.translatable_type', Post::class)
                 ->where('fallback.locale', config('app.fallback_locale'));
        })->whereIn('posts.access_level', ['public_ro', 'public_rw']);

        if (auth('api')->user()) {
            $query->orWhere('posts.owned_by', auth('api')->user()->id);
        }

        return response()->json($query->get());
    }

    public function show($locale, $post)
    {
        $post = Post::selectRaw('posts.id, posts.access_level, posts.owned_by,'.
                                 Post::getFallbackCoalesce(['title', 'slug', 'content', 'excerpt',
                                                            'raw_content', 'alt_img', 'extra_fields',
                                                            'meta_data']))
            ->leftJoin('translations AS locale', function ($join) use($locale) {
                $join->on('posts.id', '=', 'locale.translatable_id')
                     ->where('locale.translatable_type', Post::class)
                     ->where('locale.locale', $locale);
            // Switch to the fallback locale in case locale is not found, (used on front-end).
            })->leftJoin('translations AS fallback', function ($join) {
                  $join->on('posts.id', '=', 'fallback.translatable_id')
                       ->where('fallback.translatable_type', Post::class)
                       ->where('fallback.locale', config('app.fallback_locale'));
        })->find($post);

        if (!$post) {
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
