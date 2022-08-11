<?php

return [

    'title' => [
	'dashboard' => 'Dashboard',
	'users' => 'Users',
	'user_management' => 'User management',
	'groups' => 'Groups',
	'roles' => 'Roles',
	'permissions' => 'Permissions',
	'global' => 'Global',
	'settings' => 'Settings',
	'emails' => 'Emails',
	'blog' => 'Blog',
	'posts' => 'Posts',
	'categories' => 'Categories',
	'menus' => 'Menus',
	'menuitems' => 'Menu items',
	'files' => 'Files',
	'subcategories' => 'Sub-categories',
    ],
    'user' => [
	'email' => 'Email',
	'role' => 'Role',
	'roles' => 'Roles',
	'groups' => 'Groups',
	'password' => 'Password',
	'confirm_password' => 'Confirm password',
	'create_user' => 'Create a user',
	'edit_user' => 'Edit a user',
    ],
    'role' => [
	'super-admin' => 'Super Administrator',
	'admin' => 'Administrator',
	'manager' => 'Manager',
	'assistant' => 'Assistant',
	'registered' => 'Registered',
	'create_role' => 'Create a role',
	'edit_role' => 'Edit a role',
    ],
    'group' => [
	'create_group' => 'Create a group',
	'edit_group' => 'Edit a group',
	'add_selected_groups' => 'Add selected groups',
	'remove_selected_groups' => 'Remove selected groups',
	'permission' => 'Group permission',
    ],
    'post' => [
	'create_post' => 'Create a post',
	'edit_post' => 'Edit a post',
	'slug' => 'Slug',
	'content' => 'Content',
	'excerpt' => 'Excerpt',
	'show_excerpt' => 'Show excerpt',
	'blog_global_settings' => 'Blog global settings',
	'post_ordering' => 'Post ordering',
	'show_post_excerpt' => 'Show post excerpt',
	'show_post_categories' => 'Show post categories',
	'show_post_image' => 'Show post image',
    ],
    'category' => [
	'create_category' => 'Create a category',
	'edit_category' => 'Edit a category',
	'parent_category' => 'Parent category',
    ],
    'menu' => [
	'create_menu' => 'Create a menu',
	'edit_menu' => 'Edit a menu',
    ],
    'menuitem' => [
	'create_menu_item' => 'Create a menu item',
	'edit_menu_item' => 'Edit a menu item',
	'parent_item' => 'Parent item',
	'url' => 'URL',
	'model' => 'Model',
	'model_example' => '\App\Models\Post',
	'class' => 'Class',
	'anchor' => 'Anchor',
    ],
    'email' => [
	'subject' => 'Subject',
	'html' => 'HTML',
	'plain_text' => 'Plain text',
	'create_email' => 'Create an email',
	'edit_email' => 'Edit an email',
    ],
    'settings' => [
	'name' => 'Site name',
	'timezone' => 'Timezone',
	'date_format' => 'Date format',
	'per_page' => 'Number of items per page',
	'admin_email' => 'The email of the website administrator',
	'theme' => 'The theme to use for the website',
	'allow_registering' => 'Allow guests registering on the front-end',
	'redirect_to_admin' => 'Redirect authorized users to admin after logged in.',
    ],
    'generic' => [
	'title' => 'Title',
	'name' => 'Name',
	'slug' => 'Slug',
	'code' => 'Code',
	'alias' => 'Alias',
	'id' => 'ID',
	'description' => 'Description',
	'created_at' => 'Created at',
	'owned_by' => 'Owned by',
	'updated_at' => 'Updated at',
	'updated_by' => 'Updated by',
	'access_level' => 'Access level',
	'private' => 'Private',
	'public_ro' => 'Public read only',
	'public_rw' => 'Public read / write',
	'read_only' => 'Read Only',
	'read_write' => 'Read Write',
	'status' => 'Status',
	'published' => 'Published',
	'unpublished' => 'Unpublished',
	'published_up' => 'Start publishing',
	'published_down' => 'Finish publishing',
	'access' => 'Access',
	'category' => 'Category',
	'ordering' => 'Ordering',
	'type' => 'Type',
	'format' => 'Format',
	'preview' => 'Preview',
	'none' => 'None',
	'unknown' => 'Unknown',
	'unknown_user' => 'Unknown user',
	'system' => 'System',
	'image' => 'Image',
	'photo' => 'Photo',
	'extra' => 'Extra',
	'alt_img' => 'alt image attribute',
	'select_option' => '- Select -',
	'batch_title' => 'Fields to update for the selection of items',
	'global_setting' => 'Global setting',
	'details' => 'Details',
	'no_ordering' => 'No ordering',
	'title_asc' => 'Title ascendant',
	'title_desc' => 'Title descendant',
	'created_at_asc' => 'Created at ascendant',
	'created_at_desc' => 'Created at descendant',
	'updated_at_asc' => 'Updated at ascendant',
	'updated_at_desc' => 'Updated at descendant',
	'order_asc' => 'Order ascendant',
	'order_desc' => 'Order descendant',
	'yes' => 'Yes',
	'no' => 'No',
	'show_title' => 'Show title',
	'show_name' => 'Show name',
	'show_search' => 'Show search',
	'show_description' => 'Show description',
	'show_categories' => 'Show categories',
	'show_subcategories' => 'Show sub-categories',
	'show_owner' => 'Show owner',
	'show_created_at' => 'Show created at',
	'show_image' => 'Show image',
	'extra_fields' => 'Extra fields',
	'extra_field_1' => 'Extra field 1',
	'extra_field_2' => 'Extra field 2',
	'extra_field_3' => 'Extra field 3',
	'extra_field_4' => 'Extra field 4',
	'extra_field_5' => 'Extra field 5',
	'extra_field_6' => 'Extra field 6',
	'extra_field_7' => 'Extra field 7',
	'extra_field_8' => 'Extra field 8',
	'extra_field_9' => 'Extra field 9',
	'extra_field_10' => 'Extra field 10',
	'extra_field_aliases' => 'Extra field aliases',
	'alias_extra_field_1' => 'Alias extra field 1',
	'alias_extra_field_2' => 'Alias extra field 2',
	'alias_extra_field_3' => 'Alias extra field 3',
	'alias_extra_field_4' => 'Alias extra field 4',
	'alias_extra_field_5' => 'Alias extra field 5',
	'alias_extra_field_6' => 'Alias extra field 6',
	'alias_extra_field_7' => 'Alias extra field 7',
	'alias_extra_field_8' => 'Alias extra field 8',
	'alias_extra_field_9' => 'Alias extra field 9',
	'alias_extra_field_10' => 'Alias extra field 10',
	'meta_data' => 'Meta data',
	'meta_page_title' => 'Page title',
	'meta_name_description' => 'Description',
	'meta_name_robots' => 'Robots',
	'meta_og_title' => 'Open Graph - title',
	'meta_og_description' => 'Open Graph - description',
	'meta_og_type' => 'Open Graph - type',
	'meta_og_image' => 'Open Graph - image',
	'meta_og_url' => 'Open Graph - url',
	'meta_og_local' => 'Open Graph - local',
    ],
    'filter' => [
	'search' => 'Search',
	'search_by_name' => 'Search by name',
	'sorted_by' => 'Sorted by',
	'per_page' => 'Per page',
	'owned_by' => 'Owned by',
    ],
    'button' => [
	'new' => 'New',
	'delete' => 'Delete',
	'search' => 'Search',
	'clear' => 'Clear',
	'clear_all' => 'Clear all',
	'save' => 'Save',
	'save_close' => 'Save & close',
	'cancel' => 'Cancel',
	'update' => 'Update',
	'rebuild' => 'Rebuild',
	'batch' => 'Batch',
	'checkin' => 'Check-in',
	'publish' => 'Publish',
	'unpublish' => 'Unpublish',
	'send_test_email' => 'Send test email',
    ],
    'pagination' => [
	'results' => 'Showing :first to :last of :total results',
    ],
];

