<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Menu;
use App\Models\Menu\Item;
use App\Models\Cms\Email;
use App\Models\User\Role;
use App\Models\User\Permission;
use Carbon\Carbon;
use App\Models\Cms\Setting;
use App\Models\Post\Setting as PostSetting;


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

        $email = Email::create([
          'code' => 'user-registration',
          'subject' => 'Welcome {{ $data->name }}',
          'body_html' => '<p>Hello {{ $data->name }}</p>'.
          '<p>Welcome to Starter CMS !<br />A user account has been created for you.</p>'.
          '<p>login: {{ $data->email }}<br />Please use the password you chose during your registration.</p>'.
          '<p>Best regard,<br />The Starter CMS team.</p>',
          'plain_text' => 0,
        ]);

        $email->setViewFiles();

        $email = Email::create([
          'code' => 'new-message',
          'subject' => 'New message',
          'body_html' => '<p>Hello administrator<br /><br />A user has sent a message.<br />'.
          'Name: {{ $data->name }}<br />Email: {{ $data->email }}<br />Object: {{ $data->object }}<br />'.
          'Message: {{ $data->message }}<br /><br />Best regard, <br />The Starter CMS team.</p>',
          'plain_text' => 0,
        ]);

        $email->setViewFiles();

        $email = Email::create([
          'code' => 'comment-alert',
          'subject' => 'Comment alert',
          'body_html' => '<p>Hello {{ $data->post_author }}<br /><br />'.
          'The user {{ $data->comment_author }} has left a comment regarding your post {{ $data->title }}.<br />'.
          'You can check it out here: {{ $data->post_url }}'.
          '<br /><br />Best regard, <br />The Starter CMS team.</p>',
          'plain_text' => 0,
        ]);

        $email->setViewFiles();

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
            ['group' => 'search', 'key' => 'collation', 'value' => 'utf8mb4_0900_as_ci'],
            ['group' => 'search', 'key' => 'autocomplete_max_results', 'value' => 8],
            ['group' => 'pagination', 'key' => 'per_page', 'value' => '5'],
            ['group' => 'website', 'key' => 'admin_email', 'value' => 'admin@domain.com'],
            ['group' => 'website', 'key' => 'email_sending_method', 'value' => 'synchronous'],
            ['group' => 'website', 'key' => 'allow_registering', 'value' => 1],
            ['group' => 'website', 'key' => 'redirect_to_admin', 'value' => 0],
            ['group' => 'website', 'key' => 'theme', 'value' => 'starter']
        ]);

        PostSetting::insert([
            ['group' => 'posts', 'key' => 'show_owner', 'value' => 1],
            ['group' => 'posts', 'key' => 'show_created_at', 'value' => 1],
            ['group' => 'posts', 'key' => 'show_excerpt', 'value' => 1],
            ['group' => 'posts', 'key' => 'show_image', 'value' => 1],
            ['group' => 'posts', 'key' => 'show_categories', 'value' => 1],
            ['group' => 'posts', 'key' => 'allow_comments', 'value' => 0],
            ['group' => 'posts', 'key' => 'comment_alert', 'value' => 0],
            ['group' => 'posts', 'key' => 'alias_extra_field_1', 'value' => ''],
            ['group' => 'posts', 'key' => 'alias_extra_field_2', 'value' => ''],
            ['group' => 'posts', 'key' => 'alias_extra_field_3', 'value' => ''],
            ['group' => 'posts', 'key' => 'alias_extra_field_4', 'value' => ''],
            ['group' => 'posts', 'key' => 'alias_extra_field_5', 'value' => ''],
            ['group' => 'posts', 'key' => 'alias_extra_field_6', 'value' => ''],
            ['group' => 'posts', 'key' => 'alias_extra_field_7', 'value' => ''],
            ['group' => 'posts', 'key' => 'alias_extra_field_8', 'value' => ''],
            ['group' => 'posts', 'key' => 'alias_extra_field_9', 'value' => ''],
            ['group' => 'posts', 'key' => 'alias_extra_field_10', 'value' => ''],
            ['group' => 'categories', 'key' => 'show_name', 'value' => 1],
            ['group' => 'categories', 'key' => 'show_search', 'value' => 1],
            ['group' => 'categories', 'key' => 'show_description', 'value' => 1],
            ['group' => 'categories', 'key' => 'show_subcategories', 'value' => 1],
            ['group' => 'categories', 'key' => 'show_post_excerpt', 'value' => 1],
            ['group' => 'categories', 'key' => 'show_post_image', 'value' => 1],
            ['group' => 'categories', 'key' => 'show_post_categories', 'value' => 1],
            ['group' => 'categories', 'key' => 'show_post_ordering', 'value' => 'no_ordering'],
            ['group' => 'categories', 'key' => 'alias_extra_field_1', 'value' => ''],
            ['group' => 'categories', 'key' => 'alias_extra_field_2', 'value' => ''],
            ['group' => 'categories', 'key' => 'alias_extra_field_3', 'value' => ''],
            ['group' => 'categories', 'key' => 'alias_extra_field_4', 'value' => ''],
            ['group' => 'categories', 'key' => 'alias_extra_field_5', 'value' => ''],
            ['group' => 'categories', 'key' => 'alias_extra_field_6', 'value' => ''],
            ['group' => 'categories', 'key' => 'alias_extra_field_7', 'value' => ''],
            ['group' => 'categories', 'key' => 'alias_extra_field_8', 'value' => ''],
            ['group' => 'categories', 'key' => 'alias_extra_field_9', 'value' => ''],
            ['group' => 'categories', 'key' => 'alias_extra_field_10', 'value' => ''],

        ]);
    }
}
