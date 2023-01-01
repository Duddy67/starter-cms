<?php

namespace App\Traits;

use App\Models\User;

trait AccessLevel
{
    /*
     * Checks whether the current user is allowed to to change the access level of a given item.
     *
     * @param Object  $item
     * @return bool
     */
    public function canChangeAccessLevel($guard = 'web'): bool
    {
        return ($this->owned_by == auth($guard)->user()->id || auth($guard)->user()->getRoleLevel() > $this->getOwnerRoleLevel($guard)) ? true: false;
    }

    /*
     * Checks whether the current user is allowed to access a given item.
     *
     * @param Object  $item
     * @return bool
     */
    public function canAccess($guard = 'web'): bool
    {
        return (in_array($this->access_level, ['public_ro', 'public_rw']) || $this->shareGroups($guard) || $this->canEdit($guard)) ? true : false;
    }

    /*
     * Checks whether the current user is allowed to edit a given item.
     *
     * @param Object  $item
     * @return bool
     */
    public function canEdit($guard = 'web'): bool
    {
        if (!auth($guard)->check()) {
            // The user must be authenticated to edit.
            return false;
        }

        if ($this->access_level == 'public_rw' || $this->getOwnerRoleLevel($guard) < auth($guard)->user()->getRoleLevel() || $this->owned_by == auth($guard)->user()->id || $this->shareReadWriteGroups($guard)) {
            return true;
        }

        return false;
    }

    /*
     * Checks whether the current user is allowed to delete a given item according to their role level.
     *
     * @return bool
     */
    public function canDelete($guard = 'web'): bool
    {
        // The owner role level is lower than the current user's or the current user owns the item.
        if ($this->getOwnerRoleLevel($guard) < auth($guard)->user()->getRoleLevel() || $this->owned_by == auth($guard)->user()->id) {
            return true;
        }

        return false;
    }

    /*
     * Returns the role level of the item's owner.
     *
     * @return integer
     */
    public function getOwnerRoleLevel($guard = 'web'): int
    {
        $owner = ($this->owned_by == auth($guard)->user()->id) ? auth($guard)->user() : User::findOrFail($this->owned_by);

        return $owner->getRoleLevel();
    }

    /*
     * Checks whether the current user is allowed to change the status level of a given item.
     *
     * @return bool
     */
    public function canChangeStatus($guard = 'web'): bool
    {
        // Use the access level constraints.
        return $this->canChangeAccessLevel($guard);
    }

    /*
     * Checks whether the current user is allowed to change the owner,
     * categories or parent category of a given item.
     *
     * @return bool
     */
    public function canChangeAttachments($guard = 'web'): bool
    {
        // Use the access level constraints.
        return $this->canChangeAccessLevel($guard);
    }

    /**
     * The group ids the item is in.
     *
     * @return array 
     */
    public function getGroupIds(): array
    {
        return ($this->groups !== null) ? $this->groups()->pluck('groups.id')->toArray() : [];
    }

    /**
     * The group ids with read/write permission the item is in.
     *
     * @return array
     */
    public function getReadWriteGroupIds(): array
    {
        return ($this->groups !== null) ? $this->groups()->where('permission', 'read_write')->pluck('groups.id')->toArray() : [];
    }

    /*
     * Check if the item share one or more groups with the current user.
     *
     * @return bool
     */
    public function shareGroups($guard = 'web'): bool
    {
        if (!auth($guard)->check()) {
            // The user must be authenticated to share groups.
            return false;
        }

        $groups = array_intersect($this->getGroupIds(), auth($guard)->user()->getGroupIds());

        return (!empty($groups)) ? true : false;
    }

    /*
     * Check if the item share one or more read/write groups with the current user.
     *
     * @return bool
     */
    public function shareReadWriteGroups($guard = 'web'): bool
    {
        $groups = array_intersect($this->getReadWriteGroupIds(), auth($guard)->user()->getReadWriteGroupIds());

        return (!empty($groups)) ? true : false;
    }
}

