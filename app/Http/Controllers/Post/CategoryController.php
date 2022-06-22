<?php

namespace App\Http\Controllers\Post;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post\Category;
use App\Models\Menu;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;


class CategoryController extends Controller
{
    public function index(Request $request, $id, $slug)
    {
        $page = 'post.category';
        $theme = Setting::getValue('website', 'theme', 'starter');

	if (!$category = Category::where('id', $id)->first()) {
            $page = '404';
            return view('themes.'.$theme.'.index', compact('page', 'menu'));
	}

	if (!$category->canAccess()) {
            $page = '403';
            return view('themes.'.$theme.'.index', compact('page', 'menu'));
	}

	$settings = $category->getSettings();
        $menu = Menu::getMenu('main-menu');
	$posts = $category->getPosts($request);
        $metaData = $category->meta_data;
	$query = array_merge($request->query(), ['id' => $id, 'slug' => $slug]);

        return view('themes.'.$theme.'.index', compact('page', 'menu', 'category', 'settings', 'posts', 'metaData', 'query'));
    }
}
