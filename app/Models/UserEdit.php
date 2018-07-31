<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class UserEdit extends Model
{


    protected $table = 'rl_user_edits';

    static function UUID ()
    {
        return md5(Carbon::now());
    }

    static function log(Model $model)
    {

        $oldState = $model->getOriginal();

        $newState = json_decode($model->toJSON());

        Log::info('oldState ---> ' . print_r($oldState, 1));

        Log::info('newState ' . print_r($newState, 1));

        foreach ($newState as $prop => $value) {

            if($newState->$prop != $oldState[$prop]) {
                Log::info("OLD value of $prop: " . $oldState[$prop]);
                Log::info("NEW value of $prop: " . $newState->$prop);
            }

        }
    }

}
