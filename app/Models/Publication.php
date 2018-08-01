<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Publication extends CountryDependantBaseModel
{

    protected $table = 'rl_publications';


    function people()
    {
        return $this->belongsToMany(People::class, 'rl_people_publications', 'publication_id', 'person_id');
    }


    static function fetchArticlesByIds($ids) {
        return self::whereIn('id', $ids)->orderBy('year', 'DESC')->get();
    }
}
