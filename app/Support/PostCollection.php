<?php

namespace App\Support;

use App\Support\PostCollection;
use Illuminate\Database\Eloquent\Collection;


class PostCollection extends Collection
{
    public function filterPostsByCategories(array $categories)
    {
        return $this->filter(function ($post) use($categories) {
            foreach ($post->categories as $category) {
                if (in_array($category->slug, $categories)) {
                    return $post;
                }
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

