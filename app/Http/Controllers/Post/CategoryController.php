<?php

namespace App\Http\Controllers\Post;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post\Category;
use App\Models\Post\Setting as PostSetting;
use App\Models\Menu;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;


class CategoryController extends Controller
{
    public function index(Request $request, string $locale, int $id, string $slug)
    {
        $page = 'post.category';
        $theme = Setting::getValue('website', 'theme', 'starter');
        $menu = Menu::getMenu('main-menu');

	if (!$category = Category::getItem($id, $locale)) {
            $page = '404';
            return view('themes.'.$theme.'.index', compact('locale', 'page', 'menu'));
	}

	if (!$category->canAccess()) {
            $page = '403';
            return view('themes.'.$theme.'.index', compact('locale', 'page', 'menu'));
	}

        $category->global_settings = PostSetting::getDataByGroup('categories');
	$settings = $category->getSettings();
	$posts = $category->getPosts($request);
        $segments = Setting::getSegments('Post');
        $metaData = json_decode($category->meta_data, true);
	$query = array_merge($request->query(), ['locale' => $locale, 'id' => $id, 'slug' => $slug]);

        return view('themes.'.$theme.'.index', compact('locale', 'page', 'menu', 'category', 'segments', 'settings', 'posts', 'metaData', 'query'));
    }
}
