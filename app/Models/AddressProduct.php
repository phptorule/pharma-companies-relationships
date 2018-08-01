<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AddressProduct extends CountryDependantBaseModel
{
    protected $table = "rl_address_products";

    public $timestamps = false;
}
