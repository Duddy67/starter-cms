<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Post\Comment;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Post\Comment\StoreRequest;
use App\Http\Requests\Post\Comment\UpdateRequest;


class PostController extends Controller
{
    public function show(Request $request, string $locale, int $id, string $slug)
    {
        $post = Post::getItem($id, $locale);
        $page = Setting::getPage('post');

	if (!$post || ($post->layoutItems()->exists() && !view()->exists('themes.'.$page['theme'].'.pages.'.$post->page))) {
            $page['name'] = '404';
            return view('themes.'.$page['theme'].'.index', compact('locale', 'page'));
	}

	if (!$post->canAccess()) {
            $page['name'] = '403';
            return view('themes.'.$page['theme'].'.index', compact('locale', 'page'));
	}

	$post->settings = $post->getSettings();
        $metaData = json_decode($post->meta_data, true);
        $segments = Setting::getSegments('Post');
	$query = array_merge($request->query(), ['id' => $id, 'slug' => $slug, 'locale' => $locale]);

        return view('themes.'.$page['theme'].'.index', compact('locale', 'page', 'id', 'slug', 'post', 'segments', 'metaData', 'query'));
    }

    public function saveComment(StoreRequest $request, string $locale, int $id, string $slug)
    {
        $comment = Comment::create([
            'locale' => $locale,
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

    public function updateComment(UpdateRequest $request, string $locale, Comment $comment)
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

    public function deleteComment(Request $request, string $locale, Comment $comment)
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
