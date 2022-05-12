<?php

namespace App\Traits\Admin;

use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;


trait ItemConfig
{
    /*
     * Returns the column data for an item list.
     *
     * @param Array  $except 
     * @return Array of stdClass Objects
     */  
    public function getColumns($except = [])
    {
        $columns = $this->getData('columns');

        foreach ($columns as $key => $column) {
            // Remove unwanted columns if any.
            if (in_array($column->name, $except)) {
                unset($columns[$key]);
                continue;
            }
        }

        return $columns;
    }

    /*
     * Returns a row list.
     *
     * @param Array of stdClass Objects  $columns
     * @param \Illuminate\Pagination\LengthAwarePaginator  $items
     * @param Array  $except 
     * @return Array of stdClass Objects
     */  
    public function getRows($columns, $items, $except = [])
    {
        $rows = [];

        foreach ($items as $item) {
            // Check for extra attributes.
            if (method_exists($item, 'setExtraAttributes')) {
                $item->setExtraAttributes();
            }

            $row = $this->getRow($columns, $item, $except);
            $rows[] = $row;
        }

        return $rows;
    }

    /*
     * Returns a row tree list.
     *
     * @param Array of stdClass Objects  $columns
     * @param \Illuminate\Pagination\LengthAwarePaginator  $nodes
     * @param Array  $except 
     * @return Array of stdClass Objects
     */  
    public function getRowTree($columns, $nodes, $except = [])
    {
        $rows = [];

        $traverse = function ($items, $prefix = '-') use (&$traverse, &$rows, $columns, $except) {
            foreach ($items as $item) {
                $row = $this->getRow($columns, $item, $except, $prefix);
                $rows[] = $row;

                $traverse($item->children, $prefix.'-');
            }
        };

        $traverse($nodes);

        return $rows;
    }

    /*
     * Sets the values for a given item row.
     *
     * @param Array of stdClass Objects  $columns
     * @param Object  $item
     * @param Array   $except 
     * @param string  $prefix
     * @return stdClass Object
     */  
    private function getRow($columns, $item, $except = [], $prefix = '')
    {
        $row = new \stdClass();
        $row->item_id = $item->id;

        if ($item->checked_out !== null) {
            $row->checked_out = DB::table('users')->where('id', $item->checked_out)->pluck('name')->first();

            if (is_string($item->checked_out_time)) {
                // Converts the string date into Carbon object.
                $item->checked_out_time = Carbon::parse($item->checked_out_time);
            }

            $row->checked_out_time = $item->checked_out_time->toFormattedDateString();
        }

        foreach ($columns as $column) {
            if (!in_array($column->name, $except)) {

                if ($column->type == 'date') {
                    $row->{$column->name} = Setting::getFormattedDate($item->{$column->name});  
                }
                elseif ($column->name == 'owned_by') {
                    $row->owned_by = $item->owner_name;
                }
                elseif ($column->name == 'access_level') {
                    $row->access_level = __('labels.generic.'.$item->access_level);
                }
                elseif ($column->name == 'status') {
                    $row->status = __('labels.generic.'.$item->status);
                }
                elseif (in_array($column->name, ['name', 'title']) && !empty($prefix)) {
                    $attributeName = ($column->name == 'name') ? 'name' : 'title';
                    $row->$attributeName = $prefix.' '.$item->$attributeName;
                }
                // Sets the ordering links according to the position of the item/node.
                elseif ($column->name == 'ordering') {
                    $ordering = [];
                    // A menu code variable is required for the menu item routes.
                    $query = ($this->modelName == 'menuitem') ? ['code' => $item->menu_code, 'menuItem' => $item->id] : [$this->modelName => $item->id];

                    if ($item->getPrevSibling()) { 
                        $ordering['up'] = route('admin.'.$this->pluginName.'.'.Str::plural($this->modelName).'.up', $query);
                    }

                    if ($item->getNextSibling()) { 
                        $ordering['down'] = route('admin.'.$this->pluginName.'.'.Str::plural($this->modelName).'.down', $query);
                    }

                    $row->ordering = $ordering;
                }
                else {
                    $row->{$column->name} = $item->{$column->name};
                }
            }
            else {
                $row->{$column->name} = null;
            }
        }

        return $row;
    }

