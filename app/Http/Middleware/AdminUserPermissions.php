<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User\Role;

class AdminUserPermissions
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
        $access = ['admin.user.permissions.index', 'admin.user.permissions.build', 'admin.user.permissions.rebuild'];

        if (in_array($routeName, $access) && auth()->user()->getRoleType() != 'super-admin') {
            return redirect()->route('admin')->with('error', __('messages.generic.access_not_auth'));
        }

        return $next($request);
    }
}
