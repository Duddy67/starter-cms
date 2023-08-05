<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use App\Models\Cms\Setting;


class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::PROFILE;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->redirectTo = (Setting::getValue('website', 'redirect_to_admin', 0)) ? RouteServiceProvider::ADMIN : $this->redirectTo;
        $this->middleware('guest')->except('logout');
    }

    /**
     * Show the application's login form.
     * Info: Overwrites AuthenticatesUsers trait method.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        $theme = Setting::getValue('website', 'theme', 'starter');
        $allowRegistering = Setting::getValue('website', 'allow_registering', 0);

        return view('themes.'.$theme.'.auth.login', compact('allowRegistering'));
    }

    /**
     * The user has been authenticated.
     * Info: Overwrites AuthenticatesUsers trait method.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        return redirect(app()->getLocale().RouteServiceProvider::PROFILE);
    }
}