    /*
     * Returns the field data for an item form.
     *
     * @param A model instance  $item
     * @param Array  $except 
     * @return Array of stdClass Objects
     */  
    public function getFields($item = null, $except = [])
    {
        $fields = $this->getData('fields');

        foreach ($fields as $key => $field) {
            // Remove unwanted fields if any.
            if (in_array($field->name, $except)) {
                unset($fields[$key]);
                continue;
            }

            // Set the select field types.
            if ($field->type == 'select') {
                $fields[$key]->options = $this->getSelectOptions($field, $item);
            }

            if ($item) {

                if ($field->type == 'select') {
                    $fields[$key]->value = $item->getSelectedValue($field->name);
                }
                elseif ($field->type == 'date') {
                    $datetime = $item->{$field->name}->tz(Setting::getValue('app', 'timezone'))->toDateTimeString();
		    // For whatever reason Daterangepicker prevent the field value to be
		    // set. So do not use it !
                    $fields[$key]->value = null;

		    // Set date and time values through datasets.
                    if (isset($fields[$key]->dataset)) {
                        $data = explode(' ', $datetime);
                        $fields[$key]->dataset->date = $data[0];
                        $fields[$key]->dataset->time = $data[1];
                    }
                }
                elseif ($field->name == 'updated_by') {
                    $fields[$key]->value = $item->modifier_name;
                }
                else {
                    $fields[$key]->value = $item->{$field->name};
                }

                if (isset($item->access_level) && !$item->canEdit()) {
                    $field = $this->setExtraAttributes($field, ['disabled']);
                }

                if ($field->name == 'status' && !$item->canChangeStatus()) {
                    $field = $this->setExtraAttributes($field, ['disabled']);
                }

                if (isset($item->access_level) && in_array($field->name, ['access_level', 'owned_by', 'groups', 'categories', 'parent_id']) && !$item->canChangeAccessLevel()) {
                    $field = $this->setExtraAttributes($field, ['disabled']);
                }

                if (method_exists($item, 'isParentPrivate') && $item->access_level == 'private') { 
                    // Check for parent or children private items then disable field(s) accordingly.
                    if ((in_array($field->name, ['access_level', 'owned_by']) && $item->isParentPrivate()) || ($field->name == 'owned_by' && !$item->isParentPrivate())) {
                        $field = $this->setExtraAttributes($field, ['disabled']);
                    }

                    // Only the owner of the descendants private items can change their parents.
                    if ($field->name == 'parent_id' && $item->isParentPrivate() && $item->owned_by != auth()->user()->id) {
                        $field = $this->setExtraAttributes($field, ['disabled']);
                    }
                }

                if (method_exists($item, 'canDescendantsBePrivate') && $field->name == 'access_level' && $item->access_level != 'private' && !$item->canDescendantsBePrivate()) { 
                    // Prevent the private option to be selected.
                    foreach ($field->options as $key => $option) {
                        if ($option['value'] == 'private') {
                            $field->options[$key] = ['value' => 'private', 'text' => $option['text'], 'extra' => ['disabled']];
                            break;
                        }
                    }
                    
                }
            }

            if ($field->name == 'owned_by' && count($field->options) == 1) {
                // The current user is the only owner option possible so let's get rid of the empty option.
                unset($fields[$key]->blank);
            }
        }

        return $fields;
    }

