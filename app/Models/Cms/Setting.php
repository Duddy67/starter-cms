<?php

namespace App\Models\Cms;
use Request;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\User\Group;
use App\Models\User;
use App\Models\Menu;
use App\Traits\OptionList;


class Setting extends Model
{
    use HasFactory, OptionList;

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
        $settingClassModel = ($model) ? get_class($model) : '\\App\\Models\\Cms\\Setting';

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
        $settingClassModel = ($model) ? self::getSettingClassModel($model) : '\\App\\Models\\Cms\\Setting';

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
        // In case of category, use the setting class of the categorizable model
        if (isset($model->collection_type)) {
            return '\\App\\Models\\'.ucfirst($model->collection_type).'\\Setting';
        }

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
