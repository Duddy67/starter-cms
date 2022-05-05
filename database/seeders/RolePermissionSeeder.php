<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User\Role;
use App\Models\User\Permission;
use Carbon\Carbon;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

	// First create the default permissions. 
	
	$permissions = Permission::getPermissionNameList();

	foreach ($permissions as $permission) {
	    Permission::create(['name' => $permission]);
	}

	// Then create the default roles. 

	$date = Carbon::now();

	Role::insert([
	    ['name' => 'super-admin', 'guard_name' => 'web', 'role_type' => 'super-admin', 'role_level' => 5, 'owned_by' => 1,
	     'access_level' => 'public_ro', 'created_at' => $date->toDateTimeString(), 'updated_at' => $date->toDateTimeString()],
	    ['name' => 'admin', 'guard_name' => 'web', 'role_type' => 'admin', 'role_level' => 4, 'owned_by' => 1,
	     'access_level' => 'public_ro', 'created_at' => $date->toDateTimeString(), 'updated_at' => $date->toDateTimeString()],
	    ['name' => 'manager', 'guard_name' => 'web', 'role_type' => 'manager', 'role_level' => 3, 'owned_by' => 1,
	     'access_level' => 'public_ro', 'created_at' => $date->toDateTimeString(), 'updated_at' => $date->toDateTimeString()],
	    ['name' => 'assistant', 'guard_name' => 'web', 'role_type' => 'assistant', 'role_level' => 2, 'owned_by' => 1,
	     'access_level' => 'public_ro', 'created_at' => $date->toDateTimeString(), 'updated_at' => $date->toDateTimeString()],
	    ['name' => 'registered', 'guard_name' => 'web', 'role_type' => 'registered', 'role_level' => 1, 'owned_by' => 1,
	     'access_level' => 'public_ro', 'created_at' => $date->toDateTimeString(), 'updated_at' => $date->toDateTimeString()]
	]);

	// Gives the permissions corresponding to each default role.
	$roles = Role::all();
	$permissions = Permission::getPermissionsWithoutSections();

	foreach ($roles as $role) {
	    foreach ($permissions as $permission) {
		
		if (preg_match('#'.$role->role_type.'#', $permission->roles)) {
		    $role->givePermissionTo($permission->name);
		}
	    }
	}
    }
}
