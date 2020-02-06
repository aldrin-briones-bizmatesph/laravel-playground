<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'body',
    ];

    /**
     * The attributes that should be mutated to dates.
     * 
     * @var array
     */
    protected $dates = [
        'published_at'
    ];

    /**
     * Get the user of the post.
     * 
     * @return \App\User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
