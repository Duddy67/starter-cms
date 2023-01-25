<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Post\Setting as PostSetting;
use App\Models\Menu;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Comment\StoreRequest;
use App\Http\Requests\Comment\UpdateRequest;


class PostController extends Controller
{
    public function show(Request $request, string $locale, int $id, string $slug)
    {
        $post = Post::getItem($id, $locale);
        $menu = Menu::getMenu('main-menu');
        $menu->allow_registering = Setting::getValue('website', 'allow_registering', 0);
        $theme = Setting::getValue('website', 'theme', 'starter');

	if (!$post || ($post->layoutItems()->exists() && !view()->exists('themes.'.$theme.'.pages.'.$post->page))) {
            $page = '404';
            return view('themes.'.$theme.'.index', compact('locale', 'page', 'menu'));
	}

	if (!$post->canAccess()) {
            $page = '403';
            return view('themes.'.$theme.'.index', compact('locale', 'page', 'menu'));
	}

        $page = 'post';

        $post->global_settings = PostSetting::getDataByGroup('posts');
	$settings = $post->getSettings();
        $timezone = Setting::getValue('app', 'timezone');
        $metaData = json_decode($post->meta_data, true);
        $segments = Setting::getSegments('Post');
	$query = array_merge($request->query(), ['id' => $id, 'slug' => $slug, 'locale' => $locale]);

        return view('themes.'.$theme.'.index', compact('locale', 'page', 'menu', 'id', 'slug', 'post', 'segments', 'settings', 'timezone', 'metaData', 'query'));
    }

    public function saveComment(StoreRequest $request, $id, $slug)
    {
        $comment = Comment::create([
            'text' => $request->input('comment-0'),
            'owned_by' => Auth::id()
        ]);

        $post = Post::find($id);
        $post->comments()->save($comment);

        $comment->author = auth()->user()->name;
        $theme = Setting::getValue('website', 'theme', 'starter');
        $timezone = Setting::getValue('app', 'timezone');

        return response()->json([
            'id' => $comment->id,
            'action' => 'create',
            'render' => view('themes.'.$theme.'.partials.post.comment', compact('comment', 'timezone'))->render(),
            'text' => $comment->text,
            'message' => __('messages.post.create_comment_success'),
        ]);
    }

    public function updateComment(UpdateRequest $request, Comment $comment)
    {
        // Make sure the user match the comment owner.
        if (auth()->user()->id != $comment->owned_by) {
            return response()->json([
                'errors' => [],
                'commentId' => $comment->id,
                'status' => true,
                'message' => __('messages.post.edit_comment_not_auth')
            ], 422);
        }

        $comment->text = $request->input('comment-'.$comment->id);
        $comment->save();

        return response()->json([
            'id' => $comment->id,
            'action' => 'update',
            'message' => __('messages.post.update_comment_success')
        ]);
    }

    public function deleteComment(Request $request, Comment $comment)
    {
        // Make sure the user match the comment owner.
        if (auth()->user()->id != $comment->owned_by) {
            return response()->json([
                'errors' => [],
                'commentId' => $comment->id,
                'status' => true,
                'message' => __('messages.post.delete_comment_not_auth')
            ], 422);
        }

        $id = $comment->id;
        $comment->delete();

        return response()->json([
            'id' => $id,
            'action' => 'delete',
            'message' => __('messages.post.delete_comment_success')
        ]);
    }
}
