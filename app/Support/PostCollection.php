<?php

namespace App\Support;

use App\Support\PostCollection;
use Illuminate\Database\Eloquent\Collection;


class PostCollection extends Collection
{
    /*
     * Returns posts from a category sorted by the given column.
     *
     * @param integer $id      The id of the category to filter from.
     * @param string  $column  The column by which posts are sorted (Optional).
     * @param boolean $desc    When set to true, posts are sorted in the opposite order (Optional).
     *
     * @return Object  The sorted posts filtered by the given category.
     */
    public function filterPostsByCategory(int $id, string $column = '', bool $desc = false)
    {
        if (!empty($column)) {
            // Sort posts by the given column.
            $posts = ($desc) ? $this->sortByDesc($column) : $this->sortBy($column);
        }
        else {
            $posts = $this;
        }

        return $posts->filter(function ($post) use($id) {
            // Loop through the post's categories.
            foreach ($post->categories as $category) {
                // Check for the given slug.
                if ($category->id == $id) {
                    return $post;
                }
            }
        });
    }

    /*
     * Returns posts filtered by any of the given categories (OR clause).
     *
     * @param array $ids      The id of the categories to filter from.
     * @param array $except   The slug of the categories to ignore (Optional).
     *
     * @return Object  The posts filtered by the given categories.
     */
    public function filterPostsByCategories(array $ids, array $except = [])
    {
        return $this->filter(function ($post) use($ids, $except) {
            $results = [];

            foreach ($post->categories as $category) {
                // Store the id of the categories the post belongs to.
                $results[] = $category->id;
            }

            // Make sure the post belongs to any of the given categories (OR).
            if (!empty(array_intersect($ids, $results)) && empty(array_intersect($except, $results))) {
                return $post;
            }
        });
    }

    /*
     * Returns posts filtered by all of the given categories (AND clause).
     *
     * @param array $ids      The id of the categories to filter from.
     * @param array $except   The slug of the categories to ignore (Optional).
     *
     * @return Object  The posts filtered by the given categories.
     */
    public function filterPostsByAllCategories(array $ids, array $except = [])
    {
        return $this->filter(function ($post) use($ids, $except) {
            $results = [];

            foreach ($post->categories as $category) {
                // Store the id of the categories the post belongs to.
                $results[] = $category->id;
            }

            // Make sure the post belongs to all the given categories (AND).
            if (count(array_intersect($ids, $results)) == count($ids) && empty(array_intersect($except, $results))) {
                return $post;
            }
        });
    }

    /*
     * Returns posts filtered by id and sorted by the given column.
     *
     * @param array   $ids     The id of the posts to filter.
     * @param string  $column  The column by which posts are sorted (Optional).
     * @param boolean $desc    When set to true, posts are sorted in the opposite order (Optional).
     *
     * @return Object  The filtered posts.
     */
    public function filterPostsById(array $ids, string $column = '', bool $desc = false)
    {
        if (!empty($column)) {
            // Sort posts by the given column.
            $posts = ($desc) ? $this->sortByDesc($column) : $this->sortBy($column);
        }
        else {
            $posts = $this;
        }

        return $posts->filter(function ($post) use($ids) {
            if (in_array($post->id, $ids)) {
                return $post;
            }
        });
    }
}

