<?php

namespace App\Traits;

use App\Models\Cms\Setting;
use App\Models\Cms\Category;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Pagination\LengthAwarePaginator;
use Kalnoy\Nestedset\Collection;
use Illuminate\Http\Request;


trait Form
{
    /*
     * Returns the column data for an item list.
     *
     * @param array  $except 
     * @return array of stdClass Objects
     */  
    public function getColumns(array $except = []): array
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
     * @param array of stdClass Objects  $columns
     * @param \Illuminate\Pagination\LengthAwarePaginator  $items
     * @param array  $except 
     * @return array of stdClass Objects
     */  
    public function getRows(array $columns, LengthAwarePaginator $items, array $except = []): array
    {
        $rows = [];
        // Get pagination data for ordering.
        $pagination = [
            'currentPage' => $items->currentPage(),
            'count' => $items->count(),
            'hasMorePages' => $items->hasMorePages(),
            'perPage' => $items->perPage(),
            'rowPosition' => 0,
        ];

        foreach ($items as $i => $item) {
            // Check for extra attributes.
            if (method_exists($item, 'setExtraAttributes')) {
                $item->setExtraAttributes();
            }

            $pagination['rowPosition'] = $i + 1;
            $item->_row_pagination = $pagination;

            $row = $this->getRow($columns, $item, $except);
            $rows[] = $row;
        }

        return $rows;
    }

