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

    /**
     * Get the image associated with the layout item.
     */
    public function image()
    {
        return $this->morphOne(Document::class, 'documentable')->where('field', 'image');
    }

    public static function storeItems($model, $items)
    {
file_put_contents('debog_file.txt', print_r($items, true));
return;
        foreach ($items as $key => $value) {
            $type = $id = $order = null;

            if (str_starts_with($key, 'image_') || str_starts_with($key, 'alt_image_')) {
                preg_match('#_([0-9]+)$#', $key, $matches);
                continue;
            }

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
            }
        }
    }

    public static function storeImage($model, $items, $id)
    {
        $order = $items['layout_item_ordering_'.$id];
        $upload = (isset($items['image_'.$id])) ? $items['image_'.$id] : null;
        $alt = $items['alt_image_'.$id];

        if ($item = $model->layoutItems->where('id_nb', $id)->first()) {
            // The image has been replaced.
            if ($upload) {
                $item->image->delete();

                if ($image = $item->setImageData($upload)) {
                    $item->image()->save($image);
                }
            }

            $item->order = $order;
            $item->save();
        }
        // New image to upload.
        elseif ($upload) {
        }
    }

    private function setImageData($upload)
    {
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $image = new Document;
            $image->upload($request->file('image'), 'image');

            return $image;
        }

        return null;
    }
}

