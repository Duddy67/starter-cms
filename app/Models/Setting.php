<?php

namespace App\Models;
use Request;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\User\Group;
use App\Models\User;


class Setting extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'group',
        'key',
        'value',
    ];

    /**
     * No timestamps.
     *
     * @var boolean
     */
    public $timestamps = false;


    /*
     * Returns all the data global setting for a given model.
     * If no model is given, returns the CMS data setting.
     */
    public static function getData(mixed $model = null): array
    {
        $settingClassModel = ($model) ? get_class($model) : '\\App\\Models\\Setting';

        $results = $settingClassModel::all()->toArray();
        $data = [];

        foreach ($results as $param) {
            if (!isset($data[$param['group']])) {
                $data[$param['group']] = [];
            }

            $data[$param['group']][$param['key']] = $param['value'];
        }

        return $data;
    }

    /*
     * Sets the item setting values against the item global setting then returns the result.
     */
    public static function getItemSettings(mixed $item, string $group): array
    {
        // Get the global settings of the given item.
	$globalSettings = self::getDataByGroup($group, $item);
	$settings = [];

        // Parse the item setting values.
	foreach ($item->settings as $key => $value) {
	    if ($value == 'global_setting') {
	        // Overwrite with the item global setting value.
	        $settings[$key] = $globalSettings[$key];
	    }
	    else {
	        $settings[$key] = $item->settings[$key];
	    }
	}

	return $settings;
    }

    /*
     * Returns the global data setting by group for a given model.
     * If no model is given, returns the CMS data setting for the given group.
     */
    public static function getDataByGroup(string $group, mixed $model = null): array
    {
        $settingClassModel = ($model) ? self::getSettingClassModel($model) : '\\App\\Models\\Setting';

        $results = $settingClassModel::where('group', $group)->get();
	$data = [];

	foreach ($results as $param) {
	    $data[$param->key] = $param->value;
	}

	return $data;
    }

    /*
     * Computes and returns the Setting class (with namespace) for a given model.
     * N.B: As a rule of thumb, the Setting class of a collection must be in the
     *      fourth position in the namespace (eg: \App\Models\Foo\Setting).
     */
    public static function getSettingClassModel(mixed $model): ?string
    {
        // Get the class names contained in the namespace.
        $classes = explode('\\', get_class($model));

        // The namespace must at least contained 3 classes (eg: App\Models\Foo).
        if (count($classes) < 3) {
            return false;
        }

        $settingClassModel = '';

        // Build the namespace up to the third class.
        for ($i = 0; $i < 3; $i++) {
            $settingClassModel .= '\\'.$classes[$i];
        }

        // Add the Setting class to the namespace.
        $settingClassModel .= '\\Setting';

        return $settingClassModel;
    }

    /*
     * Returns the value of a given key from a given group.
     * @param  string  $group
     * @param  string  $key
     * @return string
     */
    public static function getValue(string $group, string $key, ?string $default = null): ?string
    {
        $value = Setting::where(['group' => $group, 'key' => $key])->pluck('value')->first();
        return ($value) ? $value : $default;
    }

    public static function getPerPageOptions()
    {
      return [
          ['value' => 2, 'text' => 2],
          ['value' => 5, 'text' => 5],
          ['value' => 10, 'text' => 10],
          ['value' => 15, 'text' => 15],
          ['value' => 20, 'text' => 20],
          ['value' => 25, 'text' => 25],
      ];
    }

    public static function getAccessLevelOptions()
    {
      return [
          ['value' => 'private', 'text' => __('labels.generic.private')],
          ['value' => 'public_ro', 'text' => __('labels.generic.public_ro')],
          ['value' => 'public_rw', 'text' => __('labels.generic.public_rw')],
      ];
    }

    public static function getStatusOptions()
    {
        return [
            ['value' => 'published', 'text' => __('labels.generic.published')],
            ['value' => 'unpublished', 'text' => __('labels.generic.unpublished')],
        ];
    }

    public static function getYesNoOptions()
    {
        return [
            ['value' => 1, 'text' => __('labels.generic.yes')],
            ['value' => 0, 'text' => __('labels.generic.no')],
        ];
    }

    public static function getSortedByOptions($pathToForm, $extra = [])
    {
        $json = file_get_contents($pathToForm.'/columns.json', true);
        $columns = json_decode($json);
        $options = [];

        foreach ($columns as $column) {
            if (isset($column->extra) && in_array('sortable', $column->extra)) {
                $options[] = ['value' => $column->name.'_asc', 'text' => $column->name.' asc'];
                $options[] = ['value' => $column->name.'_desc', 'text' => $column->name.' desc'];
            }
        }

        // Add the numerical order.
        if (in_array('ordering', $extra)) {
            $options[] = ['value' => 'order_asc', 'text' => 'Order asc'];
            $options[] = ['value' => 'order_desc', 'text' => 'Order desc'];
        }

        return $options;
    }

    public static function getGroupsFilterOptions()
    {
        return self::getGroupsOptions(auth()->user());
    }

    /*
     * Builds the options for the 'groups' select field.
     *
     * @return Array
     */
    public static function getGroupsOptions($item = null)
    {
        $groups = Group::all();
        $options = [];
        // Check whether the dropdown list is used as a filter on the item list view.
        $isFilter = ($item && debug_backtrace()[1]['function'] == 'getFilters') ? true : false; 

        foreach ($groups as $group) {
            // Get the owner of this group.
            $owner = ($group->owned_by == auth()->user()->id) ? auth()->user() : User::findOrFail($group->owned_by);
            $extra = [];

            // Ensure the current user can use this group.
            if ($group->access_level == 'private' && $owner->getRoleLevel() >= auth()->user()->getRoleLevel() && $group->owned_by != auth()->user()->id) {
                // The item is part of this private group. 
                if ($item && in_array($group->id, $item->getGroupIds())) {
                    // Show the group.
                    // N.B: This option is disabled in the form field.
                    //      This option is available in the search filter (list view).
                    $extra[] = ($isFilter) ? null : 'disabled';
                }
                else {
                    // Don't show the group.
                    continue;
                }
            }

            $options[] = ['value' => $group->id, 'text' => $group->name, 'extra' => $extra];
        }

        return $options;
    }

    /*
     * Returns the users that the current user is allowed to assign as owner of an item.
     *
     * @return Array 
     */  
    public static function getOwnedByOptions()
    {
        $users = auth()->user()->getAssignableUsers();
        $options = [];

        foreach ($users as $user) {
            $options[] = ['value' => $user->id, 'text' => $user->name];
        }

        return $options;
    }

    /*
     * Returns the category list of the given model in hierarchical order.
     *
     * @param  Object  $model
     * @return Array
     */
    public static function getCategoriesOptions(mixed $model): array
    {
        // Get the given model class name.
        $class = get_class($model);
        // Get the categories of the given model.
        $nodes = "\\{$class}\\Category"::select('post_categories.*', 'translations.name as name')
            ->join('translations', function($join) use($class) {
                $join->on('post_categories.id', '=', 'translatable_id')
                     ->where('translations.translatable_type', '=', $class.'\Category')
                     ->where('locale', '=', config('app.locale'));
        })->get()->toTree();

        $options = [];
        $userGroupIds = auth()->user()->getGroupIds();

        $traverse = function ($categories, $prefix = '-') use (&$traverse, &$options, $userGroupIds) {
            foreach ($categories as $category) {
                // Check wether the current user groups match the category groups (if any).
                $belongsToGroups = (!empty(array_intersect($userGroupIds, $category->getGroupIds()))) ? true : false;
                // Set the category option accordingly.
                $extra = ($category->access_level == 'private' && $category->owned_by != auth()->user()->id && !$belongsToGroups) ? ['disabled'] : [];
                $options[] = ['value' => $category->id, 'text' => $prefix.' '.$category->name, 'extra' => $extra];

                $traverse($category->children, $prefix.'-');
            }
        };

        $traverse($nodes);

        return $options;
    }

    /*
     * Returns the parent category list of the given model in hierarchical order.
     *
     * @param  Object  $model
     * @return Array
     */
    public static function getParentCategoryOptions(mixed $model, mixed $node = null)
    {
        // Get the given category model class name.
        $class = get_class($model);
        $nodes = "\\{$class}"::select('post_categories.*', 'translations.name as name')
            ->join('translations', function($join) use($class) {
                $join->on('post_categories.id', '=', 'translatable_id')
                     ->where('translations.translatable_type', '=', $class)
                     ->where('locale', '=', config('app.locale'));
        })->get()->toTree();

        $options = [];
        // Defines the state of the current instance.
        $isNew = ($node && $node->id) ? false : true;

        $traverse = function ($categories, $prefix = '-') use (&$traverse, &$options, $isNew, $node) {

            foreach ($categories as $category) {
                if (!$isNew && $node->access_level != 'private') {
                    // A non private category cannot be a private category's children.
                    $extra = ($category->access_level == 'private') ? ['disabled'] : [];
                }
                elseif (!$isNew && $node->access_level == 'private' && $category->access_level == 'private') {
                      // Only the category's owner can access it.
                      $extra = ($category->owned_by == auth()->user()->id) ? [] : ['disabled'];
                }
                elseif ($isNew && $category->access_level == 'private') {
                      // Only the category's owner can access it.
                      $extra = ($category->owned_by == auth()->user()->id) ? [] : ['disabled'];
                }
                else {
                    $extra = [];
                }

                $options[] = ['value' => $category->id, 'text' => $prefix.' '.$category->name, 'extra' => $extra];

                $traverse($category->children, $prefix.'-');
            }
        };

        $traverse($nodes);

        return $options;
    }

    /*
     * Returns the users who own a given item model according to its access level and
     * to the current user's role level and groups.
     *
     * @param  Object  $model
     * @return Array 
     */  
    public static function getOwnedByFilterOptions($model)
    {
        $table = $model->getTable();
        $query = get_class($model)::query();

        $query->select(['users.id', 'users.name'])
              ->leftJoin('users', $table.'.owned_by', '=', 'users.id')
              ->join('model_has_roles', $table.'.owned_by', '=', 'model_id')
              ->join('roles', 'roles.id', '=', 'role_id');

        // N.B: Put the following part of the query into brackets.
        $query->where(function($query) use($table, $model) {

            // Check for access levels.
            $query->where(function($query) use($table) {
                $query->where('roles.role_level', '<', auth()->user()->getRoleLevel())
                      ->orWhereIn($table.'.access_level', ['public_ro', 'public_rw'])
                      ->orWhere($table.'.owned_by', auth()->user()->id);
            });

            // N.B: Make sure the 'groups' relationship exists in the model.
            if (isset($model->groups)) {
                $groupIds = auth()->user()->getGroupIds();

                if (!empty($groupIds)) {
                    // Check for access through groups.
                    $query->orWhereHas('groups', function ($query)  use ($groupIds) {
                        $query->whereIn('id', $groupIds);
                    });
                }
            }
        });

        $owners = $query->distinct()->get();

        $options = [];

        foreach ($owners as $owner) {
            $options[] = ['value' => $owner->id, 'text' => $owner->name];
        }

        return $options;
    }

    public static function getExtraFieldByAlias($item, $alias)
    {
        if (!isset($item->extra_fields) || !isset($item->global_settings)) {
            return null;
        }

        $extraField = null;

        foreach ($item->global_settings as $key => $value) {
            if ($value == $alias && str_starts_with($key, 'alias_extra_field_')) {
                $extraField = str_replace('alias_', '', $key);
            }
        }

        return ($extraField) ? $item->extra_fields[$extraField] : null;
    }

    public static function getPageOptions()
    {
        $theme = Setting::getValue('website', 'theme');
        $pages = @scandir(resource_path().'/views/themes/'.$theme.'/pages');
        $pages = (!$pages) ? [] : $pages;
        $options = [];

        foreach ($pages as $key => $page) {
            // Skip the '.', and '..' directories as well as other directories and the no blade files. 
            if ($key < 2 || !is_file(resource_path().'/views/themes/'.$theme.'/pages/'.$page) || !str_ends_with($page, '.blade.php')) {
                continue;
            }

            // Removes ".blade.php" from the end of the string.
            $options[] = ['value' => substr($page, 0, -10), 'text' => substr($page, 0, -10)];
        }

        return $options;
    }

    public static function getLocaleOptions()
    {
        $locales = config('app.locales');
        $options = [];

        foreach ($locales as $locale) {
            $options[] = ['value' => $locale, 'text' => __('labels.locales.'.$locale)];
        }

        return $options;
    }

    public static function getSegments($modelName)
    {
        // The locales.php file always lives in the "en" lang folder.
        $segments = __('locales.segments.'.$modelName, [], 'en');
        // Set a fallback to prevent Artisan commands (cache:clear ...) to generate errors.
        $locale = \App::runningInConsole() ? app()->getLocale() : request()->segment(1);
        // The user has just landed on the website, no locale variable has been set yet.
        $locale = (empty($locale)) ? config('app.fallback_locale') : $locale;
        // Make sure the locale attribute exists.
        $locale = (!isset($segments[$locale])) ? config('app.fallback_locale') : $locale; 

        return $segments[$locale];
    }

    public static function getTimezoneOptions()
    {
        $timezoneIdentifiers = \DateTimeZone::listIdentifiers();
        $options = [];

        foreach ($timezoneIdentifiers as $identifier) {
            $options[] = ['value' => $identifier, 'text' => $identifier];
        }

        return $options;
    }

    public static function getFormattedDate($date, $format = '')
    {
        $format = (!empty($format)) ? $format : self::getValue('app', 'date_format');

        return $date->tz(self::getValue('app', 'timezone'))->format($format);
    }

    /*
     * Generic function that returns model values which are handled by select inputs. 
     */
    public function getSelectedValue(\stdClass $field): mixed
    {
        if ($field->name == 'per_page') {
            return $this->where(['group' => 'pagination', 'key' => 'per_page'])->pluck('value')->first();
        }

        return null;
    }

    /*
     * Checks the sorted_by filter is set to order_asc or order_desc.
     */
    public static function isSortedByOrder()
    {
        if (request()->input('sorted_by', null) && (request()->input('sorted_by') == 'order_asc' || request()->input('sorted_by') == 'order_desc')) {
            return true;
        }

        return false;
    }

    /*
     * Checks a user can order items numerically by a given filter.
     */
    public static function canOrderBy($filter, $excluded = [])
    {
        // Cannot order if one of the excluded filter is part of the current request.
        foreach (request()->all() as $key => $value) {
            if (in_array($key, $excluded)) {
                return false;
            }
        }

        // Can order if only one item is selected in the filter.
        return (request()->input($filter, null) && count(request()->input($filter)) == 1) ? true : false;
    }

    /*
     * Returns some needed page variables.
     */
    public static function getPage(string $name): array
    {
        $data = self::getData();
        $page = [];

        $page['name'] = $name;
        $page['menu'] = Menu::getMenu('main-menu');
        $page['theme'] = $data['website']['theme'];
        $page['timezone'] = $data['app']['timezone'];
        $page['allow_registering'] = $data['website']['allow_registering'];

        return $page;
    }

    public static function getAppSettings()
    {
        $data = DB::table('settings')->where('group', 'app')->get();
        $settings = [];

        foreach ($data as $row) {
            $settings['app.'.$row->key] = $row->value;
        }

        return $settings;
    }
}
