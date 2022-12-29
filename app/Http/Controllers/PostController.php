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


class PostController extends Controller
{
    public function show(Request $request, $id, $slug)
    {
        $post = Post::select('posts.*', 'users.name as owner_name', 'users2.name as modifier_name')
			->leftJoin('users', 'posts.owned_by', '=', 'users.id')
			->leftJoin('users as users2', 'posts.updated_by', '=', 'users2.id')
			->where('posts.id', $id)->first();

        $menu = Menu::getMenu('main-menu');
        $menu->allow_registering = Setting::getValue('website', 'allow_registering', 0);
        $theme = Setting::getValue('website', 'theme', 'starter');

        if (!$post || ($post->layoutItems()->exists() && !view()->exists('themes.'.$theme.'.pages.'.$post->page))) {
            $page = '404';
            return view('themes.'.$theme.'.index', compact('page', 'menu'));
	}

	if (!$post->canAccess()) {
            $page = '403';
            return view('themes.'.$theme.'.index', compact('page', 'menu'));
	}

        $page = 'post';

        $post->global_settings = PostSetting::getDataByGroup('posts');
	$settings = $post->getSettings();
        $timezone = Setting::getValue('app', 'timezone');
        $metaData = $post->meta_data;
        $segments = Setting::getSegments('Post');
	$query = array_merge($request->query(), ['id' => $id, 'slug' => $slug]);

        return view('themes.'.$theme.'.index', compact('page', 'menu', 'id', 'slug', 'post', 'segments', 'settings', 'timezone', 'metaData', 'query'));
    }

    public function saveComment(Request $request, $id, $slug)
    {
        $comment = Comment::create([
            'text' => $request->input('comment'), 
            'owned_by' => Auth::id()
        ]);

        $post = Post::find($id);
        $post->comments()->save($comment);

        $request->session()->flash('success', __('messages.post.comment_success'));

        //file_put_contents('debog_file.txt', print_r($request->all(), true));

        return redirect()->route('post', ['id' => $id, 'slug' => $slug]);
    }
}
