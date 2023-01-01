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
        $query->join('model_has_roles', 'posts.owned_by', '=', 'model_id')
              ->join('roles', 'roles.id', '=', 'role_id');

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
                'message' => __('messages.generic.resource_not_found')
            ], 404);
        }

        // Check for private posts.
        if (!$post->canAccess('api')) {
            return response()->json([
                'message' => __('messages.generic.access_not_auth')
            ], 403);
        }

        return response()->json($post);
    }

    public function store(StoreRequest $request)
    {
        $post = Post::create([
            'status' => 'unpublished',
            'access_level' => $request->input('access_level'), 
            'owned_by' => auth('api')->user()->id,
            'main_cat_id' => $request->input('main_cat_id', null),
            'settings' => $request->input('settings', $this->settings),
        ]);

        $post->updated_by = auth('api')->user()->id;
        $post->save();

        $translation = $post->getOrCreateTranslation(config('app.locale'));
        $translation->setAttributes($request, ['title', 'content', 'excerpt', 'alt_img', 'meta_data', 'extra_fields']);
        $translation->slug = ($request->input('slug')) ? Str::slug($request->input('slug'), '-') : Str::slug($request->input('title'), '-');
        $translation->save();
        
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

        $post->settings = $request->input('settings', $this->settings);
        $post->updated_by = auth('api')->user()->id;

        if ($post->canChangeAccessLevel()) {
            $post->access_level = $request->input('access_level');
        }

        $post->save();
        
        $translation = $post->getOrCreateTranslation($request->input('locale'));
        $translation->setAttributes($request, ['title', 'content', 'excerpt', 'alt_img', 'meta_data', 'extra_fields']);
        $translation->slug = ($request->input('slug')) ? Str::slug($request->input('slug'), '-') : Str::slug($request->input('title'), '-');
        $translation->save();

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

        $title = $post->getTranslation(config('app.locale'))->title;
        $post->delete();

        return response()->json([
            'message' => __('messages.post.delete_success', ['title' => $title])
        ], 200);
    }
}
