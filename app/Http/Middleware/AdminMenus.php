<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMenus
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

        $create = ['admin.menus.index', 'admin.menus.create', 'admin.menus.store'];
        $update = ['admin.menus.update', 'admin.menus.edit'];
        $delete = ['admin.menus.destroy', 'admin.menus.massDestroy'];

        if (in_array($routeName, $create) && !auth()->user()->isAllowedTo('create-menus')) {
            return redirect()->route('admin')->with('error', __('messages.generic.access_not_auth'));
        }

        if (in_array($routeName, $update) && !auth()->user()->isAllowedTo('update-menus')) {
            return redirect()->route('admin.menus.index')->with('error', __('messages.menu.edit_not_auth'));
        }

        if (in_array($routeName, $delete) && !auth()->user()->isAllowedTo('delete-menus')) {
            return redirect()->route('admin.menus.index')->with('error', __('messages.menu.delete_not_auth'));
        }

        return $next($request);
    }
}
