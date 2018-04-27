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
}
