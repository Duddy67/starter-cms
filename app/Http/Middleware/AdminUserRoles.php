<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminUserRoles
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

        $create = ['admin.users.roles.index', 'admin.users.roles.create', 'admin.users.roles.store'];
        $update = ['admin.users.roles.update', 'admin.users.roles.edit'];
        $delete = ['admin.users.roles.destroy', 'admin.users.roles.massDestroy'];

        if (in_array($routeName, $create) && !auth()->user()->isAllowedTo('create-user-roles')) {
            return redirect()->route('admin')->with('error', __('messages.generic.access_not_auth'));
        }

        if (in_array($routeName, $update) && !auth()->user()->isAllowedTo('update-user-roles')) {
            return redirect()->route('admin.users.roles.index')->with('error', __('messages.role.edit_not_auth'));
        }

        if (in_array($routeName, $delete) && !auth()->user()->isAllowedTo('delete-user-roles')) {
            return redirect()->route('admin.users.roles.index')->with('error', __('messages.role.delete_not_auth'));
        }

        return $next($request);
    }
}
