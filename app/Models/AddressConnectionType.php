<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AddressConnectionType extends CountryDependantBaseModel
{
    protected $table = 'rl_address_connection_types';

    public $timestamps = false;
}
