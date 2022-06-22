<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post\Category;
use App\Models\Post\Setting as PostSetting;
use App\Models\Menu;
use App\Models\Setting;


class SiteController extends Controller
{
    public function index(Request $request)
    {
        $page = ($request->segment(1)) ? $request->segment(1) : 'home';
        $posts = null;
        $settings = $metaData = [];
        $menu = Menu::getMenu('main-menu');

        if ($category = Category::where('slug', $page)->first()) {
            $posts = $category->getPosts($request);

            $globalSettings = PostSetting::getDataByGroup('category');

            foreach ($category->settings as $key => $value) {
                if ($value == 'global_setting') {
                    $settings[$key] = $globalSettings[$key];
                }
                else {
                    $settings[$key] = $category->settings[$key];
                }
            }

            $metaData = $category->meta_data;
        }
        else {
            $page = '404';
            return view('themes.'.$theme.'.index', compact('page', 'menu'));
        }

        $theme = Setting::getValue('website', 'theme', 'starter');
        $query = $request->query();

        return view('themes.'.$theme.'.index', compact('page', 'menu', 'category', 'settings', 'posts', 'metaData', 'query'));
    }


    public function show(Request $request)
    {
        $page = $request->segment(1);
        $menu = Menu::getMenu('main-menu');
        $theme = Setting::getValue('website', 'theme', 'starter');

        // First make sure the category exists.
	if (!$category = Category::where('slug', $page)->first()) {
            $page = '404';
            return view('themes.'.$theme.'.index', compact('page', 'menu'));
        }

        // Then make sure the post exists and is part of the category.
	if (!$post = $category->posts->where('id', $request->segment(2))->first()) {
            $page = '404';
            return view('themes.'.$theme.'.index', compact('page', 'menu'));
        }

        $page = $page.'-details';
	$query = $request->query();

        return view('themes.'.$theme.'.index', compact('page', 'menu', 'category', 'post', 'query'));
    }
}
