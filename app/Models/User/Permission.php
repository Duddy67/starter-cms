<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission as SpatiePermission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;


class Permission extends SpatiePermission
{
    use HasFactory;


    /*
     * Validation patterns for permission names.
     *
     * @return Array
     */
    public static function getPermissionPatterns()
    {
        return [
            'create-[0-9-a-z\-]+',
            'update-[0-9-a-z\-]+',
            'delete-[0-9-a-z\-]+',
            'access-[0-9-a-z\-]+',
            'update-own-[0-9-a-z\-]+',
            'delete-own-[0-9-a-z\-]+',
            '[0-9-a-z\-]+-settings',
        ];
    }

    /*
     * Gets the permissions.json file and returns it as a list.
     *
     * @param Array  $except (optional)
     * @return Array of stdClass Objects.
     */
    public static function getPermissionList($except = [])
    {
        $json = file_get_contents(app_path().'/Forms/User/Permission/permissions.json', true);

        if ($json === false) {
           throw new Exception('Load Failed');    
        }

        $list = json_decode($json);

        if (!empty($except)) {
            foreach ($list as $section => $permissions) {
                foreach ($permissions as $key => $permission) {
                    if (in_array($permission->type, $except)) {
                        unset($list->$section[$key]);
                    }

                    // Remove empty sections.
                    if (empty($list->$section)) {
                        unset($list->$section);
                    }
                }
            }
        }

        return $list;
    }

    /*
     * Removes the sections from the permission list.
     *
     * @param Array  $except (optional)
     * @return Array of stdClass Objects.
     */
    public static function getPermissionsWithoutSections($except = [])
    {
        $list = self::getPermissionList($except);
        $results = [];

        foreach ($list as $permissions) {
            foreach ($permissions as $permission) {
                $results[] = $permission;
            }
        }

        return $results;
    }

    /*
     * Returns the permission names.
     *
     * @param Array  $except (optional)
     * @return Array
     */
    public static function getPermissionNameList($except = [])
    {
        $list = self::getPermissionList($except);
        $nameList = [];

        foreach ($list as $permissions) {
            foreach ($permissions as $permission) {
                $nameList[] = $permission->name;
            }
        }

        return $nameList;
    }

    /*
     * Builds or rebuilds the permissions from the permissions.json file. 
     *
     * @param  Request  $request
     * @param  boolean  $rebuild  (optional)
     * @return void
     */
    public static function buildPermissions($request, $rebuild = false)
    {
        // Only the super-admin is allowed to perform these tasks.
        if (!auth()->user()->hasRole('super-admin')) {
            $request->session()->flash('error', __('messages.generic.edit_not_allowed'));

            return;
        }

        if ($rebuild) {
            self::truncatePermissions();
        }

        $permissions = self::getPermissionNameList();
        $invalidNames = [];
        $count = 0;

        foreach ($permissions as $permission) {
          // Creates the new permissions.
          if (Permission::where('name', $permission)->first() === null) {
              // Check for permission names.
              if (!preg_match('#^'.implode('|', self::getPermissionPatterns()).'$#', $permission)) {
                  $invalidNames[] = $permission;
                  continue;
              }

              Permission::create(['name' => $permission]);

              $count++;
          }
        }

        if (!empty($invalidNames)) {
            $request->session()->flash('error', __('messages.permission.invalid_permission_names', ['names' => implode(', ', $invalidNames)]));
            return;
        }

        if ($rebuild) {
            if (self::setPermissions($request)) {
                $request->session()->flash('success', __('messages.permission.rebuild_success', ['number' => $count]));
            }

            return;
        }

        if ($count) {
            $request->session()->flash('success', __('messages.permission.build_success', ['number' => $count]));
        }
        else {
            $request->session()->flash('info', __('messages.permission.no_new_permissions'));
        }
    }

    /*
     * Sets the default roles' permissions.
     *
     * @param  Request  $request
     * @return void
     */
    private static function setPermissions($request)
    {
        $permList = self::getPermissionList();

        foreach ($permList as $permissions) {
            foreach ($permissions as $permission) {
                $roles = explode('|', $permission->roles);

                foreach ($roles as $role) {
                    if (!empty($role)) {
                        try {
                            $role = \Spatie\Permission\Models\Role::findByName($role);
                            $role->givePermissionTo($permission->name);
                        }
                        catch (\Exception $e) {
                            $request->session()->flash('error', __('messages.permission.role_does_not_exist', ['name' => $role]));
                            return false;
                        }
                    }
                }
            }
        }

        return true;
    }

    /*
     * Empties the permissions and role permission pivot tables.
     *
     * @return void
     */
    public static function truncatePermissions()
    {
        Schema::disableForeignKeyConstraints();
        DB::table('permissions')->truncate();
        DB::table('role_has_permissions')->truncate();
        Schema::enableForeignKeyConstraints();

        Artisan::call('cache:clear');
    }
}
