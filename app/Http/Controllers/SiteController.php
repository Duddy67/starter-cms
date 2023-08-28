<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
//use App\Models\Post\Category;
use App\Models\Cms\Category;
use App\Models\Cms\Setting;

class SiteController extends Controller
{
    public function index(Request $request)
    {
        $name = ($request->segment(1)) ? $request->segment(1) : 'home';
        $page = Setting::getPage($name);

        $posts = null;
        $metaData = [];
        $query = $request->query();

        if ($category = Category::where('slug', $page['name'])->first()) {
            $category->settings = $category->getSettings();
            $metaData = $category->meta_data;
            //$posts = $category->getAllPosts($request);
            $posts = $category->getItems($request);

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
                }
            }
        }
        elseif ($page['name'] == 'home' || file_exists(resource_path().'/views/themes/'.$page['theme'].'/pages/'.$page['name'].'.blade.php')) {
            return view('themes.'.$page['theme'].'.index', compact('page', 'query'));
        }
        else {
            $page['name'] = '404';
            return view('themes.'.$page['theme'].'.index', compact('page'));
        }

        $segments = Setting::getSegments('Post');

        return view('themes.'.$page['theme'].'.index', compact('page', 'category', 'posts', 'segments', 'metaData', 'query'));
    }


    public function show(Request $request)
    {
        $page = Setting::getPage($request->segment(1));

        // First make sure the category exists.
	if (!$category = Category::where('slug', $page['name'])->first()) {
            $page['name'] = '404';
            return view('themes.'.$page['theme'].'.index', compact('page'));
        }

        // Then make sure the post exists and is part of the category.
	if (!$post = $category->posts->where('id', $request->segment(2))->first()) {
            $page['name'] = '404';
            return view('themes.'.$page['theme'].'.index', compact('page'));
        }

        $post->settings = $post->getSettings();
        $page['name'] = $page['name'].'-details';
        $segments = Setting::getSegments('Post');
        $metaData = $post->meta_data;
	$query = $request->query();

        return view('themes.'.$page['theme'].'.index', compact('page', 'category', 'post', 'segments', 'metaData', 'query'));
    }
}
