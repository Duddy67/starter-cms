<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Models\Cms\Setting;
use App\Models\Cms\Category;
use App\Models\Cms\Order;
use App\Models\User\Group;
use App\Traits\AccessLevel;
use App\Traits\CheckInCheckOut;
use App\Traits\OptionList;
use App\Models\Cms\Document;
use App\Models\Cms\LayoutItem;
use App\Models\Cms\Comment;
use App\Support\PostCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class Post extends Model
{
    use HasFactory, AccessLevel, CheckInCheckOut, OptionList;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'slug',
        'status',
        'owned_by',
        'main_cat_id',
        'content',
        'excerpt',
        'alt_img',
        'page',
        'access_level',
        'extra_fields',
        'meta_data',
        'settings',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'checked_out_time'
    ];

    /**
     * The attributes that should be casted.
     *
     * @var array
     */
    protected $casts = [
        'extra_fields' => 'array',
        'meta_data' => 'array',
        'settings' => 'array'
    ];

    /**
     * The extra group fields.
     *
     * @var array
     */
    public $fieldGroups = [
        'meta_data',
        'extra_fields'
    ];

    /**
     * The categories that belong to the post.
     */
    public function categories(): MorphToMany
    {
        return $this->morphToMany(Category::class, 'categorizable')->where('collection_type', 'post');
    }

    /**
     * The groups that belong to the post.
     */
    public function groups()
    {
        return $this->belongsToMany(Group::class);
    }

    /**
     * Get the image associated with the post.
     */
    public function image()
    {
        return $this->morphOne(Document::class, 'documentable')->where('field', 'image');
    }

    /**
     * The orderings that belong to the post.
     */
    public function orders(): MorphMany
    {
        return $this->morphMany(Order::class, 'orderable');
    }

    /**
     * Get all of the post's layout items.
     */
    public function layoutItems(): MorphMany
    {
        return $this->morphMany(LayoutItem::class, 'layout_itemable')->orderBy('order');
    }

    /**
     * Get the comments for the post.
     */
    public function comments(): MorphMany
    {
        // Returns the post comments in ascending order (oldest on top).
        return $this->morphMany(Comment::class, 'commentable')
                    ->leftJoin('users', 'users.id', '=', 'comments.owned_by')
                    ->select('comments.*', 'users.name AS author')
                    ->orderBy('comments.created_at', 'asc');
    }

    /**
     * Delete the model from the database (override).
     *
     * @return bool|null
     *
     * @throws \LogicException
     */
    public function delete()
    {
        $this->categories()->detach();
        $this->groups()->detach();
        $this->orders()->delete();
        $this->comments()->delete();
        $this->image()->delete();

        // Delete layout items one by one or the Document relationship in 
        // the image item type won't be deleted. 
        foreach ($this->layoutItems as $item) {
            $item->delete();
        }

        parent::delete();
    }

    /**
     * Create a new Eloquent Collection instance.
     *
     * @param  array  $models
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function newCollection(array $models = [])
    {
        return new PostCollection($models);
    }

    /*
     * Gets the post items according to the filter, sort and pagination settings.
     */
    public static function getPosts(Request $request)
    {
        $perPage = $request->input('per_page', Setting::getValue('pagination', 'per_page'));
        $search = $request->input('search', null);
        $sortedBy = $request->input('sorted_by', null);
        $ownedBy = $request->input('owned_by', null);
        $groups = $request->input('groups', []);
        $categories = $request->input('categories', []);

        $query = Post::query();
        $query->select('posts.*', 'users.name as owner_name')->leftJoin('users', 'posts.owned_by', '=', 'users.id');
        // Join the role tables to get the owner's role level.
        $query->join('model_has_roles', 'posts.owned_by', '=', 'model_id')->join('roles', 'roles.id', '=', 'role_id');

        if ($search !== null) {
            $query->where('posts.title', 'like', '%'.$search.'%');
        }

        if ($sortedBy !== null) {
            // Separate name and direction.
            preg_match('#^([a-z0-9_]+)_(asc|desc)$#', $sortedBy, $matches);

            // Check for numerical sorting.
            if (Setting::canOrderBy('categories', Post::getOrderByExcludedFilters()) && Setting::isSortedByOrder()) {
                $query->join('orders', function ($join) use($categories) { 
                    $join->on('posts.id', '=', 'orderable_id')
                         ->where('orderable_type', '=', Post::class)
                         ->where('category_id', '=', $categories[0]);
                })->orderBy('item_order', $matches[2]);
            }
            // Regular sorting.
            elseif ($matches[1] != 'order') {
                $query->orderBy($matches[1], $matches[2]);
            }
        }

        // Filter by owners
        if ($ownedBy !== null) {
            $query->whereIn('posts.owned_by', $ownedBy);
        }

        // Filter by groups
        if (!empty($groups)) {
            $query->whereHas('groups', function($query) use($groups) {
                $query->whereIn('id', $groups);
            });
        }

        // Filter by categories
        if (!empty($categories)) {
            $query->whereHas('categories', function($query) use($categories) {
                $query->whereIn('id', $categories);
            });
        }


        // N.B: Put the following part of the query into brackets.
        $query->where(function($query) {

            // Check for access levels.
            $query->where(function($query) {
                $query->where('roles.role_level', '<', auth()->user()->getRoleLevel())
                      ->orWhereIn('posts.access_level', ['public_ro', 'public_rw'])
                      ->orWhere('posts.owned_by', auth()->user()->id);
            });

            $groupIds = auth()->user()->getGroupIds();

            if (!empty($groupIds)) {
                // Check for access through groups.
                $query->orWhereHas('groups', function ($query)  use ($groupIds) {
                    $query->whereIn('id', $groupIds);
                });
            }
        });

        return $query->paginate($perPage);
    }

    public function getUrl()
    {
        $segments = Setting::getSegments('Post');
        return '/'.$segments['posts'].'/'.$this->id.'/'.$this->slug;
    }

    public function getSettings()
    {
        return Setting::getItemSettings($this, 'posts');
    }

    /*
     * Returns the filters that cannot be used with numerical order.
     */
    public static function getOrderByExcludedFilters()
    {
        return ['owned_by', 'groups', 'search'];
    }

    /*
     * Generic function that returns model values which are handled by select inputs. 
     */
    public function getSelectedValue(\stdClass $field): mixed
    {
        if ($field->name == 'groups') {
            return $this->groups->pluck('id')->toArray();
        }

        if ($field->name == 'categories') {
            return $this->categories->pluck('id')->toArray();
        }

        if (isset($field->group) && $field->group == 'settings') {
            return (isset($this->settings[$field->name])) ? $this->settings[$field->name] : null;
        }

        return $this->{$field->name};
    }

    public function getPrivateCategories()
    {
        return $this->categories()->where([
            ['categories.collection_type', '=', 'post'], 
            ['categories.access_level', '=', 'private'], 
            ['categories.owned_by', '!=', auth()->user()->id]
        ])->pluck('categories.id')->toArray();
    }

    public function getExtraFieldByAlias($alias)
    {
        return Setting::getExtraFieldByAlias($this, $alias);
    }

    public function getMainCategory()
    {
        return $this->categories()->where('id', $this->main_cat_id)->first();
    }

    public function getLayoutRawContent()
    {
        $rawContent = '';

        foreach ($this->layoutItems as $item) {
            if ($item->type == 'title' || $item->type == 'text_block') {
                $rawContent .= strip_tags($item->text.' ');
            }
        }

        return $rawContent;
    }

    /*
     * Returns the posts that belong to the given category.
     */
    public static function getCategoryItems(Request $request, Category $category, array $options = [])
    {
        $query = Post::query();
        $query->select('posts.*', 'users.name as owner_name')->leftJoin('users', 'posts.owned_by', '=', 'users.id');
        // Join the role tables to get the owner's role level.
        $query->join('model_has_roles', 'posts.owned_by', '=', 'model_id')->join('roles', 'roles.id', '=', 'role_id');

        // Get only the posts related to this category. 
        $query->whereHas('categories', function($query) use($category) {
            $query->where('id', $category->id);
        });

        if (Auth::check()) {

            // N.B: Put the following part of the query into brackets.
            $query->where(function($query) {

                // Check for access levels.
                $query->where(function($query) {
                    $query->where('roles.role_level', '<', auth()->user()->getRoleLevel())
                          ->orWhereIn('posts.access_level', ['public_ro', 'public_rw'])
                          ->orWhere('posts.owned_by', auth()->user()->id);
                });

                $groupIds = auth()->user()->getGroupIds();

                if (!empty($groupIds)) {
                    // Check for access through groups.
                    $query->orWhereHas('groups', function($query)  use($groupIds) {
                        $query->whereIn('id', $groupIds);
                    });
                }
            });
        }
        else {
            $query->whereIn('posts.access_level', ['public_ro', 'public_rw']);
        }
 
        // Do not show unpublished posts on front-end.
        $query->where('posts.status', 'published');

        // Set post ordering.
        $settings = $category->getSettings();

        if ($settings['post_ordering'] != 'no_ordering') {
            // Extract the ordering name and direction from the setting value.
            preg_match('#^([a-z-0-9_]+)_(asc|desc)$#', $settings['post_ordering'], $ordering);

            // Check for numerical sorting.
            if ($ordering[1] == 'order') {
                $query->join('orders', function($join) use($ordering, $category) { 
                    $join->on('posts.id', '=', 'orderable_id')
                         ->where('orderable_type', '=', Post::class)
                         ->where('category_id', '=', $category->id);
                })->orderBy('item_order', $ordering[2]);
            }
            // Regular sorting.
            else {
                $query->orderBy($ordering[1], $ordering[2]);
            }
        }

        $search = $request->input('search', null);

        if ($search !== null) {
            $query->where('posts.title', 'like', '%'.$search.'%');
        }

        if (in_array('pagination', $options)) {
            $perPage = $request->input('per_page', Setting::getValue('pagination', 'per_page'));

            return $query->paginate($perPage);
        }

        return $query->get();
    }

    public static function filterQueryByAuth($query)
    {
        // Join the role tables to get the owner's role level.
        $query->join('model_has_roles', 'posts.owned_by', '=', 'model_id')
              ->join('roles', 'roles.id', '=', 'role_id');

        if (Auth::check()) {
            // N.B: Put the following part of the query into brackets.
            $query->where(function($query) {

                // Check for access levels.
                $query->where(function($query) {
                    $query->where('roles.role_level', '<', auth()->user()->getRoleLevel())
                          ->orWhereIn('posts.access_level', ['public_ro', 'public_rw'])
                          ->orWhere('posts.owned_by', auth()->user()->id);
                });

                $groupIds = auth()->user()->getGroupIds();

                if (!empty($groupIds)) {
                    // Check for access through groups.
                    $query->orWhereHas('groups', function($query)  use($groupIds) {
                        $query->whereIn('id', $groupIds);
                    });
                }
            });
        }
        else {
            $query->whereIn('posts.access_level', ['public_ro', 'public_rw']);
        }

        // Do not search unpublished posts.
        $query->where('posts.status', 'published');

        return $query;
    }

    public static function searchInPosts($keyword)
    {
        $query = Post::query()->select('posts.*', 'users.name as owner_name')
                              ->leftJoin('users', 'posts.owned_by', '=', 'users.id');

        $query = self::filterQueryByAuth($query);
        $collation = Setting::getValue('search', 'collation');

        $query->where(function($query) use($keyword, $collation) {
            if (empty($collation)) {
                $query->where('title', 'LIKE', '%'.$keyword.'%')
                      ->orWhere('raw_content', 'LIKE', '%'.$keyword.'%');
            }
            else {
                $query->whereRaw('posts.title LIKE "%'.addslashes($keyword).'%" COLLATE '.$collation)
                      ->orWhereRaw('posts.raw_content LIKE "%'.addslashes($keyword).'%" COLLATE '.$collation);
            }
        });

        return $query;
    }
}
