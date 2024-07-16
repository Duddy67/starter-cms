<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cms\Category;
use App\Models\Cms\Setting;
use App\Models\Post;

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

        $category = Category::getCategory($page['name'], 'post', $locale);

        if ($category) {
            // Prioritize the category page over the page from the url.
            $page['name'] = (view()->exists('themes.'.$page['theme'].'.pages.'.$category->page)) ? $category->page : $page['name'];

            // If the page from the url is used, make sure that the view exists.
            if ($category->page != $page['name'] && !view()->exists('themes.'.$page['theme'].'.pages.'.$page['name'])) {
                $page['name'] = '404';
                return view('themes.'.$page['theme'].'.index', compact('locale', 'page'));
            }

            $category->settings = $category->getSettings();
            // Required in case of category extra fields.
            $category->global_settings = Setting::getDataByGroup('categories', $category);
            $metaData = $category->meta_data;
            $posts = $category->getItemCollection($request);

            if (count($posts)) {
                // Use the first post as model to get the global post settings.
                $globalPostSettings = Setting::getDataByGroup('posts', $posts[0]);

                // Set the setting values manually to improve performance a bit.
                foreach ($posts as $post) {
                    // N.B: Don't set the values directly through the object. Use an array to
                    // prevent the "Indirect modification of overloaded property has no effect" error.
                    $settings = [];

                    foreach ($post->settings as $key => $value) {
                        // Set the item setting values against the item global setting.
                        $settings[$key] = ($value == 'global_setting') ? $globalPostSettings[$key] : $post->settings[$key];
                    }

                    $post->settings = $settings;
                    // Required in case of extra fields.
                    $post->global_settings = $globalPostSettings;
                }
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

        return view('themes.'.$page['theme'].'.index', compact('locale', 'page', 'category', 'posts', 'segments', 'metaData', 'query'));
    }


    public function show(Request $request)
    {
        $locale = $request->segment(1);
        $page = Setting::getPage($request->segment(2));

        $category = Category::getCategory($page['name'], 'post', $locale);

        // First make sure the category exists.
	if (!$category) {
            $page['name'] = '404';
            return view('themes.'.$page['theme'].'.index', compact('locale', 'page'));
        }

        // Then make sure the post exists and is part of the category.
        if (!$post = Post::getPost($request->segment(3), $locale)) {
            $page['name'] = '404';
            return view('themes.'.$page['theme'].'.index', compact('locale', 'page'));
        }

        // Required in case of category extra fields.
        $category->global_settings = Setting::getDataByGroup('categories', $category);

        $post->settings = $post->getSettings();
        // Required in case of extra fields.
        $post->global_settings = Setting::getDataByGroup('posts', $post);
        $segments = Setting::getSegments('Post');
        $metaData = $post->meta_data;
	$query = $request->query();
        // To display the post as a sub-page called 'details' by default.
        $page['sub-page'] = 'details';

        return view('themes.'.$page['theme'].'.index', compact('locale', 'page', 'category', 'post', 'segments', 'metaData', 'query'));
    }
}
