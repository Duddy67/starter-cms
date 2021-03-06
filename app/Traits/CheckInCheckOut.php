<?php

namespace App\Traits;

use Carbon\Carbon;
use App\Models\User;


trait CheckInCheckOut
{
    /**
     * Checks a given record out for the current user.
     *
     * @return void
     */
    public function checkOut(): void
    {
        $this->checked_out = auth()->user()->id;
        $this->checked_out_time = Carbon::now();
        // Prevent updated_at field to be updated.
        $this->timestamps = false;
        $this->save();
    }

    /**
     * Checks a given record back in for the current user.
     *
     * @return void
     */
    public function checkIn(): void
    {
        $this->checked_out = null;
        $this->checked_out_time = null;
        // Prevent updated_at field to be updated.
        $this->timestamps = false;
        $this->save();
    }

    /**
     * Checks if a given record can be checked back in. 
     *
     * @return bool
     */
    public function canCheckIn(): bool
    {
        // Get the user for whom the record is checked out .
        $user = User::findOrFail($this->checked_out);

        // Ensure the current user has a higher role level or that they are the user for whom the record is checked out. 
        if (auth()->user()->getRoleLevel() > $user->getRoleLevel() || $this->checked_out == auth()->user()->id) {
            return true;
        }

        return false;
    }

    /**
     * Checks multiple records back in for the current user.
     *
     * @param array  $recordIds
     * @param mixed $model
     * @return array
     */
    public static function checkInMultiple(array $recordIds, mixed $model): array
    {
        $checkedIn = 0;
        $messages = [];

        // Check in the groups selected from the list.
        foreach ($recordIds as $id) {
            $record = $model::findOrFail($id);

            if ($record->checked_out === null) {
                continue;
            }

            if (!$record->canCheckIn()) {
                $messages['error'] = __('messages.generic.check_in_not_auth');
                continue;
            }

            $record->checkIn();
            $checkedIn++;
        }

        if ($checkedIn) {
            $messages['success'] = __('messages.generic.check_in_success', ['number' => $checkedIn]);
        }

        return $messages;

    }

    public function checkInAll()
    {
    }
}
