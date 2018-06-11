<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cluster extends Model
{

    protected $table = 'rl_clusters';

    public $timestamps = false;

    function addresses()
    {
        return $this->hasMany(Address::class);
    }

}
