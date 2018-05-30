<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Address extends Model
{

    protected $table = 'rl_addresses';

    protected $fillable = ['customer_status', 'created_at', 'updated_at'];


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

}
