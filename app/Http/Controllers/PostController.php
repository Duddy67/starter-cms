<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;
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
        $theme = Setting::getValue('website', 'theme', 'starter');

	if (!$post) {
            $page = '404';
            return view('themes.'.$theme.'.index', compact('page', 'menu'));
	}

	if (!$post->canAccess()) {
            $page = '403';
            return view('themes.'.$theme.'.index', compact('page', 'menu'));
	}

        $page = 'post';

	$settings = $post->getSettings();
        $timezone = Setting::getValue('app', 'timezone');
        $metaData = $post->meta_data;
	$query = array_merge($request->query(), ['id' => $id, 'slug' => $slug]);

        return view('themes.'.$theme.'.index', compact('page', 'menu', 'id', 'slug', 'post', 'settings', 'timezone', 'metaData', 'query'));
    }
}
