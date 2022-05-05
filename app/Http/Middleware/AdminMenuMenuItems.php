<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Menu\Menu;

class AdminMenuMenuItems
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

        // Check first for a valid menu code.
        if (!$menu = Menu::where('code', $request->route()->parameter('code'))->first()) {
            return abort('404');
        }

        // Now check if the current user has access to the corresponding menu.
        if (!$menu->canAccess()) {
            return redirect()->route('admin')->with('error', __('messages.generic.access_not_auth'));
        }

        $create = ['admin.menu.menuitems.index', 'admin.menu.menuitems.create', 'admin.menu.menuitems.store'];
        $update = ['admin.menu.menuitems.update', 'admin.menu.menuitems.edit'];
        $delete = ['admin.menu.menuitems.destroy', 'admin.menu.menuitems.massDestroy'];

        if (in_array($routeName, $create) && !auth()->user()->isAllowedTo('create-menu')) {
            return redirect()->route('admin')->with('error', __('messages.generic.access_not_auth'));
        }

        if (in_array($routeName, $update) && !auth()->user()->isAllowedTo('update-menu')) {
            return redirect()->route('admin.menu.menuitems.index')->with('error', __('messages.menu.edit_not_auth'));
        }

        if (in_array($routeName, $delete) && !auth()->user()->isAllowedTo('delete-menu')) {
            return redirect()->route('admin.menu.menuitems.index')->with('error', __('messages.menu.delete_not_auth'));
        }

        return $next($request);
    }
}
