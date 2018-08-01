<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PeopleType extends CountryDependantBaseModel
{
    protected $table = 'rl_people_types';

    public $timestamps = false;
}
