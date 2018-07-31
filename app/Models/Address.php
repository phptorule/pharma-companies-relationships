<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Address extends Model
{

    protected $table = 'rl_addresses';

    protected $fillable = ['customer_status', 'created_at', 'updated_at', 'name', 'address', 'url', 'phone'];


    function tags()
    {
        return $this->belongsToMany(Tag::class, 'rl_address_tags', 'address_id','tag_id');
    }


    function cluster()
    {
        return $this->belongsTo(Cluster::class);
    }


    function products()
    {
        return $this->belongsToMany(Product::class, 'rl_address_products', 'address_id','product_id');
    }


    function customerType()
    {
        return $this->belongsTo(CustomerType::class, 'customer_status');
    }

    function people()
    {
        return $this->belongsToMany(People::class, 'rl_address_people', 'address_id','person_id');
    }

	public function tenders()
	{
		return $this->hasMany(Tender::class, 'address_id');
	}

    static function createRelatedLabsIds($related_labs)
    {
        $related_labs_ids = "";
        $first = true;

        foreach ($related_labs as $lab){
            if ($first){
                $first = false;
            }else{
                $related_labs_ids = $related_labs_ids . ",";
            }
            $related_labs_ids = $related_labs_ids . $lab->id;
        }

        return $related_labs_ids;
    }

    public static function boot()
    {
        parent::boot();

        self::creating(function($model){
            // ... code here
        });

        self::created(function($model){
            // ... code here
        });

        self::updating(function($model){
            UserEdit::log($model);
        });

        self::updated(function($model){

        });

        self::deleting(function($model){
            // ... code here
        });

        self::deleted(function($model){
            // ... code here
        });
    }

}
