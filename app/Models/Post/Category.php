<?php

namespace App\Models\Post;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use App\Models\Post;
use App\Models\Post\Setting as PostSetting;
use App\Models\Post\Ordering as PostOrdering;
use App\Models\Cms\Document;
use Kalnoy\Nestedset\NodeTrait;
use App\Models\User\Group;
use App\Traits\TreeAccessLevel;
use App\Traits\CheckInCheckOut;
use App\Traits\Translatable;
use Illuminate\Http\Request;


class Category extends Model
{
    use HasFactory, NodeTrait, TreeAccessLevel, CheckInCheckOut, Translatable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'post_categories';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'status',
        'owned_by',
        'access_level',
        'parent_id',
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
     * The posts that belong to the category.
     */
    public function posts()
    {
        return $this->belongsToMany(Post::class);
    }

    /**
     * The groups that belong to the category.
     */
    public function groups()
    {
        return $this->belongsToMany(Group::class, 'post_category_group');
    }

    /**
     * The post orderings that belong to the category.
     */
    public function postOrderings()
    {
        return $this->hasMany(Ordering::class)->orderBy('post_order');
    }

    /**
     * Get the image associated with the category.
     */
    public function image()
    {
        return $this->morphOne(Document::class, 'documentable')->where('field', 'image');
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
        PostOrdering::where('category_id', $this->id)->delete();

        if ($this->image) {
            $this->image->delete();
        }

        $this->translations()->delete();

        parent::delete();
    }
    /*
     * Gets the category items as a tree.
     */
    public function getItems(Request $request)
    {
        $search = $request->input('search', null);

        $query = Category::select('post_categories.*', 'users.name as owner_name', 'translations.name as name')
            ->leftJoin('users', 'post_categories.owned_by', '=', 'users.id')
            ->join('translations', function ($join) { 
                $join->on('post_categories.id', '=', 'translatable_id')
                    ->where('translations.translatable_type', '=', Category::class)
                    ->where('locale', '=', config('app.locale'));
        });

        if ($search !== null) {
            $query->where('translations.name', 'like', '%'.$search.'%');
        }

        return $query->defaultOrder()->get()->toTree();
    }

    public static function getItem(int|string $id, string $locale, bool $bySlug = false)
    {
        $query = Category::select('post_categories.*', 'users.name as owner_name', 'users2.name as modifier_name',
                            'translations.name as name', 'translations.slug as slug', 
                            'translations.description as description', 'translations.alt_img as alt_img',
                            'translations.extra_fields as extra_fields', 'translations.meta_data as meta_data')
            ->leftJoin('users', 'post_categories.owned_by', '=', 'users.id')
            ->leftJoin('users as users2', 'post_categories.updated_by', '=', 'users2.id')
            ->join('translations', function ($join) use($id, $locale, $bySlug) { 
                $join->on('post_categories.id', '=', 'translatable_id')
                     ->where('translations.translatable_type', Category::class)
                     ->where('translations.locale', $locale);

                     if ($bySlug) {
                         // id stands for slug.
                         $join->where('translations.slug', $id);
                     }
        });

        return ($bySlug) ? $query->first() : $query->find($id);
    }

    public function getUrl()
    {
        $segments = PostSetting::getSegments();
        return '/'.$segments->category.'/'.$this->id.'/'.$this->slug;
    }

    /*
     * Returns posts without pagination.
     */
    public function getAllPosts(Request $request)
    {
        $query = $this->getQuery($request);
        return $query->get();
    }

    /*
     * Returns filtered and paginated posts.
     */
    public function getPosts(Request $request)
    {
        $perPage = $request->input('per_page', Setting::getValue('pagination', 'per_page'));
        $search = $request->input('search', null);
        $query = $this->getQuery($request);

        if ($search !== null) {
            $query->where('posts.title', 'like', '%'.$search.'%');
        }

        return $query->paginate($perPage);
    }

