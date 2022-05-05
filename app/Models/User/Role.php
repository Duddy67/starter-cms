<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role as SpatieRole;
use App\Models\Settings\General;
use App\Traits\Admin\AccessLevel;
use App\Traits\Admin\CheckInCheckOut;
use Carbon\Carbon;


class Role extends SpatieRole 
{
    use HasFactory, AccessLevel, CheckInCheckOut;

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


    /*
     * Roles that cannot be deleted nor updated.
     *
     * @return Array
     */
    public static function getDefaultRoles()
    {
        return [
            'super-admin',
            'admin',
            'manager',
            'assistant',
            'registered'
        ];
    }

    /*
     * Ids of the Roles that cannot be deleted nor updated.
     *
     * @return Array
     */
    public static function getDefaultRoleIds()
    {
        return [1,2,3,4,5];
    }

    /*
     * The default type role hierarchy defined numerically. 
     *
     * @return Array
     */
    public static function getRoleHierarchy()
    {
        return [
            'registered' => 1, 
            'assistant' => 2, 
            'manager' => 3, 
            'admin' => 4, 
            'super-admin' => 5
        ];
    }

    /*
     * Returns the type of a role according to its permissions.
     *
     * @return string
     */
    public function defineRoleType()
    {
        if ($this->hasPermissionTo('create-role')) {
            return 'admin';
        }
        elseif ($this->hasPermissionTo('create-user')) {
            return 'manager';
        }
        elseif ($this->hasPermissionTo('access-dashboard')) {
            return 'assistant';
        }
        else {
            return 'registered';
        }
    }

    /*
     * Gets the role items according to the filter, sort and pagination settings.
     *
     * @param  Request  $request
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getItems($request)
    {
        $perPage = $request->input('per_page', General::getValue('pagination', 'per_page'));
        $search = $request->input('search', null);

        $query = Role::query();
        $query->select('roles.*', 'users.name as owner_name')->leftJoin('users', 'roles.owned_by', '=', 'users.id');

        if ($search !== null) {
            $query->where('roles.name', 'like', '%'.$search.'%');
        }

        return $query->paginate($perPage);
    }

    /*
     * Returns only the users with a super-admin or admin role type.
     *
     * @return array
     */
    public static function getOwnedByOptions()
    {
        // Get only users with admin role types.
        $users = auth()->user()->getAssignableUsers(['manager', 'assistant', 'registered']);
        $options = [];

        foreach ($users as $user) {
            $options[] = ['value' => $user->id, 'text' => $user->name];
        }

        return $options;
    }

    public static function getRoleTypeOptions()
    {
        $roles = [
            ['value' => 'registered', 'text' => __('labels.role.registered')],
            ['value' => 'assistant', 'text' => __('labels.role.assistant')],
            ['value' => 'manager', 'text' => __('labels.role.manager')]
        ];

        if (auth()->user()->getRoleName() == 'super-admin') {
            $roles[] = ['value' => 'admin', 'text' => __('labels.role.admin')];
        }

        return $roles;
    }

    /*
     * Generic function that returns model values which are handled by select inputs. 
     */
    public function getSelectedValue($fieldName)
    {
        return $this->{$fieldName};
    }
}
