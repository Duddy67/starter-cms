<?php

namespace App\Models\Menu;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User\Group;
use App\Models\Menu\MenuItem;
use App\Models\Settings\General;
use App\Traits\Admin\AccessLevel;
use App\Traits\Admin\CheckInCheckOut;


class Menu extends Model
{
    use HasFactory, AccessLevel, CheckInCheckOut;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'code',
        'status',
        'owned_by',
        'access_level',
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
     * The groups that belong to the menu.
     */
    public function groups()
    {
        return $this->belongsToMany(Group::class);
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
        $this->groups()->detach();

        // Get the parent menu items linked to this menu.
        $menuItems = MenuItem::where(['menu_code' => $this->code, 'parent_id' => 1])->get();

        foreach ($menuItems as $menuItem) {
            // Delete the parent menu item as well as its children (if any).
            $menuItem->delete();
        }

        parent::delete();
    }

    /*
     * Gets the menus according to the filter settings.
     */
    public function getItems($request)
    {
        $perPage = $request->input('per_page', General::getValue('pagination', 'per_page'));
        $search = $request->input('search', null);
        $sortedBy = $request->input('sorted_by', null);
        $ownedBy = $request->input('owned_by', null);
        $groups = $request->input('groups', []);

        $query = Menu::query();
        $query->select('menus.*', 'users.name as owner_name')->leftJoin('users', 'menus.owned_by', '=', 'users.id');
        // Join the role tables to get the owner's role level.
        $query->join('model_has_roles', 'menus.owned_by', '=', 'model_id')->join('roles', 'roles.id', '=', 'role_id');

        if ($search !== null) {
            $query->where('menus.title', 'like', '%'.$search.'%');
        }

        if ($sortedBy !== null) {
            preg_match('#^([a-z0-9_]+)_(asc|desc)$#', $sortedBy, $matches);
            $query->orderBy($matches[1], $matches[2]);
        }

        if ($ownedBy !== null) {
            $query->whereIn('menus.owned_by', $ownedBy);
        }

        if (!empty($groups)) {
            $query->whereHas('groups', function($query) use($groups) {
                $query->whereIn('id', $groups);
            });
        }

        $query->where(function($query) {
            $query->where('roles.role_level', '<', auth()->user()->getRoleLevel())
                  ->orWhereIn('menus.access_level', ['public_ro', 'public_rw'])
                  ->orWhere('menus.owned_by', auth()->user()->id);
        });

        $groupIds = auth()->user()->getGroupIds();

        if(!empty($groupIds)) {
            $query->orWhereHas('groups', function ($query)  use ($groupIds) {
                $query->whereIn('id', $groupIds);
            });
        }

        return $query->paginate($perPage);
    }

    private function getMenuItemChildren($menuItems, $item, $node)
    {
        // Loop through the existing menu items.
        foreach ($menuItems as $key => $menuItem) {
            if ($menuItem->id == $node->parent_id) {
                $menuItems[$key]->children[] = $item;
            }
            // Search for sub-children.
            elseif (!empty($menuItems[$key]->children)) {
                // Recursive call.
                $this->getMenuItemChildren($menuItems[$key]->children, $item, $node);
            }
        }

        return $menuItems;
    }

    public function getMenuItems()
    {
        $nodes = MenuItem::where('menu_code', $this->code)->defaultOrder()->get()->toTree();
        $menuItems = [];

        $traverse = function ($nodes, $level = 0) use (&$traverse, &$menuItems) {

            foreach ($nodes as $node) {
                $item = new \stdClass();
                $item->id = $node->id;
                $item->title = $node->title;
                $item->url = $node->url;
                $item->level = $level;
                $item->parent_id = $node->parent_id;
                $item->children = [];

                $parent = MenuItem::findOrFail($node->parent_id);

                if ($parent->menu_code != 'root') {
                    $menuItems = $this->getMenuItemChildren($menuItems, $item, $node);
                }
                else {
                    $menuItems[] = $item;
                }

                $traverse($node->children, $level + 1);
            }
        };

        $traverse($nodes);

        return $menuItems;
    }

    public function getOwnedByOptions()
    {
        $users = auth()->user()->getAssignableUsers(['manager', 'assistant', 'registered']);
        $options = [];

        foreach ($users as $user) {
            $options[] = ['value' => $user->id, 'text' => $user->name];
        }

        return $options;
    }

    /*
     * Generic function that returns model values which are handled by select inputs. 
     */
    public function getSelectedValue($fieldName)
    {
        if ($fieldName == 'groups') {
            return $this->groups->pluck('id')->toArray();
        }

        return $this->{$fieldName};
    }

    /*
     * Returns the menus according to the current user's role level and groups.
     */
    public static function getMenus()
    {
        $query = Menu::query();
        // Join the role tables to get the owner's role level.
        $query->select('menus.*')->join('model_has_roles', 'menus.owned_by', '=', 'model_id')
                                 ->join('roles', 'roles.id', '=', 'role_id');

        // Check for access levels.
        $query->where(function($query) {
            $query->where('roles.role_level', '<', auth()->user()->getRoleLevel())
                  ->orWhereIn('menus.access_level', ['public_ro', 'public_rw'])
                  ->orWhere('menus.owned_by', auth()->user()->id);
        });

        $groupIds = auth()->user()->getGroupIds();

        if (!empty($groupIds)) {
            // Check for access through groups.
            $query->orWhereHas('groups', function ($query)  use ($groupIds) {
                $query->whereIn('id', $groupIds);
            });
        }

        return $query->get();
    }

    /*
     * Returns the menu corresponding to the given code.
     */
    public static function getMenu($code)
    {
        if ($menu = Menu::where(['code' => $code, 'status' => 'published'])->first()) {

            if ($menu->canAccess()) {
                return $menu;
            }
        }

        return null;
    }
}
