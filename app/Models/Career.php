<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Career extends CountryDependantBaseModel
{

    protected $table = 'rl_people_career';



    function people()
    {
        return $this->belongsTo(People::class, 'person_id');
    }

}
