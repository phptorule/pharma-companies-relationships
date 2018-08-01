<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CountryDependantBaseModel extends Model
{

    protected $connection;


    function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

//        $this->connection = Auth::user()->role == 'admin' ? 'mysql_ru' : 'mysql';
    }
}
