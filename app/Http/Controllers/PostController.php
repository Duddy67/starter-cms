<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Post\Setting as PostSetting;
use App\Models\Menu;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;


class PostController extends Controller
{
    public function show(Request $request, string $locale, int $id, string $slug)
    {
        $post = Post::getItem($id, $locale);
        $menu = Menu::getMenu('main-menu');
        $menu->allow_registering = Setting::getValue('website', 'allow_registering', 0);
        $theme = Setting::getValue('website', 'theme', 'starter');

	if (!$post || $post->layoutItems()->exists() && (!view()->exists('themes.'.$theme.'.pages.'.$post->page))) {
            $page = '404';
            return view('themes.'.$theme.'.index', compact('locale', 'page', 'menu'));
	}

	if (!$post->canAccess()) {
            $page = '403';
            return view('themes.'.$theme.'.index', compact('locale', 'page', 'menu'));
	}

        $page = 'post';

        $post->translation = $post->getTranslation($locale);
        $post->global_settings = PostSetting::getDataByGroup('posts');
	$settings = $post->getSettings();
        $timezone = Setting::getValue('app', 'timezone');
        $metaData = json_decode($post->meta_data, true);
        $segments = Setting::getSegments('Post');
	$query = array_merge($request->query(), ['id' => $id, 'slug' => $slug, 'locale' => $locale]);

        return view('themes.'.$theme.'.index', compact('locale', 'page', 'menu', 'id', 'slug', 'post', 'segments', 'settings', 'timezone', 'metaData', 'query'));
    }
}
