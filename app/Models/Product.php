<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'rl_products';

    public $timestamps = false;

    function addresses()
    {
        return $this->belongsToMany(Address::class, 'rl_address_products', 'product_id','address_id');
    }

}
