<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminBlogCategories
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

        $create = ['admin.blog.categories.index', 'admin.blog.categories.create', 'admin.blog.categories.store'];
        $update = ['admin.blog.categories.update', 'admin.blog.categories.edit'];
        $delete = ['admin.blog.categories.destroy', 'admin.blog.categories.massDestroy'];

	if (in_array($routeName, $create) && !auth()->user()->isAllowedTo('create-blog-category')) {
	    return redirect()->route('admin')->with('error', __('messages.generic.access_not_auth'));
	}

	if (in_array($routeName, $update) && !auth()->user()->isAllowedTo('update-blog-category')) {
	    return redirect()->route('admin.blog.categories.index')->with('error', __('messages.category.edit_not_auth'));
	}

	if (in_array($routeName, $delete) && !auth()->user()->isAllowedTo('delete-blog-category')) {
	    return redirect()->route('admin.blog.categories.index')->with('error', __('messages.category.delete_not_auth'));
	}

        return $next($request);
    }
}
