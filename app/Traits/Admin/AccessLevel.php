<?php

namespace App\Traits\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Auth;


trait AccessLevel
{
    /*
     * Checks whether the current user is allowed to to change the access level of a given item.
     *
     * @param Object  $item
     * @return bool
     */
    public function canChangeAccessLevel(): bool
    {
        return ($this->owned_by == auth()->user()->id || auth()->user()->getRoleLevel() > $this->getOwnerRoleLevel()) ? true: false;
    }

    /*
     * Checks whether the current user is allowed to access a given item.
     *
     * @param Object  $item
     * @return bool
     */
    public function canAccess(): bool
    {
        return (in_array($this->access_level, ['public_ro', 'public_rw']) || $this->shareGroups() || $this->canEdit()) ? true : false;
    }

    /*
     * Checks whether the current user is allowed to edit a given item.
     *
     * @param Object  $item
     * @return bool
     */
    public function canEdit(): bool
    {
        if (!Auth::check()) {
            // The user must be authenticated to edit.
            return false;
        }

        if ($this->access_level == 'public_rw' || $this->getOwnerRoleLevel() < auth()->user()->getRoleLevel() || $this->owned_by == auth()->user()->id || $this->shareReadWriteGroups()) {
            return true;
        }

        return false;
    }

    /*
     * Checks whether the current user is allowed to delete a given item according to their role level.
     *
     * @return bool
     */
    public function canDelete(): bool
    {
        // The owner role level is lower than the current user's or the current user owns the item.
        if ($this->getOwnerRoleLevel() < auth()->user()->getRoleLevel() || $this->owned_by == auth()->user()->id) {
            return true;
        }

        return false;
    }

    /*
     * Returns the role level of the item's owner.
     *
     * @return integer
     */
    public function getOwnerRoleLevel(): int
    {
        $owner = ($this->owned_by == auth()->user()->id) ? auth()->user() : User::findOrFail($this->owned_by);

        return $owner->getRoleLevel();
    }

    /*
     * Checks whether the current user is allowed to change the status level of a given item.
     *
     * @return bool
     */
    public function canChangeStatus(): bool
    {
        // Use the access level constraints.
        return $this->canChangeAccessLevel();
    }

    /*
     * Checks whether the current user is allowed to change the owner,
     * categories or parent category of a given item.
     *
     * @return bool
     */
    public function canChangeAttachments(): bool
    {
        // Use the access level constraints.
        return $this->canChangeAccessLevel();
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
    public function shareGroups(): bool
    {
        if (!Auth::check()) {
            // The user must be authenticated to share groups.
            return false;
        }

        $groups = array_intersect($this->getGroupIds(), auth()->user()->getGroupIds());

        return (!empty($groups)) ? true : false;
    }

    /*
     * Check if the item share one or more read/write groups with the current user.
     *
     * @return bool
     */
    public function shareReadWriteGroups(): bool
    {
        $groups = array_intersect($this->getReadWriteGroupIds(), auth()->user()->getReadWriteGroupIds());

        return (!empty($groups)) ? true : false;
    }
}

