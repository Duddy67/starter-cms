<?php

namespace App\Http\Controllers\Admin\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Traits\Form;
use App\Models\User\Permission;


class PermissionController extends Controller
{
    use Form;

    /*
     * Instance of the Permission model, (used in the Form trait).
     */
    protected $item;


    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin.users.permissions');
        $this->item = new Permission;
    }

    /**
     * Show the permission list.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // Gather the needed data to build the permission list.
        $actions = $this->getActions('list');
        $list = $this->getList();

        return view('admin.user.permission.list', compact('list', 'actions'));
    }

    /*
     * Creates or updates the list.
     *
     * @param  Request  $request
     * @return Response
     */
    public function build(Request $request)
    {
        Permission::buildPermissions($request);
        return redirect()->route('admin.users.permissions.index');
    }

    /*
     * Rebuilds all of the list.
     *
     * @param  Request  $request
     * @return Response
     */
    public function rebuild(Request $request)
    {
        Permission::buildPermissions($request, true);
        return redirect()->route('admin.users.permissions.index');
    }

    /*
     * Set the list of permissions to display.
     *
     * @return Array
     */
    private function getList()
    {
        $permList = Permission::getPermissionList();

        $list = [];

        foreach ($permList as $section => $permissions) {
            $list[$section] = [];

            foreach ($permissions as $permission) {
                // Check for missing permissions.
                if (Permission::where('name', $permission->name)->first() === null) {
                    $list[$section][] = $permission->name.' '.__('messages.permission.missing_alert');
                    continue;
                }

                $list[$section][] = $permission->name;
            }
        }

        return $list;
    }
}
