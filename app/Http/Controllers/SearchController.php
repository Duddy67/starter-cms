<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Menu;
use App\Models\Setting;

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

        if ($request->filled('keyword')) {
            //$posts = Post::searchInPosts($request->search)->query( function($query) {
                     //})->get();
            $posts = Post::searchInPosts($request->input('keyword'))->get();

            $posts = $this->formatResults($posts, $request->input('keyword'));
        }
        else {
            $posts = null;
        }

          
        return view('themes.starter.index', compact('page', 'posts', 'menu'));
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
