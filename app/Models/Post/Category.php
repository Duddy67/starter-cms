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
use App\Traits\Node;
use App\Models\User\Group;
use App\Traits\TreeAccessLevel;
use App\Traits\CheckInCheckOut;
use App\Traits\Translatable;
use Illuminate\Http\Request;


class Category extends Model
{
    use HasFactory, Node, TreeAccessLevel, CheckInCheckOut, Translatable;

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
        $this->posts()->detach();

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

    public static function getItem(int|string $id, string $locale)
    {
        // Check if the $id variable is passed as a slug value (used on front-end).
        $slug = (is_string($id)) ? true : false;

        $query = Category::selectRaw('post_categories.*, users.name as owner_name, users2.name as modifier_name,'.
                                     Category::getFallbackCoalesce(['name', 'slug', 'description',
                                                                        'alt_img', 'extra_fields', 'meta_data']))
            ->leftJoin('users', 'post_categories.owned_by', '=', 'users.id')
            ->leftJoin('users as users2', 'post_categories.updated_by', '=', 'users2.id')
            ->leftJoin('translations AS locale', function ($join) use($id, $locale, $slug) { 
                $join->on('post_categories.id', '=', 'locale.translatable_id')
                     ->where('locale.translatable_type', Category::class)
                     ->where('locale.locale', $locale);
        // Switch to the fallback locale in case locale is not found, (used on front-end).
        })->leftJoin('translations AS fallback', function ($join) use($id, $slug) {
              $join->on('post_categories.id', '=', 'fallback.translatable_id')
                   ->where('fallback.translatable_type', Category::class)
                   ->where('fallback.locale', config('app.fallback_locale'));
        });

        if ($slug) {
            $query->where('locale.slug', $id)->orWhere('fallback.slug', $id);
        }

        return ($slug) ? $query->first() : $query->find($id);
    }

    public function getUrl($slug = null)
    {
        $slug = ($slug) ? $slug : $this->slug;

        $segments = Setting::getSegments('Post');
        return '/'.$segments['categories'].'/'.$this->id.'/'.$slug;
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
            $query->where('locale.title', 'like', '%'.$search.'%');
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
        $query->selectRaw('posts.*, users.name as owner_name,'.Category::getFallbackCoalesce(['title', 'slug', 'excerpt', 'alt_img']))
              ->leftJoin('users', 'posts.owned_by', '=', 'users.id');
        // Join the role tables to get the owner's role level.
        $query->join('model_has_roles', 'posts.owned_by', '=', 'model_id')->join('roles', 'roles.id', '=', 'role_id');

        $query->leftJoin('translations AS locale', function ($join) use($locale) { 
            $join->on('posts.id', '=', 'locale.translatable_id')
                 ->where('locale.translatable_type', Post::class)
                 ->where('locale.locale', $locale);
        });
        // Switch to the fallback locale in case locale is not found.
        $query->leftJoin('translations AS fallback', function ($join) { 
            $join->on('posts.id', '=', 'fallback.translatable_id')
                 ->where('fallback.translatable_type', Post::class)
                 ->where('fallback.locale', config('app.fallback_locale'));
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
        return Setting::getItemSettings($this, 'categories');
    }

    public function getPostOrderingOptions()
    {
        return PostSetting::getPostOrderingOptions();
    }

    /*
     * Generic function that returns model values which are handled by select inputs. 
     */
    public function getSelectedValue(\stdClass $field): mixed
    {
        if ($field->name == 'groups') {
            return $this->groups->pluck('id')->toArray();
        }

        if (isset($field->group) && $field->group == 'settings') {
            return (isset($this->settings[$field->name])) ? $this->settings[$field->name] : null;
        }

        return $this->{$field->name};
    }

    public function getExtraFieldByAlias($alias)
    {
        return Setting::getExtraFieldByAlias($this, $alias);
    }
}
