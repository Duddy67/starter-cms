<?php

namespace App\Support;

use App\Support\PostCollection;
use Illuminate\Database\Eloquent\Collection;


class PostCollection extends Collection
{
    public function filterPostsByCategories(array $categories, array $except = [])
    {
        return $this->filter(function ($post) use($categories, $except) {
            $ids = [];

            foreach ($post->categories as $category) {
                $ids[] = $category->id;
            }

            // Make sure the post belongs to any of the given categories (OR).
            if (!empty(array_intersect($categories, $ids)) && empty(array_intersect($except, $ids))) {
                return $post;
            }
        });
    }

    public function filterPostsByAllCategories(array $categories, array $except = [])
    {
        return $this->filter(function ($post) use($categories, $except) {
            $ids = [];

            foreach ($post->categories as $category) {
                $ids[] = $category->id;
            }

            // Make sure the post belongs to all the given categories (AND).
            if (count(array_intersect($categories, $ids)) == count($categories) && empty(array_intersect($except, $ids))) {
                return $post;
            }
        });
    }

    public function filterPostsById(array $ids)
    {
        return $this->filter(function ($post) use($ids) {
            if (in_array($post->id, $ids)) {
                return $post;
            }
        });
    }
}

