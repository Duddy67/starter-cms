<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post\Category;
use App\Models\Post\Setting as PostSetting;
use App\Models\Setting;

class SiteController extends Controller
{
    public function index(Request $request)
    {
        $locale = $request->segment(1);
        $posts = null;
        $settings = $metaData = [];
        $query = $request->query();
        // Get the page name from the url or set it to the home page name in the
        // corresponding language if none is found.
        $name = ($request->segment(2)) ? $request->segment(2) : __('locales.homepage.'.$locale, [], 'en');
        $page = Setting::getPage($name);

        $category = Category::getItem($page['name'], $locale);

        if ($category) {
            // Prioritize the category page over the page from the url.
            $page['name'] = (view()->exists('themes.'.$page['theme'].'.pages.'.$category->page)) ? $category->page : $page['name'];

            // If the page from the url is used, make sure that the view exists.
            if ($category->page != $page['name'] && !view()->exists('themes.'.$page['theme'].'.pages.'.$page['name'])) {
                $page['name'] = '404';
                return view('themes.'.$page['theme'].'.index', compact('locale', 'page'));
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
        elseif (file_exists(resource_path().'/views/themes/'.$page['theme'].'/pages/'.__('locales.pages.'.$page['name'], [], 'en').'.blade.php')) {
            $page['name'] = __('locales.pages.'.$page['name'], [], 'en');
            return view('themes.'.$page['theme'].'.index', compact('locale', 'page', 'query'));
        }
        else {
            $page['name'] = '404';
            return view('themes.'.$page['theme'].'.index', compact('locale', 'page'));
        }

        $segments = Setting::getSegments('Post');

        return view('themes.'.$page['theme'].'.index', compact('locale', 'page', 'category', 'settings', 'posts', 'segments', 'metaData', 'query'));
    }


    public function show(Request $request)
    {
        $locale = $request->segment(1);
        $page = Setting::getPage($request->segment(2));

        $category = Category::getItem($page['name'], $locale);

        // First make sure the category exists.
	if (!$category) {
            $page['name'] = '404';
            return view('themes.'.$page['theme'].'.index', compact('locale', 'page'));
        }

        // Then make sure the post exists and is part of the category.
	if (!$post = $category->posts->where('id', $request->segment(3))->first()) {
            $page['name'] = '404';
            return view('themes.'.$page['theme'].'.index', compact('locale', 'page'));
        }

        $post->global_settings = PostSetting::getDataByGroup('posts');
        $page['name'] = $page['name'].'-details';
        $segments = Setting::getSegments('Post');
        $metaData = json_decode($post->meta_data, true);
	$query = $request->query();

        return view('themes.'.$page['theme'].'.index', compact('locale', 'page', 'category', 'post', 'segments', 'metaData', 'query'));
    }
}
