<?php

namespace App\Models\Menu;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Menu;
use Kalnoy\Nestedset\NodeTrait;
use App\Models\User\Group;
use App\Models\Translation;
use App\Traits\CheckInCheckOut;
use Request;


class Item extends Model
{
    use HasFactory, NodeTrait, CheckInCheckOut;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'menu_items';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'model',
        'class',
        'anchor',
        'status',
        'parent_id',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'checked_out_time'
    ];

    /**
     * Get all of the item's translations.
     */
    public function translations()
    {
        return $this->morphMany(Translation::class, 'translatable');
    }

    /**
     * Get a given item's translation.
     */
    public function getTranslation($locale)
    {
        return $this->morphMany(Translation::class, 'translatable')->where('locale', $locale)->first();
    }

    /**
     * Get a given item's translation or create it if it doesn't exist.
     */
    public function getOrCreateTranslation($locale)
    {
        $translation = $this->getTranslation($locale);

        if ($translation === null) {
            $translation =  Translation::create(['locale' => $locale]);
            $this->translations()->save($translation);
        }

        return $translation;
    }


    /*
     * Gets the menu items as a tree.
     */
    public function getItems($request, $code)
    {
        $search = $request->input('search', null);

        return Item::select('menu_items.*', 'translations.title as title', 'translations.url as url')
                     ->where('menu_code', $code)->join('translations', function ($join) use($search) { 
                         $join->on('menu_items.id', '=', 'translatable_id')
                              ->where('translations.translatable_type', '=', 'App\Models\Menu\Item')
                              ->where('locale', '=', config('app.locale'));

                              if ($search !== null) {
                                  $join->where('translations.title', 'like', '%'.$search.'%');
                              }
               })->defaultOrder()->get()->toTree();
    }

    public static function getItem($id, $locale = null)
    {
        $locale = ($locale === null) ? config('app.locale') : $locale;

        return Item::select('menu_items.*','users.name as modifier_name', 'translations.title as title', 'translations.url as url')
            ->leftJoin('users as users', 'menu_items.updated_by', '=', 'users.id')
            ->join('translations', function ($join) use($locale) { 
                $join->on('menu_items.id', '=', 'translatable_id')
                     ->where('translations.translatable_type', '=', 'App\Models\Menu\Item')
                     ->where('locale', '=', $locale);
        })->findOrFail($id);
    }

    /*
     * Parses the segment variables (if any) then returns the corresponding url.
     */
    public function getUrl(): string
    {
        // First check for segment variables in the item url.
        if (strpos($this->url, '{') === false) {
            return $this->url;
        }

        $parts = explode('/', $this->url);

        if ($parts[0] == '') {
            unset($parts[0]);
        }

        $url = '';

        foreach ($parts as $part) {
            if (substr($part, 0, 1) == '{') {
                $segment = str_replace(str_split('{}'), '', $part);
                $segments = $this->model::getSegments();

                if (isset($segments->{$segment})) {
                    $url .= '/'.$segments->{$segment};
                }
                // If the segment variable doesn't exist, return the url as it is.
                else {
                    return $this->url;
                }
            }
            else {
                $url .= '/'.$part;
            }
        }

        return $url;
    }

    public function getParentIdOptions()
    {
        // Get the parent menu code.
        $code = Request::route()->parameter('code');

        $nodes = Item::select('menu_items.*', 'translations.title as title')
            ->whereIn('menu_code', ['root', $code])
            ->join('translations', function($join) {
                $join->on('menu_items.id', '=', 'translatable_id')
                     ->where('translations.translatable_type', '=', 'App\Models\Menu\Item')
                     ->where('locale', '=', config('app.locale'));
        })->get()->toTree();

        // Defines the state of the current instance.
        $isNew = ($this->id) ? false : true;
        $options = [];

        $traverse = function ($menuItems, $prefix = '-') use (&$traverse, &$options, $isNew) {

            foreach ($menuItems as $menuItem) {
                $options[] = ['value' => $menuItem->id, 'text' => $prefix.' '.$menuItem->title];

                $traverse($menuItem->children, $prefix.'-');
            }
        };

        $traverse($nodes);

        return $options;
    }

    public function canChangeStatus()
    {
        // Rely on the parent menu for authorisations.
        $menu = Menu::where('code', $this->menu_code)->first();

        return $menu->canChangeStatus();
    }

    /*
     * Generic function that returns model values which are handled by select inputs. 
     */
    public function getSelectedValue($fieldName)
    {
        return $this->{$fieldName};
    }
}
