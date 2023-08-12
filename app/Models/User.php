<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use App\Models\User\Role;
use App\Models\User\Group;
use App\Models\Cms\Document;
use App\Models\Cms\Setting;
use App\Traits\CheckInCheckOut;
use Illuminate\Http\Request;
use Illuminate\Support\Str;


class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, CheckInCheckOut;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'last_logged_in_at',
        'last_logged_in_ip',
        'last_seen_at',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'checked_out_time',
        'last_logged_in_at',
        'last_seen_at',
    ];


    /**
     * The groups that belong to the user.
     */
    public function groups()
    {
        return $this->belongsToMany(Group::class);
    }

    /**
     * The group ids the user is in.
     *
     * @return array
     */
    public function getGroupIds()
    {
        return $this->groups()->pluck('groups.id')->toArray();
    }

    /**
     * The group ids with read/write permission the user is in.
     *
     * @return array
     */
    public function getReadWriteGroupIds()
    {
        return $this->groups()->where('permission', 'read_write')->pluck('groups.id')->toArray();
    }

    /**
     * The user's documents.
     */
    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable')->where('field', 'file_manager');
    }

    /**
     * The users's photo.
     */
    public function photo()
    {
        return $this->morphOne(Document::class, 'documentable')->where('field', 'photo');
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
        foreach ($this->documents as $document) {
            // Ensure the linked file is removed from the server, (see the Document delete() function).
            $document->delete();
        }

        if ($this->photo) {
            $this->photo->delete();
        }

        $this->groups()->detach();

        parent::delete();
    }

    /*
     * Gets the user items according to the filter, sort and pagination settings.
     *
     * @param  Request  $request
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public static function getUsers(Request $request)
    {
        $perPage = $request->input('per_page', Setting::getValue('pagination', 'per_page'));
        $sortedBy = $request->input('sorted_by', null);
        $roles = $request->input('roles', []);
        $groups = $request->input('groups', []);
        $search = $request->input('search', null);

        $query = User::whereHas('roles', function($query) use($roles) {
            if (!empty($roles)) {
                $query->whereIn('name', $roles);
            }
        });

        if (!empty($groups)) {
            $query->whereHas('groups', function($query) use($groups) {
                $query->whereIn('id', $groups);
            });
        }

        if ($search !== null) {
            $query->where('name', 'like', '%'.$search.'%');
        }

        if ($sortedBy !== null) {
            preg_match('#^([a-z0-9_]+)_(asc|desc)$#', $sortedBy, $matches);
            $query->orderBy($matches[1], $matches[2]);
        }

        return $query->paginate($perPage);
    }

    /*
     * Builds the options for the 'role' select field.
     *
     * @param  \App\Models\User $request (optional)
     * @return Array
     */
    public function getRoleOptions($user = null)
    {
        // Check first if the current user is editing their own user account.
        if ($user && auth()->user()->id == $user->id) {
            // Only display the user's role as users cannot change their own role.
            $roles = Role::where('name', $user->getRoleNames()->toArray()[0])->get();
        }
        else {
            $roles = auth()->user()->getAssignableRoles();
        }

        $options = [];

        foreach ($roles as $role) {
            $extra = [];
            $owner = ($role->owned_by == auth()->user()->id) ? auth()->user() : User::findOrFail($role->owned_by);

            if ($role->access_level == 'private' && $owner->getRoleLevel() >= auth()->user()->getRoleLevel() && $role->owned_by != auth()->user()->id) {
                $extra = ['disabled'];
            }

            $options[] = ['value' => $role->name, 'text' => $role->name, 'extra' => $extra];
        }

        return $options;
    }

    /*
     * Builds the options for the 'roles' select field, (used with filters).
     *
     * @return Array
     */
    public function getRolesOptions()
    {
        $roles = Role::all()->pluck('name')->toArray();

        foreach ($roles as $role) {
            $options[] = ['value' => $role, 'text' => $role];
        }

        return $options;
    }

    /*
     * Generic function that returns model values which are handled by select inputs. 
     */
    public function getSelectedValue(\stdClass $field): mixed
    {
        if ($field->name == 'role') {
            return $this->getRoleName();
        }

        if ($field->name == 'groups') {
            return $this->groups->pluck('id')->toArray();
        }

        return null;
    }

    /*
     * Checks whether this user's role is private and whether the current user
     * is allowed to select it or unselect it. 
     *
     * @return boolean
     */
    public function isRolePrivate()
    {
        $role = $this->roles[0];
        $owner = ($role->owned_by == auth()->user()->id) ? auth()->user() : User::findOrFail($role->owned_by);

        return ($role->access_level == 'private' && $owner->getRoleLevel() >= auth()->user()->getRoleLevel() && $role->owned_by != auth()->user()->id) ? true : false;
    }

    /*
     * Returns a relative url to the user's photo thumbnail.
     *
     * @return string
     */
    public function getThumbnail()
    {
        $photo = $this->photo;

        if ($photo) {
            return $photo->getThumbnailUrl();
        }

        // Returns a default user image.
        return '/images/user.png';
    }

    /*
     * Checks whether the current user is allowed to update a given user according to their role type.
     *
     * @param  \App\Models\User $user
     * @return boolean
     */
    public function canUpdate($user)
    {
        if (is_int($user)) {
            $user = User::findOrFail($user);
        }

        $hierarchy = Role::getRoleHierarchy();

        // Users can only update users lower in the hierarchy.
        if ($hierarchy[$this->getRoleType()] > $hierarchy[$user->getRoleType()]) {
            return true;
        }

        return false;
    }

    /*
     * Checks whether the current user is allowed to delete a given user according to their role type.
     *
     * @param  \App\Models\User $user
     * @return boolean
     */
    public function canDelete($user)
    {
        if (is_int($user)) {
            $user = User::findOrFail($user);
        }

        // Users cannot delete their own account.
        if ($this->id == $user->id) {
            return false;
        }

        $hierarchy = Role::getRoleHierarchy();

        // Users can only delete users lower in the hierarchy.
        if ($hierarchy[$this->getRoleType()] > $hierarchy[$user->getRoleType()]) {
            return true;
        }

        return false;
    }

    /*
     * Returns the user's role name.
     *
     * @return string
     */
    public function getRoleName()
    {
        return $this->getRoleNames()->toArray()[0];
    }

    /*
     * Returns the user's role level.
     *
     * @return integer
     */
    public function getRoleLevel()
    {
        $role = Role::where('name', $this->getRoleName())->first();

        return Role::getRoleHierarchy()[$role->role_type];
    }

    /*
     * Returns the user's role type.
     *
     * @return string
     */
    public function getRoleType()
    {
        $role = Role::where('name', $this->getRoleName())->first();

        return $role->role_type;
    }

    /*
     * Returns the users that a user is allowed to assign as owner of an item.
     *
     * @param  Array  $exceptRoleTypes (optional)
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAssignableUsers($exceptRoleTypes = [])
    {
        $roleType = $this->getRoleType();
        $roleTypes = [];

        if ($roleType == 'assistant') {
            $roleTypes = ['registered'];
        }
        elseif ($roleType == 'manager') {
            $roleTypes = ['assistant', 'registered'];
        }
        elseif ($roleType == 'admin') {
            $roleTypes = ['manager', 'assistant', 'registered'];
        }
        elseif ($roleType == 'super-admin') {
            $roleTypes = ['admin', 'manager', 'assistant', 'registered'];
        }

        // Remove possible role types from the list.
        $roleTypes = array_diff($roleTypes, $exceptRoleTypes);

        return User::whereHas('roles', function ($query) use($roleTypes) {
            $query->whereIn('role_type', $roleTypes);
        })->orWhere('id', $this->id)->get(); // Get the user himself as well.
    }

    /*
     * Returns the roles that the current user is allowed to assign to an other user.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getAssignableRoles()
    {
        // Get the current user's role type.
        $roleType = $this->getRoleType();

        // Proceed only with role types able to create users and groups 
        if (!in_array($roleType, ['super-admin', 'admin', 'manager'])) {
            // Returns an empty collection.
            return new \Illuminate\Database\Eloquent\Collection();
        }

        if ($roleType == 'manager') {
            $roles = Role::whereIn('role_type', ['registered', 'assistant'])->get();
        }
        elseif ($roleType == 'admin') {
            $roles = Role::whereIn('role_type', ['manager', 'registered', 'assistant'])->get();
        }
        elseif ($roleType == 'super-admin') {
            $roles = Role::whereIn('role_type', ['admin', 'manager', 'registered', 'assistant'])->get();
        }

        return $roles;
    }

    /*
     * Returns the number of dependencies (if any) owned by this user.
     *
     * @return Array
     */
    public function hasDependencies()
    {
        $dependencies = [
            'posts' => '\\App\\Models\\Post',
            'categories' => '\\App\\Models\\Post\\Category',
            'roles' => '\\App\\Models\\User\\Role',
            'groups' => '\\App\\Models\\User\\Group',
            'menus' => '\\App\\Models\\Menu',
            'documents' => '\\App\\Models\\Cms\\Document',
        ];

        foreach ($dependencies as $name => $model) {
            if ($name == 'documents') {
                // Search for the documents uploaded by this user from the file manager.
                if ($nbItems = $model::where(['documentable_id' => $this->id, 'documentable_type' => 'App\\Models\\User', 'field' => 'file_manager'])->count()) {
                    return ['name' => 'files', 'nbItems' => $nbItems];
                }
                else {
                    continue;
                }
            }

            if ($nbItems = $model::where('owned_by', $this->id)->count()) {
                return ['name' => $name, 'nbItems' => $nbItems];
            }
        }

        return null;
    }

    /**
     * Update (or create) the authenticated user's API token.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function updateApiToken()
    {
        $token = Str::random(60);

        $this->forceFill([
            'api_token' => hash('sha256', $token),
        ])->save();

        return $token;
    }

    /*
     * Blade directive
     *
     * @return boolean
     */
    public function isSuperAdmin()
    {
        return ($this->getRoleName() == 'super-admin') ? true : false;
    }

    /*
     * Blade directive
     *
     * @param  \Spatie\Permission\Models\Permission $permission
     * @return boolean
     */
    public function isAllowedTo($permission)
    {
        return $this->hasRole('super-admin') || $this->hasPermissionTo($permission);
    }

    /*
     * Blade directive
     *
     * @param  \Spatie\Permission\Models\Permission $permission
     * @return boolean
     */
    public function isAllowedToAny($permission)
    {
        return $this->hasRole('super-admin') || $this->hasAnyPermission($permission);
    }

    /*
     * Blade directive
     *
     * @param  \Spatie\Permission\Models\Permission $permission
     * @return boolean
     */
    public function isAllowedToAll($permissions)
    {
        return $this->hasRole('super-admin') || $this->hasAllPermissions($permissions);
    }

    /*
     * Blade directive
     *
     * @return boolean
     */
    public function canAccessAdmin()
    {
        return in_array($this->getRoleType(), ['super-admin', 'admin', 'manager', 'assistant']);
    }
}
