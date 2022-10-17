<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;
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

    /**
     * Get the image associated with the layout item.
     */
    public function image()
    {
        return $this->morphOne(Document::class, 'documentable')->where('field', 'image');
    }

    public static function storeItems($model, $items)
    {
        $refresh = [];

        if (isset(request()->all()['layout_items'])) {
            $items = request()->all()['layout_items'];

//file_put_contents('debog_file.txt', print_r($items, true));
            foreach ($items as $key => $value) {
                // Image items.
                if (preg_match('#^([upload|alt_text]{1,})_([0-9]+)$#', $key, $matches)) {
                    $id = $matches[2];
                    $item = $model->layoutItems->where('id_nb', $id)->first();

                    // The image item is new but no image has been selected OR an image is selected so the 
                    // alt_text field will be treated in the uploadImage function or has already been treated. 
                    if (($matches[1] == 'alt_text' && !LayoutItem::hasImageFile($id) && !$item) || ($matches[1] == 'alt_text' && LayoutItem::hasImageFile($id))) {
                        continue;
                    }

                    // Make sure a image file exists before creating a brand new item.
                    if (!$item && LayoutItem::hasImageFile($id)) {
                        $item = LayoutItem::create(['type' => 'image', 'id_nb' => $id]);
                        $model->layoutItems()->save($item);
                    }

                    if ($item) {
                        LayoutItem::uploadImage($item, $id);

                        $item->value = json_encode(['alt_text' => $items['alt_text_'.$id], 'thumbnail' => $item->image->getThumbnailUrl()]);
                        //$item->value = json_encode(['alt_text' => $items['alt_text_'.$id], 'thumbnail' => $image->getThumbnailUrl()]);
                        $item->order = $items['layout_item_ordering_'.$id];
                        $item->save();

                        $refresh['layout-item-thumbnail-'.$id] = $item->image->getThumbnailUrl();
                        $refresh['layout-item-upload-'.$id] = '';
                    }

                    continue;
                }

                // Regular items.
                if (preg_match('#^([a-z]+)_([0-9]+)$#', $key, $matches)) {
                    $type = $matches[1];
                    $id = $matches[2];
                    $order = $items['layout_item_ordering_'.$id];
                    //$item = $model->layoutItems->where('id_nb', $id)->first();

                    /*if (!$item) {
                        $item = LayoutItem::create(['type' => $type, 'id_nb' => $id]);
                        $model->layoutItems()->save($item);
                    }*/

                    if ($item = $model->layoutItems->where('id_nb', $id)->first()) {
                        $item->value = $value;
                        $item->order = $order;
                        $item->save();
                    }
                    else {
                        $item = LayoutItem::create(['type' => $type, 'id_nb' => $id, 'value' => $value, 'order' => $order]);
                        $model->layoutItems()->save($item);
                    }

                    /*if ($type == 'image') {
                        LayoutItem::uploadImage($item, $id);
                        $item->value = json_encode(['alt_text' => $items['alt_text_'.$id], 'thumbnail' => $item->image->getThumbnailUrl()]);

                        $refresh['layout-item-thumbnail-'.$id] = url('/').'/'.$item->image->getThumbnailUrl();
                        $refresh['layout-item-upload-'.$id] = '';
                    }
                    else {
                        $item->value = $value;
                    }

                    $item->order = $order;
                    $item->save();*/
                }
            }
        }

        return $refresh;
    }

    public static function hasImageFile($id)
    {
        return (Request::hasFile('layout_items.upload_'.$id) && Request::file('layout_items.upload_'.$id)->isValid()); 
    }

    public static function uploadImage(&$item, $id)
    {
        //$upload = (Request::hasFile('layout_items.upload_'.$id) && Request::file('layout_items.upload_'.$id)->isValid());
        //$item = $model->layoutItems->where('id_nb', $id)->first();

        //
        /*if (!$item && $upload) {
            $item = LayoutItem::create(['type' => 'image', 'id_nb' => $id]);
            $model->layoutItems()->save($item);
        }*/

        if (LayoutItem::hasImageFile($id)) {
            // The image has been replaced.
            if ($item->image) {
                $item->image->delete();
            }

            $image = new Document;
            $image->upload(Request::file('layout_items.upload_'.$id), 'image');
            $item->image()->save($image);
            $item->refresh();
        }

        //return $image;
    }
}

