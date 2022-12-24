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
        $locale = $request->segment(1);
        $posts = null;
        $settings = $metaData = [];
        $menu = Menu::getMenu('main-menu');
        $menu->allow_registering = Setting::getValue('website', 'allow_registering', 0);
        $theme = Setting::getValue('website', 'theme', 'starter');
        $query = $request->query();
        $timezone = Setting::getValue('app', 'timezone');
        // Get the page name from the url or set it to the home page name in the
        // corresponding language if none is found.
        $page = ($request->segment(2)) ? $request->segment(2) : __('locales.homepage.'.$locale, [], 'en');

        $category = Category::getItem($page, $locale);

        if ($category) {
            // Prioritize the category page over the page from the url.
            $page = (view()->exists('themes.'.$theme.'.pages.'.$category->page)) ? $category->page : $page;

            // If the page from the url is used, make sure that the view exists.
            if ($category->page != $page && !view()->exists('themes.'.$theme.'.pages.'.$page)) {
                $page = '404';
                return view('themes.'.$theme.'.index', compact('locale', 'page', 'menu'));
            }

            $posts = $category->getAllPosts($request);

            $globalSettings = PostSetting::getDataByGroup('categories');

            foreach ($category->settings as $key => $value) {
                if ($value == 'global_setting') {
                    $settings[$key] = $globalSettings[$key];
                }
                else {
                    $settings[$key] = $category->settings[$key];
                }
            }

            $category->global_settings = $globalSettings;
            $metaData = json_decode($category->meta_data, true);
            $globalSettings = PostSetting::getDataByGroup('posts');

            foreach ($posts as $post) {
                $post->global_settings = $globalSettings;
            }
        }
        // Just display the page. Get the page name from the locale page mapping array.
        elseif (file_exists(resource_path().'/views/themes/'.$theme.'/pages/'.__('locales.pages.'.$page, [], 'en').'.blade.php')) {
            $page = __('locales.pages.'.$page, [], 'en');
            return view('themes.'.$theme.'.index', compact('locale', 'page', 'menu', 'query'));
        }
        else {
            $page = '404';
            return view('themes.'.$theme.'.index', compact('locale', 'page', 'menu'));
        }

        $segments = Setting::getSegments('Post');

        return view('themes.'.$theme.'.index', compact('locale', 'page', 'menu', 'category', 'settings', 'posts', 'segments', 'metaData', 'timezone', 'query'));
    }


    public function show(Request $request)
    {
        $locale = $request->segment(1);
        $page = $request->segment(2);
        $menu = Menu::getMenu('main-menu');
        $menu->allow_registering = Setting::getValue('website', 'allow_registering', 0);
        $theme = Setting::getValue('website', 'theme', 'starter');
        $timezone = Setting::getValue('app', 'timezone');

        $category = Category::getItem($page, $locale);

        // First make sure the category exists.
	if (!$category) {
            $page = '404';
            return view('themes.'.$theme.'.index', compact('locale', 'page', 'menu'));
        }

        // Then make sure the post exists and is part of the category.
	if (!$post = $category->posts->where('id', $request->segment(3))->first()) {
            $page = '404';
            return view('themes.'.$theme.'.index', compact('locale', 'page', 'menu'));
        }

        $post->global_settings = PostSetting::getDataByGroup('posts');
        $page = $page.'-details';
        $segments = Setting::getSegments('Post');
        $metaData = json_decode($post->meta_data, true);
	$query = $request->query();

        return view('themes.'.$theme.'.index', compact('locale', 'page', 'menu', 'category', 'post', 'segments', 'metaData', 'timezone', 'query'));
    }
}
