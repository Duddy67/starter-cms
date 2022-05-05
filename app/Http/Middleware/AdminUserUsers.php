<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminUserUsers
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

        $create = ['admin.user.users.index', 'admin.user.users.create', 'admin.user.users.store'];
        $update = ['admin.user.users.update', 'admin.user.users.edit'];
        $delete = ['admin.user.users.destroy', 'admin.user.users.massDestroy'];

        if (in_array($routeName, $create) && !auth()->user()->isAllowedTo('create-user')) {
            return redirect()->route('admin')->with('error', __('messages.generic.access_not_auth'));
        }

        if (in_array($routeName, $update) && !auth()->user()->isAllowedTo('update-user')) {
            return redirect()->route('admin.user.users.index')->with('error', __('messages.user.edit_not_auth'));
        }

        if (in_array($routeName, $delete) && !auth()->user()->isAllowedTo('delete-user')) {
            return redirect()->route('admin.user.users.index')->with('error', __('messages.user.delete_not_auth'));
        }

        return $next($request);
    }
}
