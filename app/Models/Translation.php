<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Translation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'locale',
        'title',
        'name',
        'slug',
        'content',
        'raw_content',
        'excerpt',
        'description',
        'alt_img',
        'url',
        'value',
        'meta_data',
        'extra_fields',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be casted.
     *
     * @var array
     */
    protected $casts = [
        'meta_data' => 'array',
        'extra_fields' => 'array',
    ];


    /**
     * Get all of the owning translatable models.
     */
    public function translatable()
    {
        return $this->morphTo();
    }
}
