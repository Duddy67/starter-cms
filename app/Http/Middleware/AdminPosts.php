<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminPosts
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

        $create = ['admin.posts.index', 'admin.posts.create', 'admin.posts.store'];
        $update = ['admin.posts.update', 'admin.posts.edit'];
        $delete = ['admin.posts.destroy', 'admin.posts.massDestroy'];

        if (in_array($routeName, $create) && !auth()->user()->isAllowedTo('create-posts')) {
            return redirect()->route('admin')->with('error', __('messages.generic.access_not_auth'));
        }

        if (in_array($routeName, $update) && !auth()->user()->isAllowedTo('update-posts') && !auth()->user()->isAllowedTo('update-own-posts')) {
            return redirect()->route('admin.posts.index')->with('error', __('messages.post.edit_not_auth'));
        }

        if (in_array($routeName, $delete) && !auth()->user()->isAllowedTo('delete-posts') && !auth()->user()->isAllowedTo('delete-own-posts')) {
            return redirect()->route('admin.posts.index')->with('error', __('messages.post.delete_not_auth'));
        }

        return $next($request);
    }
}
