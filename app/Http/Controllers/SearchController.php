<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
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
        $page = Setting::getPage($request->segment(1));
        $maxRows = Setting::getValue('search', 'autocomplete_max_results');
        $perPage = $request->input('per_page', Setting::getValue('pagination', 'per_page'));
        $posts = collect(new Post);
        $message = __('messages.search.no_matches_found');

        if ($request->filled('keyword')) {
            if (strlen($request->input('keyword')) > 2) {
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
          
        return view('themes.'.$page['theme'].'.index', compact('page', 'posts', 'message', 'maxRows'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function formatResults($posts, $keyword)
    {
        foreach ($posts as $key => $post) {
            $post->search_results = [];

            // Shorten the possible results coming from the raw_content field.
            if (preg_match_all('#.{0,30}'.$keyword.'.{0,30}#i', $post->raw_content, $matches)) {
                $post->search_results = $matches[0];
            }
        }

        return $posts;
    }

    public function autocomplete(Request $request)
    {
        $query = Post::query()->select('title', 'raw_content');
        $collation = Setting::getValue('search', 'collation');
        $maxRows = Setting::getValue('search', 'autocomplete_max_results');

        $query->where(function($query) use($request, $collation) { 
            if (empty($collation)) {
                $query->where('title', 'LIKE', '%'.$request->get('query').'%')
                      ->orWhere('raw_content', 'LIKE', '%'.$request->get('query').'%');
            }
            else {
                $query->whereRaw('posts.title LIKE "%'.addslashes($request->get('query')).'%" COLLATE '.$collation)
                      ->orWhereRaw('posts.raw_content LIKE "%'.addslashes($request->get('query')).'%" COLLATE '.$collation);
            }
        });

        $query = Post::filterQueryByAuth($query);
        $posts = $query->get();
        $data = [];

        foreach ($posts as $key => $post) {
            if (preg_match('#'.$request->get('query').'#i', $post->title)) {
                $data[] = $post->title;
            }

            if (preg_match('#.{0,10}'.$request->get('query').'.{0,10}#i', $post->raw_content, $matches)) {
                // Skip possible duplicates.
                if (in_array($matches[0], $data)) {
                    continue;
                }

                $data[] = $matches[0];
            }

            if ($key > $maxRows - 1) {
                break;
            }
        }

        return response()->json($data);
    }
}
