<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post\Category;
use App\Models\Post\Setting;


class SiteController extends Controller
{
    public function index(Request $request, $page = null)
    {
        $page = ($page) ? $page : 'home';
        $posts = null;
        $settings = $metaData = [];

        if ($category = Category::where('slug', $page)->first()) {
            $posts = $category->getPosts($request);

            $globalSettings = Setting::getDataByGroup('category');

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

        $query = $request->query();

        return view('index', compact('page', 'category', 'settings', 'posts', 'metaData', 'query'));
    }
}
