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

    /**
     * Delete the model from the database (override).
     *
     * @return bool|null
     *
     * @throws \LogicException
     */
    public function delete()
    {
        if ($this->image) {
            $this->image->delete();
        }

        parent::delete();
    }

    public static function storeItems(object $model): array
    {
        $refresh = [];

        if (isset(request()->all()['layout_items'])) {
            $items = request()->all()['layout_items'];

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
                        if (!$image = $item->uploadImage($id)) {
                            // Use the current image.
                            $image = $item->image;
                        }

                        // Store value as JSON.
                        $item->value = json_encode(['alt_text' => $items['alt_text_'.$id], 'url' => $image->getUrl(), 'thumbnail' => $image->getThumbnailUrl()]);
                        $item->order = $items['layout_item_ordering_'.$id];
                        $item->save();

                        $refresh['layout-item-thumbnail-'.$id] = url('/').'/'.$image->getThumbnailUrl();
                        $refresh['layout-item-upload-'.$id] = '';
                    }

                    continue;
                }

                // Regular items.
                if (preg_match('#^([a-z]+)_([a-z]*)_?([0-9]+)$#', $key, $matches)) {
                    // Concatenate possible compound type names (eg: group_start).
                    $type = (!empty($matches[2])) ? $matches[1].'_'.$matches[2] : $matches[1];
                    $id = $matches[3];
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

        return $refresh;
    }

    public static function hasImageFile(int $id): bool
    {
        return (Request::hasFile('layout_items.upload_'.$id) && Request::file('layout_items.upload_'.$id)->isValid()); 
    }

    public function uploadImage(int $id): ?Document
    {
        $image = null;

        if (LayoutItem::hasImageFile($id)) {
            // The image has been replaced.
            if ($this->image) {
                $this->image->delete();
            }

            $image = new Document;
            $image->upload(Request::file('layout_items.upload_'.$id), 'image');
            $this->image()->save($image);
        }

        return $image;
    }
}

