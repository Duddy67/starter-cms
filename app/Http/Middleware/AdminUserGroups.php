<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminUserGroups
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

        $create = ['admin.users.groups.index', 'admin.users.groups.create', 'admin.users.groups.store'];
        $update = ['admin.users.groups.update', 'admin.users.groups.edit'];
        $delete = ['admin.users.groups.destroy', 'admin.users.groups.massDestroy'];

        if (in_array($routeName, $create) && !auth()->user()->isAllowedTo('create-group')) {
            return redirect()->route('admin')->with('error', __('messages.generic.access_not_auth'));
        }

        if (in_array($routeName, $update) && !auth()->user()->isAllowedTo('update-group') && !auth()->user()->isAllowedTo('update-own-group')) {
            return redirect()->route('admin.users.groups.index')->with('error', __('messages.group.edit_not_auth'));
        }

        if (in_array($routeName, $delete) && !auth()->user()->isAllowedTo('delete-group') && !auth()->user()->isAllowedTo('delete-own-group')) {
            return redirect()->route('admin.users.groups.index')->with('error', __('messages.group.delete_not_auth'));
        }

        return $next($request);
    }
}
