<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\User;
use App\Models\Post\Comment;
use App\Models\Cms\Setting;
use App\Models\Cms\Email;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Post\Comment\StoreRequest;
use App\Http\Requests\Post\Comment\UpdateRequest;


class PostController extends Controller
{
    public function show(Request $request, $id, $slug)
    {
        $post = Post::select('posts.*', 'users.name as owner_name', 'users2.name as modifier_name')
			->leftJoin('users', 'posts.owned_by', '=', 'users.id')
			->leftJoin('users as users2', 'posts.updated_by', '=', 'users2.id')
			->where('posts.id', $id)->first();

        $page = Setting::getPage('post');

        if (!$post || ($post->layoutItems()->exists() && !view()->exists('themes.'.$page['theme'].'.pages.'.$post->page))) {
            $page['name'] = '404';
            return view('themes.'.$page['theme'].'.index', compact('page'));
	}

	if (!$post->canAccess()) {
            $page['name'] = '403';
            return view('themes.'.$page['theme'].'.index', compact('page'));
	}

	$post->settings = $post->getSettings();
        $metaData = $post->meta_data;
        $segments = Setting::getSegments('Post');
	$query = array_merge($request->query(), ['id' => $id, 'slug' => $slug]);

        return view('themes.'.$page['theme'].'.index', compact('page', 'id', 'slug', 'post', 'segments', 'metaData', 'query'));
    }

    public function saveComment(StoreRequest $request, $id, $slug)
    {
        $comment = Comment::create([
            'text' => $request->input('comment-0'), 
            'owned_by' => Auth::id()
        ]);

        $post = Post::find($id);
        $post->comments()->save($comment);

        // Set variables used in the render.
        $comment->author = auth()->user()->name;
        $theme = Setting::getValue('website', 'theme', 'starter');
        $page = Setting::getPage('post');
        $count = $post->comments()->count();
        $key = $count - 1;

        if ($post->settings['comment_alert'] && auth()->user()->id != $post->owned_by) {
            $author = User::find($post->owned_by);
            $post->recipient = $author->email;
            $post->post_author = $author->name;
            $post->comment_author = auth()->user()->name;
            $post->post_url = url('/').$post->getUrl();
            Email::sendEmail('comment-alert', $post);
        }

        return response()->json([
            'id' => $comment->id, 
            'action' => 'create', 
            'render' => view('themes.'.$theme.'.partials.post.comment', compact('comment', 'page', 'count', 'key'))->render(),
            'text' => $comment->text,
            'count' => $count,
            'key' => $key,
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
