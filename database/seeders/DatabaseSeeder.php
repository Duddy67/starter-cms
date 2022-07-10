<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Menu;
use App\Models\Menu\Item;
use App\Models\Email;
use App\Models\User\Role;
use App\Models\User\Permission;
use Carbon\Carbon;
use App\Models\Setting;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $menu = Menu::create([
            'title' => 'Main menu',
            'code' => 'main-menu',
            'status' => 'published',
            'access_level' => 'public_rw',
            'owned_by' => 1,
        ]);

        // Creates the root item which is the parent of all of the menu items.
        $node = new Item;
        $node->title = 'Root';
        $node->menu_code = 'root';
        $node->url = 'root';
        $node->status = 'published';
        // Saved as root
        $node->save();

        $menuItem = Item::create([
            'title' => 'Home',
            'url' => '/',
            'status' => 'published',
            'parent_id' => 1,
        ]);

        $parent = Item::findOrFail($menuItem->parent_id);
        $parent->appendNode($menuItem);

        $menuItem->menu_code = 'main-menu';
        $menuItem->save();

        Email::create([
          'code' => 'user_registration',
          'subject' => 'Welcome {{ $data->name }}',
          'body_html' => '<p>Hello {{ $data->name }}</p>'.
          '<p>Welcome to Starter CMS !<br />A user account has been created for you.</p>'.
          '<p>login: {{ $data->email }}<br />Please use the password you chose during your registration.</p>'.
          '<p>Best regard,<br />The Starter CMS team.</p>',
          'plain_text' => 0,
        ]);

        Email::create([
          'code' => 'new_message',
          'subject' => 'New message',
          'body_html' => '<p>Hello administrator<br /><br />A user has sent a message.<br />'.
          'Name: {{ $data->name }}<br />Email: {{ $data->email }}<br />Object: {{ $data->object }}<br />'.
          'Message: {{ $data->message }}<br /><br />Best regard, <br />The Starter CMS team.</p>',
          'plain_text' => 0,
        ]);

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

        Setting::insert([
            ['group' => 'app', 'key' => 'name', 'value' => 'Starter CMS'],
            ['group' => 'app', 'key' => 'timezone', 'value' => 'Europe/Paris'],
            ['group' => 'app', 'key' => 'date_format', 'value' => 'd/m/Y H:i'],
            ['group' => 'pagination', 'key' => 'per_page', 'value' => '5'],
            ['group' => 'website', 'key' => 'allow_registering', 'value' => 1],
            ['group' => 'website', 'key' => 'redirect_to_admin', 'value' => 0],
            ['group' => 'website', 'key' => 'theme', 'value' => 'starter']
        ]);
    }
}
