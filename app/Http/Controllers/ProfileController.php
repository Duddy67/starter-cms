<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cms\Setting;


class ProfileController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function show()
    {
        $theme = Setting::getValue('website', 'theme', 'starter');

        return view('themes.'.$theme.'.profile');
    }
}
