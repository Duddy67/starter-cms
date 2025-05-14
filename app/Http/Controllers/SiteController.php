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
        $name = ($request->segment(1)) ? $request->segment(1) : 'home';
        $page = Setting::getPage($name);

        $posts = null;
        $metaData = [];
        $query = $request->query();

        if ($category = Category::where('slug', $page['name'])->first()) {
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

        // Then make sure the post exists, is published and is part of the category.
        $post = Post::select('posts.*', 'users.name as owner_name', 'users2.name as modifier_name')
			->leftJoin('users', 'posts.owned_by', '=', 'users.id')
			->leftJoin('users as users2', 'posts.updated_by', '=', 'users2.id')
                        ->join('categorizables', function($join) use($category) {
                              $join->on('categorizables.categorizable_id', '=', 'posts.id')
                                   ->where('categorizables.category_id', '=', $category->id);
                          })->where('posts.id', $request->segment(2))->where('posts.status', 'published')->first();

	if (!$post) {
            $page['name'] = '404';
            return view('themes.'.$page['theme'].'.index', compact('page'));
        }

        // Required in case of category extra fields.
        $category->global_settings = Setting::getDataByGroup('categories', $category);

        $post->settings = $post->getSettings();
        // Required in case of extra fields.
        $post->global_settings = Setting::getDataByGroup('posts', $post);
        $segments = Setting::getSegments('Post');
        $metaData = $post->meta_data;
        $query = array_merge($request->query(), ['id' => $post->id, 'slug' => $post->slug]);
        // To display the post as a sub-page called 'details' by default.
        $page['sub-page'] = 'details';

        // The post has a layout and the name of the selected page ends by '-layout'.
        if ($post->layoutItems()->exists() && preg_match('#^[a-z0-9\-]+\-layout$#', $post->page)) {
            // Set the layout page name.
            $page['name'] = $post->page;
        }

        return view('themes.'.$page['theme'].'.index', compact('page', 'category', 'post', 'segments', 'metaData', 'query'));
    }
}