    /*
     * Builds the Post query.
     */
    private function getQuery(Request $request)
    {
        $locale = ($request->segment(1)) ? $request->segment(1) : config('app.locale');
        $query = Post::query();
        $query->select('posts.*', 'users.name as owner_name',
                                  'translations.title as title',
                                  'translations.slug as slug',
                                  'translations.excerpt as excerpt')
              ->leftJoin('users', 'posts.owned_by', '=', 'users.id');
        // Join the role tables to get the owner's role level.
        $query->join('model_has_roles', 'posts.owned_by', '=', 'model_id')->join('roles', 'roles.id', '=', 'role_id');

        // Get the default locale translation.
        $query->join('translations', function ($join) use($locale) { 
            $join->on('posts.id', '=', 'translatable_id')
                 ->where('translations.translatable_type', '=', 'App\Models\Post')
                 ->where('translations.locale', '=', $locale);
        });

        // Get only the posts related to this category. 
        $query->whereHas('categories', function ($query) {
            $query->where('id', $this->id);
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
                    $query->orWhereHas('groups', function ($query)  use ($groupIds) {
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
        $settings = $this->getSettings();

        if ($settings['post_ordering'] != 'no_ordering') {
            // Extract the ordering name and direction from the setting value.
            preg_match('#^([a-z-0-9_]+)_(asc|desc)$#', $settings['post_ordering'], $ordering);

            // Check for numerical sorting.
            if ($ordering[1] == 'order') {
                $query->join('ordering_category_post', function ($join) use ($ordering) { 
                    $join->on('posts.id', '=', 'post_id')
                         ->where('category_id', '=', $this->id);
                })->orderBy('post_order', $ordering[2]);
            }
            // Regular sorting.
            else {
                $query->orderBy($ordering[1], $ordering[2]);
            }
        }

        return $query;
    }

    public function getParentIdOptions()
    {
        $nodes = Category::select('post_categories.*', 'translations.name as name')
            ->join('translations', function($join) {
                $join->on('post_categories.id', '=', 'translatable_id')
                     ->where('translations.translatable_type', '=', 'App\Models\Post\Category')
                     ->where('locale', '=', config('app.locale'));
        })->get()->toTree();

        $options = [];
        // Defines the state of the current instance.
        $isNew = ($this->id) ? false : true;

        $traverse = function ($categories, $prefix = '-') use (&$traverse, &$options, $isNew) {

            foreach ($categories as $category) {
                if (!$isNew && $this->access_level != 'private') {
                    // A non private category cannot be a private category's children.
                    $extra = ($category->access_level == 'private') ? ['disabled'] : [];
                }
                elseif (!$isNew && $this->access_level == 'private' && $category->access_level == 'private') {
                      // Only the category's owner can access it.
                      $extra = ($category->owned_by == auth()->user()->id) ? [] : ['disabled'];
                }
                elseif ($isNew && $category->access_level == 'private') {
                      // Only the category's owner can access it.
                      $extra = ($category->owned_by == auth()->user()->id) ? [] : ['disabled'];
                }
                else {
                    $extra = [];
                }

                $options[] = ['value' => $category->id, 'text' => $prefix.' '.$category->name, 'extra' => $extra];

                $traverse($category->children, $prefix.'-');
            }
        };

        $traverse($nodes);

        return $options;
    }

    public function getOwnedByOptions()
    {
        $users = auth()->user()->getAssignableUsers(['assistant', 'registered']);
        $options = [];

        foreach ($users as $user) {
            $extra = [];

            // The user is a manager who doesn't or no longer have the create-post-category permission.
            if ($user->getRoleType() == 'manager' && !$user->can('create-post-category')) {
                // The user owns this category.
                // N.B: A new owner will be required when updating this category. 
                if ($this->id && $this->access_level != 'private') {
                    // Don't show this user.
                    continue;
                }

                // If the user owns a private category his name will be shown until the category is no longer private.
            }

            $options[] = ['value' => $user->id, 'text' => $user->name, 'extra' => $extra];
        }

        return $options;
    }

    public function getSettings()
    {
        return PostSetting::getItemSettings($this, 'categories');
    }

    public function getPostOrderingOptions()
    {
        return PostSetting::getPostOrderingOptions();
    }

    /*
     * Generic function that returns model values which are handled by select inputs. 
     */
    public function getSelectedValue($fieldName)
    {
        if ($fieldName == 'groups') {
            return $this->groups->pluck('id')->toArray();
        }

        return $this->{$fieldName};
    }

    public function getExtraFieldByAlias($alias)
    {
        return Setting::getExtraFieldByAlias($this, $alias);
    }
}
