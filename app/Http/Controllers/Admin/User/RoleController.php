<?php

namespace App\Http\Controllers\Admin\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Traits\Form;
use App\Traits\CheckInCheckOut;
use App\Models\User\Role;
use App\Models\User\Permission;
use App\Models\User;
use App\Models\Cms\Setting;
use App\Http\Requests\User\Role\StoreRequest;
use App\Http\Requests\User\Role\UpdateRequest;


class RoleController extends Controller
{
    use Form;

    /*
     * Instance of the model.
     */
    protected $model;

    /*
     * The item to edit in the form.
     */
    protected $item = null;


    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin.users.roles');
        $this->model = new Role;
    }

    /**
     * Show the role list.
     *
     * @param  Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        // Gather the needed data to build the item list.
        $columns = $this->getColumns();
        $actions = $this->getActions('list');
        $filters = $this->getFilters($request);
        $items = $this->model->getItems($request);
        $rows = $this->getRows($columns, $items);
        $this->setRowValues($rows, $columns, $items);

        $url = ['route' => 'admin.users.roles', 'item_name' => 'role', 'query' => $request->query()];
        $query = $request->query();

        return view('admin.user.role.list', compact('items', 'columns', 'rows', 'actions', 'filters', 'url', 'query'));
    }

    /**
     * Show the form for creating a new role.
     *
     * @param  Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function create(Request $request)
    {
        // Gather the needed data to build the form.

        $fields = $this->getFields(['updated_by', 'created_at', 'updated_at', 'owner_name']);
        $actions = $this->getActions('form', ['destroy']);
        $board = $this->getPermissionBoard();
        $query = $request->query();
        $permissions = file_get_contents(app_path().'/Forms/User/Permission/permissions.json', true);

        return view('admin.user.role.form', compact('fields', 'actions', 'board', 'query', 'permissions'));
    }

    /**
     * Show the form for editing the specified role.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function edit(Request $request, $id)
    {
        $role = $this->item = Role::select('roles.*', 'users.name as owner_name', 'users2.name as modifier_name')
                                    ->leftJoin('users', 'roles.owned_by', '=', 'users.id')
                                    ->leftJoin('users as users2', 'roles.updated_by', '=', 'users2.id')
                                    ->findOrFail($id);

        if (!$role->canAccess()) {
            return redirect()->route('admin.users.roles.index', $request->query())->with('error',  __('messages.generic.access_not_auth'));
        }

        if ($role->checked_out && $role->checked_out != auth()->user()->id && !$role->isUserSessionTimedOut()) {
            return redirect()->route('admin.users.roles.index', $request->query())->with('error',  __('messages.generic.checked_out'));
        }

        if (in_array($role->name, Role::getDefaultRoles())) {
            // Remove the irrelevant fields.
            $except = ['updated_by', 'updated_by', 'owner_name', 'owned_by', 'access_level'];
            // No need to check out the default roles as they can't be edited or deleted.
        }
        // Regular roles.
        else {
            $role->checkOut();

            // Gather the needed data to build the form.

            $except = (auth()->user()->getRoleLevel() > $role->getOwnerRoleLevel() || $role->owned_by == auth()->user()->id) ? ['owner_name'] : ['owned_by'];
        }

        $fields = $this->getFields($except);
        $this->setFieldValues($fields, $role);
        $board = $this->getPermissionBoard($role);
        $except = (in_array($role->name, Role::getDefaultRoles()) || !$role->canEdit()) ? ['save', 'saveClose', 'destroy'] : [];
        $actions = $this->getActions('form', $except);
        // Add the id parameter to the query.
        $query = array_merge($request->query(), ['role' => $id]);

        return view('admin.user.role.form', compact('role', 'fields', 'actions', 'board', 'query'));
    }

    /**
     * Checks the record back in.
     *
     * @param  Request  $request
     * @param  \App\Models\User\Role $role (optional)
     * @return Response
     */
    public function cancel(Request $request, Role $role = null)
    {
        if ($role) {
            $role->safeCheckIn();
        }

        return redirect()->route('admin.users.roles.index', $request->query());
    }

    /**
     * Update the specified role. (AJAX)
     *
     * @param  \App\Http\Requests\User\Role\UpdateRequest  $request
     * @param  \App\Models\User\Role $role
     * @return Response
     */
    public function update(UpdateRequest $request, Role $role)
    {
        if (in_array($role->id, Role::getDefaultRoleIds())) {
            $request->session()->flash('error', __('messages.role.cannot_update_default_roles'));
            return response()->json(['redirect' => route('admin.users.roles.index', $request->query())]);
        }

        if (!$role->canEdit()) {
            $request->session()->flash('error', __('messages.generic.edit_not_auth'));
            return response()->json(['redirect' => route('admin.users.roles.index', $request->query())]);
        }

        $role->name = $request->input('name');
        $role->updated_by = auth()->user()->id;

        // Ensure the current user has a higher role level than the item owner's or the current user is the item owner.
        if (auth()->user()->getRoleLevel() > $role->getOwnerRoleLevel() || $role->owned_by == auth()->user()->id) {
            $role->owned_by = $request->input('owned_by');
            $role->access_level = $request->input('access_level');
        }

        $role->save();

        // Set the permission list.
        
        $permissions = Permission::getPermissionsWithoutSections();

        foreach ($permissions as $permission) {

            $optional = (isset($permission->optional) && preg_match('#'.$role->role_type.'#', $permission->optional)) ? true : false;

            // Check the optional permissions.
            // Note: No need to check the default role permissions since they have been set during the storing process and cannot be modified anymore.

            if ($optional && in_array($permission->name, $request->input('permissions', [])) && !$role->hasPermissionTo($permission->name)) {
                $role->givePermissionTo($permission->name);
            }
            elseif ($optional && !in_array($permission->name, $request->input('permissions', [])) && $role->hasPermissionTo($permission->name)) {
               $role->revokePermissionTo($permission->name);
            }
        }

        if ($request->input('_close', null)) {
            $role->safeCheckIn();
            // Store the message to be displayed on the list view after the redirect.
            $request->session()->flash('success', __('messages.role.update_success'));
            return response()->json(['redirect' => route('admin.users.roles.index', $request->query())]);
        }

        $refresh = ['updated_at' => Setting::getFormattedDate($role->updated_at), 'updated_by' => auth()->user()->name];

        return response()->json(['success' => __('messages.role.update_success'), 'refresh' => $refresh]);
    }

    /**
     * Store a new role. (AJAX)
     *
     * @param  \App\Http\Requests\User\Role\StoreRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        // Ensure first that an admin doesn't use any level1 permissions (ie: super-admin's permissions). 
        $level1Perms = Permission::getPermissionNameList(['level2', 'level3']);
        $count = array_intersect($request->input('permissions', []), $level1Perms);

        if (auth()->user()->getRoleType() == 'admin' && $count) {
            $request->session()->flash('error', __('messages.role.permission_not_auth'));
            return response()->json(['redirect' => route('admin.users.roles.index', $request->query())]);
        }

        $permissions = Permission::getPermissionsWithoutSections();
        $toGiveTo = [];

        foreach ($permissions as $permission) {

            $roles = (preg_match('#'.$request->input('role_type').'#', $permission->roles)) ? true : false;
            $optional = (isset($permission->optional) && preg_match('#'.$request->input('role_type').'#', $permission->optional)) ? true : false;

            if ($roles && !$optional) {
                 $toGiveTo[] = $permission;
            }
            elseif ($optional && in_array($permission->name, $request->input('permissions', []))) {
                 $toGiveTo[] = $permission;
            }
        }

        $role = Role::create([
            'name' => $request->input('name'),
            'access_level' => $request->input('access_level'),
            'owned_by' => $request->input('owned_by', auth()->user()->id)
        ]);

        foreach ($toGiveTo as $permission) {
            $role->givePermissionTo($permission->name);
        }

        // Set the role attributes.
        $role->role_type = $request->input('role_type');
        $role->role_level = Role::getRoleHierarchy()[$role->role_type];

        $role->save();

        $request->session()->flash('success', __('messages.role.create_success'));

        if ($request->input('_close', null)) {
            return response()->json(['redirect' => route('admin.users.roles.index', $request->query())]);
        }

        // Reload the page.
        return response()->json(['redirect' => route('admin.users.roles.edit', array_merge($request->query(), ['role' => $role->id]))]);
    }

    /**
     * Remove the specified role from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User\Role $role
     * @return Response
     */
    public function destroy(Request $request, Role $role)
    {
        if (!$role->canDelete()) {
            return redirect()->route('admin.users.roles.edit', array_merge($request->query(), ['role' => $role->id]))->with('error',  __('messages.generic.delete_not_auth'));
        }

        if (in_array($role->name, Role::getDefaultRoles())) {
            return redirect()->route('admin.users.roles.edit', array_merge($request->query(), ['role' => $role->id]))->with('error', __('messages.role.cannot_delete_default_roles'));
        }

        if (User::role($role->name)->count()) {
            return redirect()->route('admin.users.roles.edit', array_merge($request->query(), ['role' => $role->id]))->with('error', __('messages.role.user_assigned_to_roles', ['name' => $role->name]));
        }

        $name = $role->name;
        $role->delete();

        return redirect()->route('admin.users.roles.index', $request->query())->with('success', __('messages.role.delete_success', ['name' => $name]));
    }

    /**
     * Remove one or more roles from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return Response
     */
    public function massDestroy(Request $request)
    {
        // Check first for default roles.
        $roles = Role::whereIn('id', $request->input('ids'))->pluck('name')->toArray();
        $result = array_intersect($roles, Role::getDefaultRoles());

        if (!empty($result)) {
            return redirect()->route('admin.users.roles.index', $request->query())->with('error',  __('messages.role.cannot_delete_roles', ['roles' => implode(',', $result)]));
        }

        $roles = [];

        // Then check for dependencies and permissions.
        foreach ($request->input('ids') as $id) {
            $role = Role::findOrFail($id);

            if ($role->users->count()) {
                // Some users are already assigned to this role.
                return redirect()->route('admin.users.roles.index', $request->query())->with('error', __('messages.role.users_assigned_to_roles', ['name' => $role->name]));
            }

            if (!$role->canDelete()) {
                return redirect()->route('admin.users.roles.edit', array_merge($request->query(), ['role' => $id]))->with('error',  __('messages.generic.delete_not_auth'));
            }

            $roles[] = $role;
        }

        foreach ($roles as $role) {
            $role->delete();
        }

        return redirect()->route('admin.users.roles.index', $request->query())->with('success', __('messages.role.delete_list_success', ['number' => count($request->input('ids'))]));
    }

    /**
     * Checks in one or more roles.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return Response
     */
    public function massCheckIn(Request $request)
    {
        $messages = CheckInCheckOut::checkInMultiple($request->input('ids'), '\\App\\Models\\User\\Role');

        return redirect()->route('admin.users.roles.index', $request->query())->with($messages);
    }

    /*
     * Builds the permission board.
     */
    private function getPermissionBoard($role = null)
    {
        // N.B: Only the super-admin and the users type admin are allowed to manage roles.

        $userRoleLevel = auth()->user()->getRoleLevel();
        $hierarchy = Role::getRoleHierarchy();
        $isDefault = ($role && in_array($role->id, Role::getDefaultRoleIds())) ? true : false;

        if (auth()->user()->getRoleType() == 'admin' && !$isDefault) {
            // Restrict permissions for users type admin.
            $permList = Permission::getPermissionList(['level1']);
        }
        // super-admin
        else {
            $permList = Permission::getPermissionList();
        }

        $list = [];

        foreach ($permList as $section => $permissions) {
            $list[$section] = [];

            foreach ($permissions as $permission) {
                $checkbox = new \stdClass();
                $checkbox->type = 'checkbox';
                $checkbox->label = $permission->name;
                $checkbox->position = 'right';
                $checkbox->id = $permission->name;
                $checkbox->name = 'permissions[]';
                $checkbox->value = $permission->name;
                $checkbox->dataset = ['section' => $section];
                $checkbox->checked = false;

                if ($role) {
                    try {
                        if ($role->hasPermissionTo($permission->name)) {
                            $checkbox->checked = true;
                        }

                        $optional = (isset($permission->optional)) ? explode('|', $permission->optional) : [];
                        $checkbox->disabled = (in_array($role->role_type, $optional)) ? false : true;
                    }
                    catch (\Exception $e) {
                        $checkbox->label = $permission->name.' (missing !)';
                        $checkbox->disabled = true;
                        $list[$section][] = $checkbox;

                        continue;
                    }

                    // Disable permissions according to the edited role type.

                    if ($role->name == 'super-admin') {
                        // super-admin has all permissions.
                        $checkbox->checked = true;
                        $role->role_type = 'super-admin';
                    }

                    if (in_array($role->name, Role::getDefaultRoles())) {
                        $checkbox->disabled = true;
                    }

                    if ($role->getOwnerRoleLevel() > $userRoleLevel && ($role->access_level != 'public_rw' || $role->owned_by == auth()->user()->id)) {
                        $checkbox->disabled = true;
                    }
                }

                $list[$section][] = $checkbox;
            }
        }

        return $list;
    }

    /*
     * Sets the row values specific to the Role model.
     *
     * @param  Array  $rows
     * @param  Array of stdClass Objects  $columns
     * @param  \Illuminate\Pagination\LengthAwarePaginator  $roles
     * @return void
     */
    private function setRowValues(&$rows, $columns, $roles)
    {
        foreach ($roles as $key => $role) {
            foreach ($columns as $column) {
                if ($column->name == 'access_level' && in_array($role->id, Role::getDefaultRoleIds())) {
                    $rows[$key]->access_level = __('labels.generic.public_ro');
                }

                if ($column->name == 'owned_by' && in_array($role->id, Role::getDefaultRoleIds())) {
                    $rows[$key]->owned_by = __('labels.generic.system');
                }
            }
        }
    }

    /*
     * Sets field values specific to the Role model.
     *
     * @param  Array of stdClass Objects  $fields
     * @param  \App\Models\User\Role  $role
     * @return void
     */
    private function setFieldValues(&$fields, $role)
    {
        foreach ($fields as $field) {
            if ($field->name == 'role_type') {
                // Role type value cannot be changed again.
                $field = $this->setExtraAttributes($field, ['disabled']);
            }

            if ($field->name == 'name' && in_array($role->name, Role::getDefaultRoles())) {
                $field = $this->setExtraAttributes($field, ['disabled']);
            }
        }
    }
}
