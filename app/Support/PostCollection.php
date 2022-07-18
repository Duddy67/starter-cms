<?php

namespace App\Support;

use App\Support\PostCollection;
use Illuminate\Database\Eloquent\Collection;


class PostCollection extends Collection
{
    public function filterPostsByCategories(array $categories, array $except = [])
    {
        return $this->filter(function ($post) use($categories, $except) {
            $slugs = [];

            foreach ($post->categories as $category) {
                $slugs[] = $category->slug;
            }

            // Make sure the post belongs to any of the given categories (OR).
            if (!empty(array_intersect($categories, $slugs)) && empty(array_intersect($except, $slugs))) {
                return $post;
            }
        });
    }

    public function filterPostsByAllCategories(array $categories, array $except = [])
    {
        return $this->filter(function ($post) use($categories, $except) {
            $slugs = [];

            foreach ($post->categories as $category) {
                $slugs[] = $category->slug;
            }

            // Make sure the post belongs to all the given categories (AND).
            if (count(array_intersect($categories, $slugs)) == count($categories) && empty(array_intersect($except, $slugs))) {
                return $post;
            }
        });
    }

    public function filterPostsBySlug(array $slugs)
    {
        return $this->filter(function ($post) use($slugs) {
            if (in_array($post->slug, $slugs)) {
                return $post;
            }
        });
    }
}