    /*
     * Returns the filter data for an item list.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param Array  $except 
     * @return Array of stdClass Objects
     */  
    public function getFilters($request, $except = [])
    {
        $filters = $this->getData('filters');

        foreach ($filters as $key => $filter) {

            // Remove unwanted filters if any.
            if (in_array($filter->name, $except)) {
                unset($filters[$key]);
                continue;
            }

            if ($filter->type == 'button') {
                continue;
            }

            $default = null;

            if ($filter->type == 'select') {
                // Build the function name.
                $function = 'get'.str_replace('_', '', ucwords($filter->name, '_')).'Options';

                // Common filters.

                if ($filter->name == 'per_page') {
                    $options = Setting::$function();
                    $default = Setting::getValue('pagination', 'per_page');
                }
                elseif ($filter->name == 'sorted_by') {
                    $options = Setting::$function($this->pluginName, $this->modelName);
                }
                elseif ($filter->name == 'owned_by' && $this->modelName != 'document') {
                    $options = Setting::getOwnedByFilterOptions($this->model);
                }
                elseif ($filter->name == 'groups') {
                    $options = Setting::getGroupsFilterOptions();
                }
                // Specific to the model.
                else {
                    $options = $this->model->$function();
                }

                $filters[$key]->options = $options;
            }

            $filters[$key]->value = $request->input($filter->name, $default);
        }

        return $filters;
    }

    /*
     * Returns the action data for an item list or form.
     *
     * @param  string  $section
     * @param  Array  $except
     * @return Array of stdClass Objects
     */  
    public function getActions($section, $except = [])
    {
        $actions = $this->getData('actions');

        if (!in_array($section, ['list', 'form', 'batch'])) {
            return null;
        }

        if (!empty($except)) {
            foreach ($actions->{$section} as $key => $action) {
                if (in_array($action->id, $except)) {
                    unset($actions->{$section}[$key]);
                }
            } 
        }

        return $actions->{$section};
    }

    /*
     * Returns only the fields passed in parameters.
     *
     * @param  Array  $fieldNames
     * @return stdClass Object
     */  
    public function getSpecificFields($fieldNames)
    {
        $fields = $this->getData('fields');

        foreach ($fields as $key => $field) {
            // Keep only the fields passed in parameters.
            if (!in_array($field->name, $fieldNames)) {
                unset($fields[$key]);
                continue;
            }

            // Set the select field types.
            if ($field->type == 'select') {
                $fields[$key]->options = $this->getSelectOptions($field);
            }
        }

        return $fields;
    }

    /*
     * Returns the options for a given select field.
     *
     * @param  Array  $field
     * @param A model instance  $item
     * @return Array
     */  
    private function getSelectOptions($field, $item = null)
    {
        // Build the function name.
        $function = 'get'.str_replace('_', '', ucwords($field->name, '_')).'Options';

        // Common options.

        if ($field->name == 'groups') {
            // Pass the current item object if available.
            $options = Setting::$function($item);
        }
        elseif (in_array($field->name, ['status', 'owned_by', 'access_level']) && !method_exists($this->model, $function)) {
            // Call the Setting method when not availabe in the model.
            $options = Setting::$function();
        }
        // Sets the yes/no select lists.
        elseif (isset($field->extra) && in_array('yes_no', $field->extra)) {
            $options = Setting::getYesNoOptions();
        }
        else {
            // Call the model method.
            $options = ($item) ? $item->$function() : $this->model->$function();
        }

        if (isset($field->extra) && in_array('global_setting', $field->extra)) {
            $options[] = ['value' => 'global_setting', 'text' => __('labels.generic.global_setting')];
        }

        return $options;
    }

    /*
     * Adds one or more extra attributes to a given field.
     *
     * @param  stdClass $field
     * @param  Array  $attributes
     * @return stdClass Object
     */  
    public function setExtraAttributes($field, $attributes)
    {
        if (!isset($field->extra)) {
            $field->extra = $attributes;
        }
        elseif (!in_array('disabled', $field->extra)) {
            foreach ($attributes as $attribute) {
                $field->extra[] = $attribute;
            }
        }

        return $field;
    }

    /*
     * Gets a json file related to a given item then returns the decoded data.
     *
     * @return Array of stdClass Objects / stdClass Object
     */  
    private function getData($type)
    {
        $json = file_get_contents(app_path().'/Models/'.ucfirst($this->pluginName).'/'.$this->modelName.'/'.$type.'.json', true);

        if ($json === false) {
           throw new Exception('Load Failed');    
        }

        return json_decode($json);
    }
}
