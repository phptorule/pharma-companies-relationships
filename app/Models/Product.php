<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{

    protected $table = 'rl_products';


    function addresses()
    {
        return $this->belongsToMany(Address::class, 'rl_address_products', 'product_id','address_id');
    }

	function purchases()
	{
		return $this->belongsToMany(TenderPurchase::class, 'rl_address_tenders_purchase_products', 'product_id','purchase_id');
	}

}
