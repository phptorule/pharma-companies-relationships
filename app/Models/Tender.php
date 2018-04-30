<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tender extends Model
{
	protected $table = 'rl_address_tenders';

	public function purchase()
	{
		return $this->hasMany(TenderPurchase::class)->orderBy('total_price', 'desc')->take(3);
	}

	public function budget()
	{
		return $this->hasOne(TenderBudget::class);
	}
}
