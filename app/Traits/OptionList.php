<?php

namespace App\Traits;

use App\Models\User\Group;
use App\Models\User;
use App\Models\Cms\Setting;

trait OptionList
{
    public function getPerPageOptions()
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

    public function getAccessLevelOptions()
    {
      return [
          ['value' => 'private', 'text' => __('labels.generic.private')],
          ['value' => 'public_ro', 'text' => __('labels.generic.public_ro')],
          ['value' => 'public_rw', 'text' => __('labels.generic.public_rw')],
      ];
    }

    public function getStatusOptions()
    {
        return [
            ['value' => 'published', 'text' => __('labels.generic.published')],
            ['value' => 'unpublished', 'text' => __('labels.generic.unpublished')],
        ];
    }

    public function getYesNoOptions()
    {
        return [
            ['value' => 1, 'text' => __('labels.generic.yes')],
            ['value' => 0, 'text' => __('labels.generic.no')],
        ];
    }

    public function getSortedByOptions($pathToForm, $extra = [])
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

    public function getGroupsFilterOptions()
    {
        return $this->getGroupsOptions(true);
    }

    /*
     * Builds the options for the 'groups' select field.
     *
     * @return Array
     */
    public function getGroupsOptions($isFilter = false)
    {
        $groups = Group::all();
        $options = [];

        foreach ($groups as $group) {
            // Get the owner of this group.
            $owner = ($group->owned_by == auth()->user()->id) ? auth()->user() : User::findOrFail($group->owned_by);
            $extra = [];

            // Ensure the current user can use this group.
            if ($group->access_level == 'private' && $owner->getRoleLevel() >= auth()->user()->getRoleLevel() && $group->owned_by != auth()->user()->id) {
                // The item is part of this private group. 
                if ($this->exists && in_array($group->id, $this->getGroupIds())) {
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
    public function getOwnedByOptions()
    {
        $users = auth()->user()->getAssignableUsers();
        $options = [];

        foreach ($users as $user) {
            $options[] = ['value' => $user->id, 'text' => $user->name];
        }

        return $options;
    }

    /*
     * Returns the category list of the model in hierarchical order.
     *
     * @return Array 
     */  
    public function getCategoriesOptions(): array
    {
        // Get the model class name.
        $class = get_class($this);
        // Get the categories of the model.
        $nodes = "\\{$class}\\Category"::defaultOrder()->get()->toTree();
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
     * @return Array 
     */  
    public function getParentCategoryOptions(): array
    {
        // Get the given category model class name.
        $class = get_class($this);
        $nodes = "\\{$class}"::defaultOrder()->get()->toTree();
        $options = [];
        // Defines the state of the current instance.
        //$isNew = ($node && $node->id) ? false : true;
        $isNew = ($this->exists) ? false : true;
        $item = $this;

        $traverse = function ($categories, $prefix = '-') use (&$traverse, &$options, $isNew, $item) {

            foreach ($categories as $category) {
                if (!$isNew && $item->access_level != 'private') {
                    // A non private category cannot be a private category's children.
                    $extra = ($category->access_level == 'private') ? ['disabled'] : [];
                }
                elseif (!$isNew && $item->access_level == 'private' && $category->access_level == 'private') {
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
     * @return Array 
     */  
    public function getOwnedByFilterOptions()
    {
        $table = $this->getTable();
        $query = get_class($this)::query();
        $item = $this;

        $query->select(['users.id', 'users.name'])
              ->leftJoin('users', $table.'.owned_by', '=', 'users.id')
              ->join('model_has_roles', $table.'.owned_by', '=', 'model_id')
              ->join('roles', 'roles.id', '=', 'role_id');

        // N.B: Put the following part of the query into brackets.
        $query->where(function($query) use($table, $item) {

            // Check for access levels.
            $query->where(function($query) use($table) {
                $query->where('roles.role_level', '<', auth()->user()->getRoleLevel())
                      ->orWhereIn($table.'.access_level', ['public_ro', 'public_rw'])
                      ->orWhere($table.'.owned_by', auth()->user()->id);
            });

            // N.B: Make sure the 'groups' relationship exists in the model.
            if (isset($item->groups)) {
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

    public function getPageOptions()
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

    public function getTimezoneOptions()
    {
        $timezoneIdentifiers = \DateTimeZone::listIdentifiers();
        $options = [];

        foreach ($timezoneIdentifiers as $identifier) {
            $options[] = ['value' => $identifier, 'text' => $identifier];
        }

        return $options;
    }
}
