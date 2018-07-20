<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Configuration extends Model
{


    protected $table = 'rl_configurations';

    protected $fillable = ['key', 'value'];

}
