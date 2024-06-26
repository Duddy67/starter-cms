<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Cms\Setting;
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
                $query = Post::searchInPosts($request->input('keyword'));
                // Get all of the results or the paginated result according to the $perPage value.
                $posts = ($perPage == -1) ? $query->paginate($query->count()) : $query->paginate($perPage);
                $posts = $this->formatResults($posts, $request->input('keyword'));

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

            if (count($data) > $maxRows - 1) {
                break;
            }

            if (preg_match('#.{0,10}'.$request->get('query').'.{0,10}#i', $post->raw_content, $matches)) {
                // Skip possible duplicates.
                if (in_array($matches[0], $data)) {
                    continue;
                }

                foreach ($matches as $match) {
                    $data[] = $match;

                    if (count($data) > $maxRows - 1) {
                        break 2;
                    }
                }
            }
        }

        return response()->json($data);
    }
}
