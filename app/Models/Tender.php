<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tender extends Model
{
	protected $table = 'rl_address_tenders';

	public function purchase()
	{
		return $this->hasMany(TenderPurchase::class);
	}
}
