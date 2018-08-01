<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Product extends CountryDependantBaseModel
{
    protected $table = 'rl_products';

    public $timestamps = false;

    function addresses()
    {
        return $this->belongsToMany(Address::class, 'rl_address_products', 'product_id','address_id');
    }

	function purchases()
	{
		return $this->belongsToMany(TenderPurchase::class, 'rl_address_tenders_purchase_products', 'product_id','purchase_id');
	}


	function getImageAttribute($value) {
        return !empty($value) ? '/storage' . $value : '';
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
