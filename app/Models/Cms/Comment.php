<?php

namespace App\Models\Cms;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'comments';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'text',
        'owned_by',
    ];


    /**
     * Get all of the owning commentable models.
     */
    public function commentable()
    {
        return $this->morphTo();
    }

    /**
     * Check if the current user is allowed to edit a given comment.
     */
    public function canEdit(): bool
    {
        return (auth()->check() && auth()->user()->id == $this->owned_by);
    }
}
