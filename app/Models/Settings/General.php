<?php

namespace App\Models\Settings;
use Request;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\User\Group;
use App\Models\User\User;


class General extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'settings_general';

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


    public static function getData()
    {
        $results = General::all()->toArray();
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
     * Returns the value of a given key from a given group.
     * @param  string  $group
     * @param  string  $key
     * @return string
     */
    public static function getValue($group, $key)
    {
        return General::where(['group' => $group, 'key' => $key])->pluck('value')->first();
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

    public static function getSortedByOptions($pluginName, $modelName)
    {
        $json = file_get_contents(app_path().'/Models/'.ucfirst($pluginName).'/'.$modelName.'/columns.json', true);
        $columns = json_decode($json);
        $options = [];

        foreach ($columns as $column) {
            if (isset($column->extra) && in_array('sortable', $column->extra)) {
                $options[] = ['value' => $column->name.'_asc', 'text' => $column->name.' asc'];
                $options[] = ['value' => $column->name.'_desc', 'text' => $column->name.' desc'];
            }
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
    public function getSelectedValue($fieldName)
    {
        if ($fieldName == 'per_page') {
            return $this->where(['group' => 'pagination', 'key' => 'per_page'])->pluck('value')->first();
        }
    }

    public static function getAppSettings()
    {
        $data = DB::table('settings_general')->where('group', 'app')->get();
        $settings = [];

        foreach ($data as $row) {
            $settings['app.'.$row->key] = $row->value;
        }

        return $settings;
    }
}
