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
use App\Models\User\Group;
use App\Traits\TreeAccessLevel;
use App\Traits\CheckInCheckOut;
use App\Traits\Translatable;
use App\Traits\OptionList;
use Illuminate\Http\Request;


class Category extends Model
{
    use HasFactory, Node, TreeAccessLevel, CheckInCheckOut, Translatable, OptionList;

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
        'settings' => 'array',
        'meta_data' => 'array',
        'extra_fields' => 'array'
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
        $this->translations()->delete();
        $this->posts()->detach();
        $this->groups()->detach();

        parent::delete();
    }

    /*
     * Gets the category items as a tree.
     */
    public static function getCategories(Request $request, string $collectionType)
    {
        $search = $request->input('search', null);

        $query = Category::select('categories.*', 'users.name as owner_name', 'translations.name as name')
            ->leftJoin('users', 'categories.owned_by', '=', 'users.id')
            ->join('translations', function ($join) { 
                $join->on('categories.id', '=', 'translatable_id')
                    ->where('translations.translatable_type', '=', Category::class)
                    ->where('locale', '=', config('app.locale'));
        })->where('categories.collection_type', $collectionType);

        if ($search !== null) {
            // No tree for search results.
            return $query->where('translations.name', 'like', '%'.$search.'%')->defaultOrder()->get();
        }

        return $query->defaultOrder()->get()->toTree();
    }

    public static function getCategory(int|string $id, string $collectionType, string $locale)
    {
        // Check if the $id variable is passed as a slug value (used on front-end).
        $slug = (is_string($id)) ? true : false;

        $query = Category::selectRaw('categories.*, users.name as owner_name, users2.name as modifier_name,'.
                                     Category::getFallbackCoalesce(['name', 'slug', 'description',
                                                                        'alt_img', 'extra_fields', 'meta_data']))
            ->leftJoin('users', 'categories.owned_by', '=', 'users.id')
            ->leftJoin('users as users2', 'categories.updated_by', '=', 'users2.id')
            ->leftJoin('translations AS locale', function ($join) use($id, $locale, $slug) { 
                $join->on('categories.id', '=', 'locale.translatable_id')
                     ->where('locale.translatable_type', Category::class)
                     ->where('locale.locale', $locale);
        // Switch to the fallback locale in case locale is not found, (used on front-end).
        })->leftJoin('translations AS fallback', function ($join) use($id, $slug) {
              $join->on('categories.id', '=', 'fallback.translatable_id')
                   ->where('fallback.translatable_type', Category::class)
                   ->where('fallback.locale', config('app.fallback_locale'));
        });

        if ($slug) {
            $query->where(function($query) use($id) { 
                $query->where('locale.slug', $id)->orWhere('fallback.slug', $id);
            });
        }

        $query->where('categories.collection_type', $collectionType);

        return ($slug) ? $query->first() : $query->find($id);
    }

    public function getUrl($slug = null)
    {
        $slug = ($slug) ? $slug : $this->slug;

        $segments = Setting::getSegments(ucfirst($this->collection_type));
        return '/'.$segments['categories'].'/'.$this->id.'/'.$slug;
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

    public function getItemsPerPageOptions(): array
    {
        // Invoke the getItemsPerPageOptions function shared by all the categorizable item models.
        return $this->categorizableTypes[$this->collection_type.'_setting']::getItemsPerPageOptions();
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