    /*
     * Returns a row tree list.
     *
     * @param array of stdClass Objects  $columns
     * @param \Kalnoy\Nestedset\Collection  $nodes
     * @param array  $except 
     * @return array of stdClass Objects
     */  
    public function getRowTree(array $columns, Collection $nodes, array $except = []): array
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
     * @param array of stdClass Objects  $columns
     * @param mixed   $item
     * @param array   $except 
     * @param string  $prefix
     * @return stdClass Object
     */  
    private function getRow(array $columns, mixed $item, array $except = [], string $prefix = ''): \stdClass
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
                    $format = (isset($column->format)) ? $column->format : '';
                    $row->{$column->name} = Setting::getFormattedDate($item->{$column->name}, $format);  
                }
                elseif ($column->name == 'owned_by') {
                    $row->owned_by = $item->owner_name;
                }
                elseif ($column->name == 'access_level') {
                    $row->access_level = __('labels.generic.'.$item->access_level);
                }
                elseif ($column->name == 'status') {
                    $type = (isset($column->status_type)) ? $column->status_type : 'generic';
                    $row->status = __('labels.'.$type.'.'.$item->status);
                }
                elseif (in_array($column->name, ['name', 'title']) && !empty($prefix)) {
                    $attributeName = ($column->name == 'name') ? 'name' : 'title';
                    $row->$attributeName = $prefix.' '.$item->$attributeName;
                }
                // Sets the ordering links according to the position of the item/node.
                elseif ($column->name == 'ordering') {
                    $ordering = [];

                    // Look for a possible collection type to use with the ordering routes.
                    $collectionType = (isset($this->item->collection_type)) ? '.'.Str::plural($this->item->collection_type) : '.'.Str::plural(strtolower($this->getUpperLevelClassName()));
                    // In case no type is found, set it as an empty string.
                    $collectionType = ($collectionType == '.') ? '' : $collectionType;

                    // Tree list type orderings.  
                    if (in_array($this->getClassName(), ['Item', 'Category'])) {
                        // A menu code variable is required for the menu item routes.
                        $query = ($this->getClassName() == 'Item') ? ['code' => $item->menu_code, 'item' => $item->id] : [strtolower($this->getClassName()) => $item->id];

                        if ($item->getPrevSibling()) { 
                            $ordering['up'] = route('admin'.$collectionType.'.'.Str::plural(strtolower($this->getClassName())).'.up', $query);
                        }

                        if ($item->getNextSibling()) { 
                            $ordering['down'] = route('admin'.$collectionType.'.'.Str::plural(strtolower($this->getClassName())).'.down', $query);
                        }
                    }
                    // Normal orderings
                    else {
                        // Get the current query from the Request facade and merge it with the item id.
                        $query = array_merge(\Request::query(), [strtolower($this->getClassName()) => $item->id]); 
                        $pagination = $item->_row_pagination;

                        if ($pagination['rowPosition'] != 1 || $pagination['currentPage'] != 1) {
                            $ordering['up'] = route('admin'.$collectionType.'.'.Str::plural(strtolower($this->getClassName())).'.up', $query);
                        }

                        if ($pagination['hasMorePages'] || $pagination['count'] != $pagination['rowPosition']) {
                            $ordering['down'] = route('admin'.$collectionType.'.'.Str::plural(strtolower($this->getClassName())).'.down', $query);
                        }
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
     * @param array  $except 
     * @return array of stdClass Objects
     */  
    public function getFields(array $except = []): array
    {
        $fields = $this->getData('fields');
        // Checks for field groups such as meta_data, extra_fields... (generally set in different json files).
        if (isset($this->item->fieldGroups)) {
            $fields = $this->getFieldGroups($fields, $this->item->fieldGroups);
        }

        foreach ($fields as $key => $field) {
            // Remove unwanted fields if any.
            if (in_array($field->name, $except)) {
                unset($fields[$key]);
                continue;
            }

            // Set the select field types.
            if ($field->type == 'select') {
                $fields[$key]->options = $this->getOptionList($field);
            }

            // Check if the object exists in database.
            if ($this->item->exists) {
                $item = $this->item;

                if ($field->type == 'select') {
                    $fields[$key]->value = $item->getSelectedValue($field);
                }
                elseif ($field->type == 'date') {
                    // Do not set any date value as it is handled by the datepicker after the page is loaded. 
                    $fields[$key]->value = null;

		    // Set date value and time value (if any) through datasets.
                    if (isset($fields[$key]->dataset)) {
                        // Check for time value.
                        if (isset($fields[$key]->dataset->time)) {
                            $datetime = $item->{$field->name}->tz(Setting::getValue('app', 'timezone'))->toDateTimeString();
                            $data = explode(' ', $datetime);
                            $fields[$key]->dataset->date = $data[0];
                            $fields[$key]->dataset->time = $data[1];
                        }
                        // date only
                        else {
                            $fields[$key]->dataset->date = $item->{$field->name}->tz(Setting::getValue('app', 'timezone'))->toDateString();
                        }
                    }
                }
                elseif ($field->name == 'updated_by') {
                    $fields[$key]->value = $item->modifier_name;
                }
                // Checks for values set in array as meta_data, extra_fields etc..
                elseif (isset($field->group) && isset($item->{$field->group})) {
                    $field->value = (isset($item->{$field->group}[$field->name])) ? $item->{$field->group}[$field->name] : null;
                }
                // Regular value field.
                else {
                    $fields[$key]->value = (isset($item->{$field->name})) ? $item->{$field->name} : null;
                }

                if (isset($item->access_level) && !$item->canEdit()) {
                    $field = $this->setExtraAttributes($field, ['disabled']);
                }

                if ($field->name == 'status' && !$item->canChangeStatus()) {
                    $field = $this->setExtraAttributes($field, ['disabled']);
                }

                if (isset($item->access_level) && in_array($field->name, ['access_level', 'owned_by', 'groups', 'categories', 'parent_id'])
                    && !$item->canChangeAccessLevel()) {
                    $field = $this->setExtraAttributes($field, ['disabled']);
                }

                if (method_exists($item, 'isParentPrivate') && $item->access_level == 'private') { 
                    // Check for parent or children private items then disable field(s) accordingly.
                    if ((in_array($field->name, ['access_level', 'owned_by']) &&
                          $item->isParentPrivate()) || ($field->name == 'owned_by'
                          && !$item->isParentPrivate())) {
                        $field = $this->setExtraAttributes($field, ['disabled']);
                    }

                    // Only the owner of the descendants private items can change their parents.
                    if ($field->name == 'parent_id' && $item->isParentPrivate() && $item->owned_by != auth()->user()->id) {
                        $field = $this->setExtraAttributes($field, ['disabled']);
                    }
                }

                if (method_exists($item, 'canDescendantsBePrivate') && $field->name == 'access_level'
                    && $item->access_level != 'private' && !$item->canDescendantsBePrivate()) { 
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
     * Gets and stores field groups as meta data etc...
     *
     * @param array  $fields
     * @param array  $groups
     * @return array of stdClass Objects
     */  
    public function getFieldGroups(array $fields, array $groups): array
    {
        foreach ($groups as $group) {
            // Check first that group name exists as attribute in the model.
            if (\Schema::hasColumn($this->item->getTable(), $group)) {
                $data = $this->getData($group);

                foreach ($data as $field) {
                    $fields[] = $field;
                }
            }
        }

        return $fields;
    }

    /*
     * Returns the filter data for an item list.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param array  $except 
     * @return array of stdClass Objects
     */  
    public function getFilters(Request $request, array $except = []): array
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
                // Check first if a function name is available or use the filter name.
                $name = (isset($filter->function)) ? $filter->function : $filter->name;
                // Build the function name.
                $function = 'get'.str_replace('_', '', ucwords($name, '_')).'Options';

                $default = ($filter->name == 'per_page') ? Setting::getValue('pagination', 'per_page') : $default;

                if ($filter->name == 'sorted_by') {
                    // The getSortedByOptions function requires arguments. 
                    $extra = (isset($filter->extra)) ? $filter->extra : [];
                    $options = $this->item->$function($this->getPathToForm(), $extra);
                }
                // Call the model method.
                else {
                    $options = $this->item->$function();
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
     * @param  array  $except
     * @return array of stdClass Objects
     */  
    public function getActions(string $section, array $except = []): array
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
     * @param  array  $fieldNames
     * @return array of stdClass Objects
     */  
    public function getSpecificFields(array $fieldNames): array
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
                $fields[$key]->options = $this->getOptionList($field);
            }
        }

        return $fields;
    }

    /*
     * Returns the most common field values that need to be updated after an AJAX query.
     *
     * @param Request $request
     * @return array
     */  
    public function getFieldValuesToUpdate(Request $request): array
    {
        $fields = [];
        // The one to one relation types that need to be updated.
        $relations = ['image', 'photo'];

        if ($this->item->updated_at) {
            $format = (isset($request->input('_date_formats')['updated_at'])) ? $request->input('_date_formats')['updated_at'] : '';
            $fields['updated_at'] = Setting::getFormattedDate($this->item->updated_at, $format); 
        }

        if ($this->item->updated_by) {
            $fields['updated_by'] = auth()->user()->name; 
        }

        if ($this->item->slug) {
            $fields['slug'] = $this->item->slug;
        }

        foreach ($relations as $relation) {
            if ($this->item->$relation) {
                // The field are named after the model name (in lowercase) and the relation name.
                $fields[strtolower($this->getClassName()).'-'.$relation] = url('/').'/storage/thumbnails/'.$this->item->$relation->disk_name;
                $fields[$relation] = '';
            }
        }

        if ($this->item->layoutItems) {
            foreach ($this->item->layoutItems as $layoutItem) {
                if ($layoutItem->type == 'image') {
                    $fields['layout-item-thumbnail-'.$layoutItem->id_nb] = url('/').'/'.$layoutItem->image->getThumbnailUrl();
                    $fields['layout-item-upload-'.$layoutItem->id_nb] = '';
                    $fields['layout-item-image-status-'.$layoutItem->id_nb] = 'update';
                }
            }
        }

        return $fields;
    }

    /*
     * Returns the options for a given select field.
     *
     * @param stdClass $field
     * @return array
     */  
    private function getOptionList(\stdClass $field): array
    {
        // Check first if a function name is available or use the field name.
        $name = (isset($field->function)) ? $field->function : $field->name;
        // Build the function name.
        $function = 'get'.str_replace('_', '', ucwords($name, '_')).'Options';

        // Call the model method.
        $options = $this->item->$function();

        if (isset($field->extra) && in_array('global_setting', $field->extra)) {
            $options[] = ['value' => 'global_setting', 'text' => __('labels.generic.global_setting')];
        }

        return $options;
    }

    /*
     * Adds one or more extra attributes to a given field.
     *
     * @param  stdClass $field
     * @param  array  $attributes
     * @return stdClass Object
     */  
    public function setExtraAttributes(\stdClass $field, array $attributes): \stdClass
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

    public function getClassName(): string
    {
        return class_basename(get_class($this->item));
    }

    /*
     * Gets a possible upper level class name in a namespace (eg: App\Models\->Post<-\Category)
     *
     * @return string|false
     */  
    public function getUpperLevelClassName(): string|false
    {
        if (preg_match('#Models\\\\([A-Z][a-z0-9]*)\\\\#', get_class($this->item), $matches)) {
            return $matches[1];
        }

        return false;
    }

    public function getPathToForm(): string
    {
        $path = ($this->getUpperLevelClassName()) ? $this->getUpperLevelClassName().'/'.$this->getClassName() : $this->getClassName();

        return app_path().'/Forms/'.$path;
    }

    /*
     * Gets a json file related to a given item then returns the decoded data.
     *
     * @param  string $type
     * @return mixed
     */  
    private function getData(string $type): mixed
    {
        // Check if a categorizable model overwrite the initial category file.
        // The overwritten file must live in: app/Form/ModelName/Category∕overwritten_file.json
        if (get_class($this->item) == Category::class && file_exists(app_path().'/Forms/'.ucfirst($this->item->collection_type).'/Category/'.$type.'.json')) {
            $json = file_get_contents(app_path().'/Forms/'.ucfirst($this->item->collection_type).'/Category/'.$type.'.json', true);
        }
        else {
            $json = file_get_contents($this->getPathToForm().'/'.$type.'.json', true);
        }

        if ($json === false) {
           throw new Exception('Load Failed');    
        }

        return json_decode($json);
    }
}
