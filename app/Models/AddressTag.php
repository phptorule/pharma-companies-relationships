<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AddressTag extends CountryDependantBaseModel
{
    protected $table = "rl_address_tags";

    public $timestamps = false;
}
