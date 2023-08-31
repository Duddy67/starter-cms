<?php

namespace App\Models\Post;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\OptionList;

class Setting extends Model
{
    use HasFactory, OptionList;

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


    /**
     * Function shared with all the categorizable models.
     */
    public static function getItemOrderingOptions(): array
    {
      return [
	  ['value' => 'no_ordering', 'text' => __('labels.generic.no_ordering')],
	  ['value' => 'title_asc', 'text' => __('labels.generic.title_asc')],
	  ['value' => 'title_desc', 'text' => __('labels.generic.title_desc')],
	  ['value' => 'created_at_asc', 'text' => __('labels.generic.created_at_asc')],
	  ['value' => 'created_at_desc', 'text' => __('labels.generic.created_at_desc')],
	  ['value' => 'updated_at_asc', 'text' => __('labels.generic.updated_at_asc')],
	  ['value' => 'updated_at_desc', 'text' => __('labels.generic.updated_at_desc')],
	  ['value' => 'order_asc', 'text' => __('labels.generic.order_asc')],
	  ['value' => 'order_desc', 'text' => __('labels.generic.order_desc')],
      ];
    }
}
