<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminBlogPosts
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

        $create = ['admin.blog.posts.index', 'admin.blog.posts.create', 'admin.blog.posts.store'];
        $update = ['admin.blog.posts.update', 'admin.blog.posts.edit'];
        $delete = ['admin.blog.posts.destroy', 'admin.blog.posts.massDestroy'];

        if (in_array($routeName, $create) && !auth()->user()->isAllowedTo('create-post')) {
            return redirect()->route('admin')->with('error', __('messages.generic.access_not_auth'));
        }

        if (in_array($routeName, $update) && !auth()->user()->isAllowedTo('update-post') && !auth()->user()->isAllowedTo('update-own-post')) {
            return redirect()->route('admin.blog.posts.index')->with('error', __('messages.post.edit_not_auth'));
        }

        if (in_array($routeName, $delete) && !auth()->user()->isAllowedTo('delete-post') && !auth()->user()->isAllowedTo('delete-own-post')) {
            return redirect()->route('admin.blog.posts.index')->with('error', __('messages.post.delete_not_auth'));
        }

        return $next($request);
    }
}
