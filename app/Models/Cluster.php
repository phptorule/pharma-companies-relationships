<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cluster extends CountryDependantBaseModel
{

    protected $table = 'rl_clusters';

    public $timestamps = false;

    function addresses()
    {
        return $this->hasMany(Address::class);
    }


    public static function boot()
    {
        parent::boot();

        self::updating(function($model){
            UserEdit::log($model);
        });

        self::created(function($model){
            UserEdit::log($model, 'created');
        });
    }

}
