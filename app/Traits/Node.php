<?php

namespace App\Traits;

use Kalnoy\Nestedset\NodeTrait;

trait Node
{
    use NodeTrait;

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
        $leaves = get_class($this)::whereDescendantOf($this)->whereIsLeaf()->get();

        while ($leaves->isNotEmpty()) {
            foreach ($leaves as $leaf) {
                $leaf->delete();
            }

            $leaves = get_class($this)::whereDescendantOf($this)->whereIsLeaf()->get();
        }
    }
}

