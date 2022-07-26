<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Menu;
use App\Models\Setting;
use App\Models\Post\Setting as PostSetting;

class SearchController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $page = $request->segment(1);
        $menu = Menu::getMenu('main-menu');
        $menu->allow_registering = Setting::getValue('website', 'allow_registering', 0);
        $theme = Setting::getValue('website', 'theme', 'starter');
        $perPage = $request->input('per_page', Setting::getValue('pagination', 'per_page'));
        $posts = collect(new Post);
        $message = __('messages.search.no_matches_found');

        if ($request->filled('keyword')) {
            if (strlen($request->input('keyword')) > 3) {
                $posts = Post::searchInPosts($request->input('keyword'))->paginate($perPage);
                $posts = $this->formatResults($posts, $request->input('keyword'));

                $globalSettings = PostSetting::getDataByGroup('posts');

                foreach ($posts as $post) {
                    $post->global_settings = $globalSettings;
                }
            }
            else {
                $message = __('messages.search.invalid_keyword_length', ['length' => 3]);
            }
        }
          
        return view('themes.'.$theme.'.index', compact('page', 'posts', 'menu', 'message'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function formatResults($posts, $keyword)
    {
        foreach ($posts as $post) {
            $post->search_results = [];

            if (preg_match_all('#.{0,30}'.$keyword.'.{0,30}#i', $post->raw_content, $matches)) {
                $post->search_results = $matches[0];
            }
        }

        return $posts;
    }
}
