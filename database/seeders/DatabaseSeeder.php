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
        $node->menu_code = 'root';
        $node->status = 'published';
        // Saved as root
        $node->save();

        $translation = $node->getOrCreateTranslation(config('app.locale'));
        $translation->title = 'Root';
        $translation->url = 'root';
        $translation->save();

        $menuItem = Item::create([
            'status' => 'published',
            'parent_id' => 1,
        ]);

        $parent = Item::findOrFail($menuItem->parent_id);
        $parent->appendNode($menuItem);

        $menuItem->menu_code = 'main-menu';
        $menuItem->save();

        $translation = $menuItem->getOrCreateTranslation('en');
        $translation->title = 'Home';
        $translation->url = '/';
        $translation->save();

        $translation = $menuItem->getOrCreateTranslation('fr');
        $translation->title = 'Accueil';
        $translation->url = '/';
        $translation->save();

        // Create basic emails.

        $email = Email::create([
          'code' => 'user-registration',
          'updated_by' => 1,
          'plain_text' => 0,
        ]);

        $translation = $email->getOrCreateTranslation('en');
        $translation->subject = 'Welcome {{ $data->name }}';
        $translation->body_html = '<p>Hello {{ $data->name }}</p>'.
          '<p>Welcome to Starter CMS !<br />A user account has been created for you.</p>'.
          '<p>login: {{ $data->email }}<br />Please use the password you chose during your registration.</p>'.
          '<p>Best regard,<br />The Starter CMS team.</p>';
        $translation->save();

        $email->setViewFiles('en');

        $translation = $email->getOrCreateTranslation('fr');
        $translation->subject = 'Bienvenue {{ $data->name }}';
        $translation->body_html = '<p>Bonjour {{ $data->name }}</p>'.
          '<p>Bienvenue sur Starter CMS !<br />Un compte utilisateur a été créé pour vous.</p>'.
          '<p>login: {{ $data->email }}<br />Veuillez utiliser le mot de passe que vous avez choisi durant l\'inscription.</p>'.
          '<p>Cordialement,<br />L\'équipe de Starter CMS.</p>';
        $translation->save();

        $email->setViewFiles('fr');

        $email = Email::create([
          'code' => 'new-message',
          'updated_by' => 1,
          'plain_text' => 0,
        ]);

        $translation = $email->getOrCreateTranslation('en');
        $translation->subject = 'New message';
        $translation->body_html = '<p>Hello administrator<br /><br />A user has sent a message.<br />'.
          'Name: {{ $data->name }}<br />Email: {{ $data->email }}<br />Object: {{ $data->object }}<br />'.
          'Message: {{ $data->message }}<br /><br />Best regard, <br />The Starter CMS team.</p>';
        $translation->save();

        $email->setViewFiles('en');

        $translation = $email->getOrCreateTranslation('fr');
        $translation->subject = 'Nouveau message';
        $translation->body_html = '<p>Bonjour administrateur<br /><br />Un utilisateur a envoyé un message.<br />'.
          'Nom: {{ $data->name }}<br />Email: {{ $data->email }}<br />Objet: {{ $data->object }}<br />'.
          'Message: {{ $data->message }}<br /><br />Cordialement, <br />L\'équipe de Starter CMS.</p>';
        $translation->save();

        $email->setViewFiles('fr');

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
            ['group' => 'website', 'key' => 'admin_email', 'value' => 'admin@domain.com'],
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
