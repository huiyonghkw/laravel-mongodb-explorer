<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class AdClick extends Model
{
    protected $connection = 'mongodb';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];
}
