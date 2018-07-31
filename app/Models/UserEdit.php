<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserEdit extends Model
{

    protected $table = 'rl_user_edits';

    protected $fillable = ['user_id', 'entity_id', 'entity_row_key', 'field', 'new_value', 'old_value', 'url_formatter', 'batch_uuid'];


    static function UUID ()
    {
        return md5(Carbon::now());
    }


    static function log(Model $model)
    {
        $oldState = $model->getOriginal();

        $newState = json_decode($model->toJSON());

        $uuid = self::UUID();

        foreach ($newState as $prop => $value) {

            if($newState->$prop != $oldState[$prop]) {
                Log::info("OLD value of $prop: " . $oldState[$prop]);
                Log::info("NEW value of $prop: " . $newState->$prop);

                $params = [
                    'user_id' => Auth::user()->id,
                    'entity_id' => Entity::where('table_name', $model->getTable())->first()->id,
                    'entity_row_key' => $model->id,
                    'field' => $prop,
                    'new_value' => $newState->$prop,
                    'old_value' => $oldState[$prop],
                    'url_formatter' => null,
                    'batch_uuid' => $uuid
                ];

                self::create($params);
            }

        }
    }

}
