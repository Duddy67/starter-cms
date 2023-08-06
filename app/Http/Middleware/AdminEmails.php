<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminEmails
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

        $access = ['admin.cms.emails.index'];
        $create = ['admin.cms.emails.create', 'admin.cms.emails.store'];
        $update = ['admin.cms.emails.update', 'admin.cms.emails.edit'];
        $delete = ['admin.cms.emails.destroy', 'admin.cms.emails.massDestroy'];

	// N.B: Some admin type users might be allowed to only update email subjects and bodies. 
	//      To allow them to access the email list the update-emails permission is used  
	//      as the access-emails permission doesn't exists. 
	if (in_array($routeName, $access) && !auth()->user()->isAllowedTo('update-emails')) {
	    return redirect()->route('admin')->with('error', __('messages.generic.access_not_auth'));
	}

	if (in_array($routeName, $create) && !auth()->user()->isSuperAdmin()) {
	    return redirect()->route('admin')->with('error', __('messages.generic.access_not_auth'));
	}

	if (in_array($routeName, $update) && !auth()->user()->isAllowedTo('update-emails')) {
	    return redirect()->route('admin.cms.emails.index')->with('error', __('messages.email.edit_not_auth'));
	}

	if (in_array($routeName, $delete) &&  !auth()->user()->isSuperAdmin()) {
	    return redirect()->route('admin.cms.emails.index')->with('error', __('messages.email.delete_not_auth'));
	}

        return $next($request);
    }
}
