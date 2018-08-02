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

        switch (Auth::user()->default_country) {

            case 'switzerland':
                $this->connection = 'mysql_ch';
                break;

            case 'russia':
                $this->connection = 'mysql_ru';
                break;
        }
    }
}
