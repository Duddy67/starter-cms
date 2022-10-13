<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Cms\Document;

class LayoutItem extends Model
{
    use HasFactory;

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id_nb',
        'type',
        'value',
        'order',
    ];

    /**
     * Get all of the owning layoutItemable models.
     */
    public function layoutItemable()
    {
        return $this->morphTo();
    }

    public static function storeItems($model, $items)
    {
        $ids = [];

        foreach ($items as $key => $value) {
            $type = $id = $order = null;

            if (preg_match('#^([a-z]+)_([0-9]+)$#', $key, $matches)) {
                $type = $matches[1];
                $id = $matches[2];
                $order = $items['layout_item_ordering_'.$id];

                if ($item = $model->layoutItems->where('id_nb', $id)->first()) {
                    $item->value = $value;
                    $item->order = $order;
                    $item->save();
                }
                else {
                    $item = LayoutItem::create(['type' => $type, 'id_nb' => $id, 'value' => $value, 'order' => $order]);
                    $model->layoutItems()->save($item);
                }

                $ids[] = $item->id;
            }
        }

//file_put_contents('debog_file.txt', print_r($items, true));
    }
}
