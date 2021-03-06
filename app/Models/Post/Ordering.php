<?php

namespace App\Models\Post;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use App\Models\Post;
use App\Models\Post\Category;

class Ordering extends Model implements Sortable
{
    use HasFactory, SortableTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ordering_category_post';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'post_id',
        'category_id',
        'title',
    ];

    public $sortable = [
        'order_column_name' => 'post_order',
        'sort_when_creating' => true,
    ];

    /**
     * Get the post that owns the ordering.
     */
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Get the category that owns the ordering.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Synchronizes the post orderings according to the linked categories.
     */
    public static function sync($post, $categories)
    {
        // Get the previous categories linked to the post.
        $olds = $post->orderings->pluck('category_id')->toArray();

        foreach ($categories as $category) {
            if (!in_array($category, $olds)) {
                $ordering = Ordering::create(['post_id' => $post->id, 'category_id' => $category, 'title' => $post->title]);
                $ordering->save();
            }
        }

	// Delete the previous categories that are no longer linked to the post.
        $olds = array_diff($olds, $categories);

        foreach ($olds as $old) {
            Ordering::where(['post_id' => $post->id, 'category_id' => $old])->delete();

            $category = Category::find($old);

            // Reorder the other posts in the category.
            foreach ($category->postOrderings as $i => $ordering) {
                $ordering->post_order = $i + 1;
                $ordering->save();
            }
        }
    }

    /**
     * Filters by the category_id field so that Spatie methods take it into account.
     */
    public function buildSortQuery()
    {
        return static::query()->where('category_id', $this->category_id);
    }
}
