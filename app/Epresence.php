<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Epresence extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id_users', 'type', 'is_approve', 'waktu',
    ];
}
