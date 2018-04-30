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

	public function budget()
	{
		return $this->hasOne(TenderBudget::class);
	}

	public function scopeThreeProductsWithMostBudgetSpent ($q)
	{
		return $q->with(['purchase' => function ($query){
				$query->orderBy('total_price', 'desc')->take(3);
				$query->with('products');
			}])
			->with('budget');
	}
}
