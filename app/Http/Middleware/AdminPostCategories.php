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

        $create = ['admin.post.categories.index', 'admin.post.categories.create', 'admin.post.categories.store'];
        $update = ['admin.post.categories.update', 'admin.post.categories.edit'];
        $delete = ['admin.post.categories.destroy', 'admin.post.categories.massDestroy'];

	if (in_array($routeName, $create) && !auth()->user()->isAllowedTo('create-post-category')) {
	    return redirect()->route('admin')->with('error', __('messages.generic.access_not_auth'));
	}

	if (in_array($routeName, $update) && !auth()->user()->isAllowedTo('update-post-category')) {
	    return redirect()->route('admin.post.categories.index')->with('error', __('messages.category.edit_not_auth'));
	}

	if (in_array($routeName, $delete) && !auth()->user()->isAllowedTo('delete-post-category')) {
	    return redirect()->route('admin.post.categories.index')->with('error', __('messages.category.delete_not_auth'));
	}

        return $next($request);
    }
}
