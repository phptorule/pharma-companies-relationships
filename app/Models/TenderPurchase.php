<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenderPurchase extends Model
{
	protected $table = 'rl_address_tenders_purchase';

	public function tender()
	{
		return $this->belongsTo(Tender::class);
	}

	function products()
	{
		return $this->belongsToMany(Product::class, 'rl_address_tenders_purchase_products', 'purchase_id', 'product_id');
	}

	function consumables() {
        return $this->belongsToMany(ProductConsumable::class, 'rl_address_tenders_purchase_products', 'purchase_id', 'consumable_id');
    }

}
