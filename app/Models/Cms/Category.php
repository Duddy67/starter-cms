<?php

namespace App\Models\Cms;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use App\Models\Cms\Setting;
use Illuminate\Support\Facades\Auth;
use App\Models\Post;
use App\Models\Post\Setting as PostSetting;
use App\Models\Cms\Order;
use App\Models\Cms\Document;
use App\Traits\Node;
use App\Traits\OptionList;
use App\Models\User\Group;
use App\Traits\TreeAccessLevel;
use App\Traits\CheckInCheckOut;
use Illuminate\Http\Request;


class Category extends Model
{
    use HasFactory, Node, TreeAccessLevel, CheckInCheckOut, OptionList;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'categories';

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
        'alt_img',
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
     * The types of the categorizable models.
     *
     * @var array
     */
    protected $categorizableTypes = [
        'post' => Post::class,
        'post_setting' => PostSetting::class,
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
     * Get all of the posts that are assigned this category.
     */
    public function posts(): MorphToMany
    {
        return $this->morphedByMany(Post::class, 'categorizable');
    }

    /**
     * The groups that belong to the category.
     */
    public function groups()
    {
        return $this->belongsToMany(Group::class, 'category_group');
    }

    /**
     * The item orders that belong to the category.
     */
    public function orders()
    {
        return $this->hasMany(Order::class)->orderBy('item_order');
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
        $this->orders()->delete();
        $this->image()->delete();
        $this->posts()->detach();
        $this->groups()->detach();

        parent::delete();
    }
    /*
     * Gets the category items as a tree.
     */
    public static function getCategories(Request $request, string $collectionType)
    {
        $query = Category::query();
        $query->select('categories.*', 'users.name as owner_name')
              ->leftJoin('users', 'categories.owned_by', '=', 'users.id')
              ->where('collection_type', $collectionType);

        if ($search = $request->input('search', null)) {
            // No tree display while searching.
            return $query->where('categories.name', 'like', '%'.$search.'%')->get();
        }

        return $query->defaultOrder()->get()->toTree();
    }

    public function getUrl()
    {
        $segments = Setting::getSegments(ucfirst($this->collection_type));
        return '/'.$segments['categories'].'/'.$this->id.'/'.$this->slug;
    }

    /*
     * Returns the collection of the categorizable items contained into this category.
     */
    public function getItemCollection(Request $request, array $options = [])
    {
        // Invoke the getCategoryItems function shared by all the categorizable item models.
        return $this->categorizableTypes[$this->collection_type]::getCategoryItems($request, $this, $options);
    }

    public function getOwnedByOptions()
    {
        $users = auth()->user()->getAssignableUsers(['assistant', 'registered']);
        $options = [];

        foreach ($users as $user) {
            $extra = [];

            // The user is a manager who doesn't or no longer have the create-post-category permission.
            if ($user->getRoleType() == 'manager' && !$user->can('create-'.$this->collection_type.'-category')) {
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

    public function getItemOrderingOptions(): array
    {
        // Invoke the getItemOrderingOptions function shared by all the categorizable item models.
        return $this->categorizableTypes[$this->collection_type.'_setting']::getItemOrderingOptions();
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
