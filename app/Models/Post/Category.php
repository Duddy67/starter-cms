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
use Illuminate\Http\Request;


class Category extends Model
{
    use HasFactory, NodeTrait, TreeAccessLevel, CheckInCheckOut;

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
        'name',
        'slug',
        'status',
        'owned_by',
        'description',
        'access_level',
        'parent_id',
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

        $this->posts()->detach();

        parent::delete();
    }
    /*
     * Gets the category items as a tree.
     */
    public function getItems(Request $request)
    {
        $search = $request->input('search', null);

        if ($search !== null) {
            return Category::where('name', 'like', '%'.$search.'%')->get();
        }
        else {
            return Category::select('post_categories.*', 'users.name as owner_name')->leftJoin('users', 'post_categories.owned_by', '=', 'users.id')->defaultOrder()->get()->toTree();
        }
    }

    public function getUrl()
    {
        $segments = Setting::getSegments('Post');
        return '/'.$segments['category'].'/'.$this->id.'/'.$this->slug;
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
        $query = Post::query();
        $query->select('posts.*', 'users.name as owner_name')->leftJoin('users', 'posts.owned_by', '=', 'users.id');
        // Join the role tables to get the owner's role level.
        $query->join('model_has_roles', 'posts.owned_by', '=', 'model_id')->join('roles', 'roles.id', '=', 'role_id');

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
        $nodes = Category::get()->toTree();
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

    /*
     *  Overwrite the deleteDescendants() NodeTrait function.
     *
     *  Delete the node's descendants leaf node by leaf node in order to use the delete
     *  method of the model.
     *
     *  Note:
     *  The NodeTrait uses the statement: $node->descendants()->delete(). However, descendants()
     *  returns a QueryBuilder object and the delete() statement delete all related elements using
     *  a QueryBuilder delete statement.
     *  Given it's not an Eloquent statement it doesn't trigger any Eloquent events and thus the 
     *  delete() method of the model is not called.
     *
     *  https://github.com/lazychaser/laravel-nestedset/issues/568
     *  https://laracasts.com/discuss/channels/eloquent/is-it-possible-override-the-delete-method
     */
    public function deleteDescendants()
    {
        $leaves = Category::whereDescendantOf($this)->whereIsLeaf()->get();

        while ($leaves->isNotEmpty()) {
            foreach ($leaves as $leaf) {
                $leaf->delete();
            }

            $leaves = Category::whereDescendantOf($this)->whereIsLeaf()->get();
        }
    }
}
