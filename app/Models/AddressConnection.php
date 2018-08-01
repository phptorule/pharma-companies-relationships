<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AddressConnection extends CountryDependantBaseModel
{
    protected $table = 'rl_address_connections';

    public $timestamps = false;
}
