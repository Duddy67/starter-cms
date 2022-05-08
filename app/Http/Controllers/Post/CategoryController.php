<?php

namespace App\Http\Controllers\Post;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post\Category;
use Illuminate\Support\Facades\Auth;


class CategoryController extends Controller
{
    public function index(Request $request, $id, $slug)
    {
        $page = 'blog.category';

	if (!$category = Category::where('id', $id)->first()) {
	    return abort('404');
	}

	if (!$category->canAccess()) {
	    return abort('403');
	}

	$settings = $category->getSettings();
	$posts = $category->getPosts($request);
	$query = array_merge($request->query(), ['id' => $id, 'slug' => $slug]);

        return view('default', compact('page', 'category', 'settings', 'posts', 'query'));
    }
}
