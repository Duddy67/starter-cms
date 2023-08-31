<?php

namespace App\Http\Controllers\Post;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cms\Category;
use App\Models\Cms\Setting;
use Illuminate\Support\Facades\Auth;


class CategoryController extends Controller
{
    public function index(Request $request, string $locale, int $id, string $slug)
    {
        $page = Setting::getPage('post.category');

	if (!$category = Category::getCategory($id, 'post', $locale)) {
            $page['name'] = '404';
            return view('themes.'.$page['theme'].'.index', compact('locale', 'page'));
	}

	if (!$category->canAccess()) {
            $page['name'] = '403';
            return view('themes.'.$page['theme'].'.index', compact('locale', 'page'));
	}

        $category->settings = $category->getSettings();
        $posts = $category->getItemCollection($request, ['pagination']);

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

        $segments = Setting::getSegments('Post');
        $metaData = $category->meta_data;
	$query = array_merge($request->query(), ['locale' => $locale, 'id' => $id, 'slug' => $slug]);

        return view('themes.'.$page['theme'].'.index', compact('locale', 'page', 'category', 'segments', 'posts', 'metaData', 'query'));
    }
}
