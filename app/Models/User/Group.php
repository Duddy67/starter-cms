<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User\User;
use App\Models\Blog\Post;
use App\Models\Blog\Category;
use App\Models\Menu\Menu;
use App\Models\Settings\General;
use App\Traits\Admin\AccessLevel;
use App\Traits\Admin\CheckInCheckOut;


class Group extends Model
{
    use HasFactory, AccessLevel, CheckInCheckOut;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'owned_by',
        'description',
        'access_level',
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
     * The users that belong to the group.
     */
    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * The posts that belong to the group.
     */
    public function posts()
    {
        return $this->belongsToMany(Post::class);
    }

    /**
     * The categories that belong to the group.
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'blog_category_group');
    }

    /**
     * The menus that belong to the group.
     */
    public function menus()
    {
        return $this->belongsToMany(Menu::class);
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
        $this->users()->detach();
        parent::delete();
    }

    /*
     * Gets the group items according to the filter, sort and pagination settings.
     */
    public function getItems($request)
    {
        $perPage = $request->input('per_page', General::getValue('pagination', 'per_page'));
        $search = $request->input('search', null);
        $sortedBy = $request->input('sorted_by', null);
        $ownedBy = $request->input('owned_by', null);
        $permission = $request->input('permission', null);

        $query = Group::query();
        $query->select('groups.*', 'users.name as owner_name')->leftJoin('users', 'groups.owned_by', '=', 'users.id');
        // Join the role tables to get the owner's role level.
        $query->join('model_has_roles', 'groups.owned_by', '=', 'model_id')->join('roles', 'roles.id', '=', 'role_id');

        if ($search !== null) {
            $query->where('groups.name', 'like', '%'.$search.'%');
        }

        if ($sortedBy !== null) {
            preg_match('#^([a-z0-9_]+)_(asc|desc)$#', $sortedBy, $matches);
            $query->orderBy($matches[1], $matches[2]);
        }

        if ($ownedBy !== null) {
            $query->whereIn('groups.owned_by', $ownedBy);
        }

        if ($permission !== null) {
            $query->where('groups.permission', $permission);
        }

        $query->where(function($query) {
            $query->where('roles.role_level', '<', auth()->user()->getRoleLevel())
                  ->orWhereIn('groups.access_level', ['public_ro', 'public_rw'])
                  ->orWhere('groups.owned_by', auth()->user()->id);
        });

        return $query->paginate($perPage);
    }

    public function getOwnedByOptions()
    {
        $users = auth()->user()->getAssignableUsers(['assistant', 'registered']);
        $options = [];

        foreach ($users as $user) {
            $options[] = ['value' => $user->id, 'text' => $user->name];
        }

        return $options;
    }

    public function getPermissionOptions()
    {
        return [
            ['value' => 'read_only', 'text' => __('labels.generic.read_only')],
            ['value' => 'read_write', 'text' => __('labels.generic.read_write')],
        ];
    }

    /*
     * Generic function that returns model values which are handled by select inputs. 
     */
    public function getSelectedValue($fieldName)
    {
        return $this->{$fieldName};
    }

    /*
     * Checks whether the given item has any private groups that the current user
     * is not allowed to add or remove. 
     *
     * @return array
     */
    public static function getPrivateGroups($item)
    {
        return $item->groups()->join('model_has_roles', 'groups.owned_by', '=', 'model_id')
                              ->join('roles', 'roles.id', '=', 'role_id')
                              ->where([
                                          ['groups.access_level', '=', 'private'], 
                                          ['roles.role_level', '>=', auth()->user()->getRoleLevel()],
                                          ['groups.owned_by', '!=', auth()->user()->id]
                                      ])->pluck('groups.id')->toArray();
    }
}
