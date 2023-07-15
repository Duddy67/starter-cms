<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminPostCategories
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
	$routeName = $request->route()->getName();

        $create = ['admin.posts.categories.index', 'admin.posts.categories.create', 'admin.posts.categories.store'];
        $update = ['admin.posts.categories.update', 'admin.posts.categories.edit'];
        $delete = ['admin.posts.categories.destroy', 'admin.posts.categories.massDestroy'];

	if (in_array($routeName, $create) && !auth()->user()->isAllowedTo('create-post-category')) {
	    return redirect()->route('admin')->with('error', __('messages.generic.access_not_auth'));
	}

	if (in_array($routeName, $update) && !auth()->user()->isAllowedTo('update-post-category')) {
	    return redirect()->route('admin.posts.categories.index')->with('error', __('messages.category.edit_not_auth'));
	}

	if (in_array($routeName, $delete) && !auth()->user()->isAllowedTo('delete-post-category')) {
	    return redirect()->route('admin.posts.categories.index')->with('error', __('messages.category.delete_not_auth'));
	}

        return $next($request);
    }
}
