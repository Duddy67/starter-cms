<?php

namespace App\Http\Controllers\Post;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post\Category;
use App\Models\Post\Setting as PostSetting;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;


class CategoryController extends Controller
{
    public function index(Request $request, $id, $slug)
    {
        $page = Setting::getPage('post.category');

	if (!$category = Category::where('id', $id)->first()) {
            $page['name'] = '404';
            return view('themes.'.$page['theme'].'.index', compact('page'));
	}

	if (!$category->canAccess()) {
            $page['name'] = '403';
            return view('themes.'.$page['theme'].'.index', compact('page'));
	}

        $category->global_settings = PostSetting::getDataByGroup('categories');
	$settings = $category->getSettings();
	$posts = $category->getPosts($request);
        $segments = Setting::getSegments('Post');
        $metaData = $category->meta_data;
	$query = array_merge($request->query(), ['id' => $id, 'slug' => $slug]);

        return view('themes.'.$page['theme'].'.index', compact('page', 'category', 'segments', 'settings', 'posts', 'metaData', 'query'));
    }
}
