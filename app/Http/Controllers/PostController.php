<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;


class PostController extends Controller
{
    public function show(Request $request, $id, $slug)
    {
        $post = Post::select('posts.*', 'users.name as owner_name')
			->selectRaw('IFNULL(users2.name, ?) as modifier_name', [__('labels.generic.unknown_user')])
			->leftJoin('users', 'posts.owned_by', '=', 'users.id')
			->leftJoin('users as users2', 'posts.updated_by', '=', 'users2.id')
			->where('posts.id', $id)->first();

	if (!$post) {
	    return abort('404');
	}

	if (!$post->canAccess()) {
	    return abort('403');
	}

        $page = 'post';

	$settings = $post->getSettings();
	$query = array_merge($request->query(), ['id' => $id, 'slug' => $slug]);

        return view('default', compact('page', 'id', 'slug', 'post', 'settings', 'query'));
    }
}
