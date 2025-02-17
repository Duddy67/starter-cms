<?php

namespace App\Support;

use App\Support\PostCollection;
use Illuminate\Database\Eloquent\Collection;


class PostCollection extends Collection
{
    /*
     * Returns posts from a category sorted by the given column.
     *
     * @param string  $slug    The slug of the category to filter from.
     * @param string  $column  The column by which posts are sorted (Optional).
     * @param boolean $desc    When set to true, posts are sorted in the opposite order (Optional).
     *
     * @return Object  The sorted posts filtered by the given category.
     */
    public function filterPostsByCategory(string $slug, string $column = '', bool $desc = false)
    {
        if (!empty($column)) {
            // Sort posts by the given column.
            $posts = ($desc) ? $this->sortByDesc($column) : $this->sortBy($column);
        }
        else {
            $posts = $this;
        }

        return $posts->filter(function ($post) use($slug) {
            // Loop through the post's categories.
            foreach ($post->categories as $category) {
                // Check for the given slug.
                if ($category->slug == $slug) {
                    return $post;
                }
            }
        });
    }

    /*
     * Returns posts filtered by any of the given categories (OR clause).
     *
     * @param array $slugs    The slug of the categories to filter from.
     * @param array $except   The slug of the categories to ignore (Optional).
     *
     * @return Object  The posts filtered by the given categories.
     */
    public function filterPostsByCategories(array $slugs, array $except = [])
    {
        return $this->filter(function ($post) use($slugs, $except) {
            $results = [];

            foreach ($post->categories as $category) {
                // Store the slug of the categories the post belongs to.
                $results[] = $category->slug;
            }

            // Make sure the post belongs to any of the given categories (OR).
            if (!empty(array_intersect($slugs, $results)) && empty(array_intersect($except, $results))) {
                return $post;
            }
        });
    }

    /*
     * Returns posts filtered by all of the given categories (AND clause).
     *
     * @param array $slugs    The slug of the categories to filter from.
     * @param array $except   The slug of the categories to ignore (Optional).
     *
     * @return Object  The posts filtered by the given categories.
     */
    public function filterPostsByAllCategories(array $slugs, array $except = [])
    {
        return $this->filter(function ($post) use($slugs, $except) {
            $results = [];

            foreach ($post->categories as $category) {
                // Store the slug of the categories the post belongs to.
                $results[] = $category->slug;
            }

            // Make sure the post belongs to all the given categories (AND).
            if (count(array_intersect($slugs, $results)) == count($slugs) && empty(array_intersect($except, $results))) {
                return $post;
            }
        });
    }

    /*
     * Returns posts filtered by slug and sorted by the given column.
     *
     * @param array   $slugs   The slug of the posts to filter.
     * @param string  $column  The column by which posts are sorted (Optional).
     * @param boolean $desc    When set to true, posts are sorted in the opposite order (Optional).
     *
     * @return Object  The filtered posts.
     */
    public function filterPostsBySlug(array $slugs, string $column = '', bool $desc = false)
    {
        if (!empty($column)) {
            // Sort posts by the given column.
            $posts = ($desc) ? $this->sortByDesc($column) : $this->sortBy($column);
        }
        else {
            $posts = $this;
        }

        return $posts->filter(function ($post) use($slugs) {
            if (in_array($post->slug, $slugs)) {
                return $post;
            }
        });
    }
}

