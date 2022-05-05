<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMenuMenus
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

        $create = ['admin.menu.menus.index', 'admin.menu.menus.create', 'admin.menu.menus.store'];
        $update = ['admin.menu.menus.update', 'admin.menu.menus.edit'];
        $delete = ['admin.menu.menus.destroy', 'admin.menu.menus.massDestroy'];

        if (in_array($routeName, $create) && !auth()->user()->isAllowedTo('create-menu')) {
            return redirect()->route('admin')->with('error', __('messages.generic.access_not_auth'));
        }

        if (in_array($routeName, $update) && !auth()->user()->isAllowedTo('update-menu')) {
            return redirect()->route('admin.menu.menus.index')->with('error', __('messages.menu.edit_not_auth'));
        }

        if (in_array($routeName, $delete) && !auth()->user()->isAllowedTo('delete-menu')) {
            return redirect()->route('admin.menu.menus.index')->with('error', __('messages.menu.delete_not_auth'));
        }

        return $next($request);
    }
}
