<?php

namespace App\Models\Post;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'post_settings';

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
        $results = Setting::all()->toArray();
	$data = [];

	foreach ($results as $param) {
	    if (!isset($data[$param['group']])) {
		$data[$param['group']] = [];
	    }

	    $data[$param['group']][$param['key']] = $param['value'];
	}

	return $data;
    }

    public static function getDataByGroup($group)
    {
        $results = Setting::where('group', $group)->get();
	$data = [];

	foreach ($results as $param) {
	    $data[$param->key] = $param->value;
	}

	return $data;
    }

    public static function getItemSettings($item, $group)
    {
        // Get the global settings of the item.
	$globalSettings = Setting::getDataByGroup($group);
	$settings = [];

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

    public static function getSegments(): array
    {
        $results = Setting::whereIn('key', ['post_segment', 'category_segment', 'plugin_segment'])->select('key', 'value')->get();
        $defaults = ['post_segment' => 'post', 'category_segment' => 'category', 'plugin_segment' => 'blog'];
        $segments = [];

        foreach ($results as $result) {
            // Remove the '_segment' part from the key.
            $key = substr($result->key, 0, strpos($result->key, '_'));
            $segments[$key] = ($result->value) ? $result->value : $defaults[$result->key];
        }

        return $segments;
    }

    public static function getPostOrderingOptions(): array
    {
      return [
	  ['value' => 'no_ordering', 'text' => __('labels.generic.no_ordering')],
	  ['value' => 'title_asc', 'text' => __('labels.generic.title_asc')],
	  ['value' => 'title_desc', 'text' => __('labels.generic.title_desc')],
	  ['value' => 'created_at_asc', 'text' => __('labels.generic.created_at_asc')],
	  ['value' => 'created_at_desc', 'text' => __('labels.generic.created_at_desc')],
	  ['value' => 'updated_at_asc', 'text' => __('labels.generic.updated_at_asc')],
	  ['value' => 'updated_at_desc', 'text' => __('labels.generic.updated_at_desc')],
	  //['value' => 'ordering_asc', 'text' => __('labels.generic.ordering_asc')],
	  //['value' => 'ordering_desc', 'text' => __('labels.generic.ordering_desc')],
      ];
    }
}
