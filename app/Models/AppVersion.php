<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppVersion extends Model
{

    protected $fillable = ['main', 'major', 'minor'];


    static function last()
    {
        return (new self)->orderBy('id','DESC')->first();
    }


}
