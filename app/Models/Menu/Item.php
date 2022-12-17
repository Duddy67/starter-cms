<?php

namespace App\Models\Menu;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Menu;
use Kalnoy\Nestedset\NodeTrait;
use App\Models\User\Group;
use App\Models\Setting;
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
        'title',
        'url',
        'model_name',
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


    /*
     * Gets the menu items as a tree.
     */
    public function getItems($request, $code)
    {
        $search = $request->input('search', null);

        if ($search !== null) {
            return Item::where('title', 'like', '%'.$search.'%')->get();
        }
        else {
          return Item::where('menu_code', $code)->defaultOrder()->get()->toTree();
        }
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
                $segments = Setting::getSegments($this->model_name);

                if (isset($segments[$segment])) {
                    $url .= '/'.$segments[$segment];
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

        $nodes = Item::whereIn('menu_code', ['root', $code])->get()->toTree();
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

    /*
     *  Overwrite the deleteDescendants() NodeTrait function.
     *
     *  Delete the node's descendants leaf node by leaf node in order to use the delete
     *  method of the model.
     *
     *  Note:
     *  The NodeTrait uses the statement: $node->descendants()->delete(). However, descendants()
     *  returns a QueryBuilder object and the delete() statement delete all related elements using
     *  a QueryBuilder delete statement.
     *  Given it's not an Eloquent statement it doesn't trigger any Eloquent events and thus the 
     *  delete() method of the model is not called.
     *
     *  https://github.com/lazychaser/laravel-nestedset/issues/568
     *  https://laracasts.com/discuss/channels/eloquent/is-it-possible-override-the-delete-method
     */
    public function deleteDescendants()
    {
        $leaves = Item::whereDescendantOf($this)->whereIsLeaf()->get();

        while ($leaves->isNotEmpty()) {
            foreach ($leaves as $leaf) {
                $leaf->delete();
            }

            $leaves = Item::whereDescendantOf($this)->whereIsLeaf()->get();
        }
    }
}
