<?php

namespace App\Models\Cms;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\Setting;


class Document extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'item_type',
        'field',
        'disk_name',
        'file_name',
        'file_size',
        'content_type',
    ];


    /*
     * Stores the file linked to a document model.
     * @param  Illuminate\Http\UploadedFile  $file
     * @param  string  $itemType
     * @param  string  $fieldName
     * @return void
     */
    public function upload($file, $itemType, $fieldName, $public = true)
    {
        $this->item_type = $itemType;
        $this->field = $fieldName;
        $this->disk_name = md5($file->getClientOriginalName().microtime()).'.'.$file->getClientOriginalExtension();
        $this->file_name = $file->getClientOriginalName();
        $this->file_size = $file->getSize();
        $this->content_type = $file->getMimeType();
        $this->is_public = $public;
	$path = ($public) ? 'public' : 'uploads';

	Storage::disk('local')->putFileAs($path, $file, $this->disk_name);

	if (preg_match('#^image\/#', $this->content_type)) {
	    $imagePath = Storage::disk('local')->path(null).$path;
	    $this->createThumbnail($imagePath);
	}

	return;
    }

    /*
     * Gets the current user's document items according to the filter, sort and pagination settings.
     */
    public function getFileManagerItems($request)
    {
        $perPage = $request->input('per_page', Setting::getValue('pagination', 'per_page'));
        $search = $request->input('search', null);
        $sortedBy = $request->input('sorted_by', null);
        $types = $request->input('types', []);

	$query = Document::query();
	$query->where(['item_type' => 'user', 'owned_by' => auth()->user()->id, 'field' => 'file_manager', 'is_public' => 1]);

	if ($search !== null) {
	    $query->where('file_name', 'like', '%'.$search.'%');
	}

	if (!empty($types)) {
	    $query->where('content_type', 'regexp', '^('.implode('|', $types).')');
	}

	if ($sortedBy !== null) {
	    preg_match('#^([a-z0-9_]+)_(asc|desc)$#', $sortedBy, $matches);
	    $query->orderBy($matches[1], $matches[2]);
	}

        $items = $query->paginate($perPage);

	foreach ($items as $key => $item) {
	    // Set the file url.
	    $items[$key]->url = url('/').'/storage/'.$item->disk_name;

	    // Set the thumbnail url for images. 
	    if (preg_match('#^image\/#', $item->content_type)) {
		$items[$key]->thumbnail = url('/').'/storage/thumbnails/'.$item->disk_name;
	    }

	    $items[$key]->file_size = $this->formatSizeUnits($items[$key]->file_size);
	}

	return $items;
    }

    /*
     * Gets the document items uploaded by the all the users from the file manager.
     */
    public function getAllFileManagerItems($request)
    {
        $perPage = $request->input('per_page', Setting::getValue('pagination', 'per_page'));
        $search = $request->input('search', null);
        $sortedBy = $request->input('sorted_by', null);
        $types = $request->input('types', []);
        $owners = $request->input('owned_by', []);

	$query = Document::query();
	$query->select('documents.*', 'users.name as owner_name')
	      ->leftJoin('users', 'documents.owned_by', '=', 'users.id')
	      ->join('model_has_roles', 'documents.owned_by', '=', 'model_id')
	      ->join('roles', 'roles.id', '=', 'role_id');

	$query->where(['item_type' => 'user', 'field' => 'file_manager', 'is_public' => 1]);

	// Check for role levels.
	$query->where(function($query) {
	    $query->where('roles.role_level', '<', auth()->user()->getRoleLevel())
		  ->orWhere('documents.owned_by', auth()->user()->id);
	});

	if ($search !== null) {
	    $query->where('file_name', 'like', '%'.$search.'%');
	}

	if (!empty($types)) {
	    $query->where('content_type', 'regexp', '^('.implode('|', $types).')');
	}

	if (!empty($owners)) {
	    $query->whereIn('owned_by', $owners);
	}

	if ($sortedBy !== null) {
	    preg_match('#^([a-z0-9_]+)_(asc|desc)$#', $sortedBy, $matches);
	    $query->orderBy($matches[1], $matches[2]);
	}

        $items = $query->paginate($perPage);

	foreach ($items as $key => $item) {
	    // Set the file url.
	    $items[$key]->url = url('/').'/storage/'.$item->disk_name;

	    // Set the thumbnail url for images. 
	    if (preg_match('#^image\/#', $item->content_type)) {
		$items[$key]->thumbnail = url('/').'/storage/thumbnails/'.$item->disk_name;
	    }

	    $items[$key]->file_size = $this->formatSizeUnits($items[$key]->file_size);
	}

	return $items;
    }

    /*
     * Builds the options for the 'types' select field.
     */
    public function getTypesOptions()
    {
        $types = ['image', 'application', 'audio', 'video', 'text', 'font'];
	$options = [];

	foreach ($types as $type) {
	    $options[] = ['value' => $type, 'text' => $type];
	}

	return $options;
    }

    public function getOwnedByOptions()
    {
	$query = Document::query();
	$query->select(['users.id', 'users.name'])
	      ->leftJoin('users', 'documents.owned_by', '=', 'users.id')
	      ->join('model_has_roles', 'documents.owned_by', '=', 'model_id')
	      ->join('roles', 'roles.id', '=', 'role_id');

	//$query->where(['item_type' => 'user', 'field' => 'file_manager', 'is_public' => 1]);

	// Check for access levels.
	$query->where(function($query) {
	    $query->where('roles.role_level', '<', auth()->user()->getRoleLevel())
		  ->orWhere('documents.owned_by', auth()->user()->id);
	});

	$owners = $query->distinct()->get();

	$options = [];

	foreach ($owners as $owner) {
	    $options[] = ['value' => $owner->id, 'text' => $owner->name];
	}

	return $options;
    }

    /*
     * Override.
     */
    public function delete()
    {
        // Removes the file from the server.
	$path = ($this->is_public) ? 'public/' : 'uploads/';
	Storage::delete($path.$this->disk_name);

	if (preg_match('#^image\/#', $this->content_type)) {
	    // Removes the corresponding thumbnail.
	    Storage::delete($path.'thumbnails/'.$this->disk_name);
	}

	// Then deletes the model.
        parent::delete();
    }

    /*
     * Returns a relative url to the file linked to the document.
     */
    public function getUrl()
    {
        return Storage::url($this->disk_name);
    }

    /*
     * Returns a relative url to the thumbnail of the image file linked to the document.
     */
    public function getThumbnailUrl()
    {
        return Storage::url('thumbnails/'.$this->disk_name);
    }

    /*
     * Returns the absolute path to the file linked to the document.
     */
    public function getPath()
    {
        return Storage::path($this->disk_name);
    }

    /*
     * Returns the given bytes in the proper size format according to the byte units.
     * @return string
     */
    public function formatSizeUnits($bytes)
    {
        if ($bytes >= 1073741824) {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        }
        elseif ($bytes >= 1048576) {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        }
        elseif ($bytes >= 1024) {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        }
        elseif ($bytes > 1) {
            $bytes = $bytes . ' bytes';
        }
        elseif ($bytes == 1) {
            $bytes = $bytes . ' byte';
        }
        else {
            $bytes = '0 bytes';
        }

        return $bytes;
    }

    /*
     * Creates a thumbnail for a given image.
     * @return void
     *
     * Source: https://code.tutsplus.com/tutorials/how-to-create-a-thumbnail-image-in-php--cms-36421
     */
    private function createThumbnail($imagePath, $thumbWidth = 100)
    {
        // Set the name of the PHP functions to use according to the image extension (ie: imagecreatefromjpeg(), imagegif()... ).
        $extension = strtolower(pathinfo($imagePath.'/'.$this->disk_name, PATHINFO_EXTENSION));
        $suffixes = ['jpg' => 'jpeg', 'jpeg' => 'jpeg', 'png' => 'png', 'gif' => 'gif', 'bmp' => 'wbmp'];
	$imagecreatefrom = 'imagecreatefrom'.$suffixes[$extension];
	$image = 'image'.$suffixes[$extension];

        $sourceImage = $imagecreatefrom($imagePath.'/'.$this->disk_name);
        $orgWidth = imagesx($sourceImage);
        $orgHeight = imagesy($sourceImage);
        $thumbHeight = floor($orgHeight * ($thumbWidth / $orgWidth));
        $destImage = imagecreatetruecolor($thumbWidth, $thumbHeight);
        imagecopyresampled($destImage, $sourceImage, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $orgWidth, $orgHeight);

	// Create the thumbnails directory if it doesn't exist.
	if (!File::exists($imagePath.'/thumbnails')) {
	    File::makeDirectory($imagePath.'/thumbnails', 0755, true, true);
	}

	// Store the file in the thumbnail directory as the original file name.
        $image($destImage, $imagePath.'/thumbnails/'.$this->disk_name);
        imagedestroy($sourceImage);
        imagedestroy($destImage);

	return;
    }
}
