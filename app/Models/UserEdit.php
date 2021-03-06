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

    protected static $UUID;


    static function setUUID() {
        self::$UUID = md5(Carbon::now());
    }


    static function log(Model $model, $event = null)
    {
        $oldState = $model->getOriginal();

        $newState = json_decode($model->toJSON());

        if($event === 'created') {
            return self::logCreatedEvent($model);
        }

        foreach ($newState as $prop => $value) {

            if($newState->$prop != $oldState[$prop]) {
                $params = [
                    'user_id' => Auth::user()->id,
                    'entity_id' => Entity::where('table_name', $model->getTable())->first()->id,
                    'entity_row_key' => $model->id,
                    'field' => $prop,
                    'new_value' => $newState->$prop,
                    'old_value' => $oldState[$prop],
                    'url_formatter' => null,
                    'batch_uuid' => self::$UUID
                ];

                self::create($params);
            }

        }
    }


    static function logManyToMany(Model $model, $relatedPropTable, $newIds, $oldIds)
    {
        sort($oldIds);
        sort($newIds);

        $oldValues = implode(',', $oldIds);
        $newValues = implode(',', $newIds);

        if ($oldValues == $newValues) {
            return;
        }

        $params = [
            'user_id' => Auth::user()->id,
            'entity_id' => Entity::where('table_name', $model->getTable())->first()->id,
            'entity_row_key' => $model->id,
            'field' => $relatedPropTable,
            'new_value' => $newValues ? $newValues : null,
            'old_value' => $oldValues ? $oldValues : null,
            'url_formatter' => null,
            'batch_uuid' => self::$UUID
        ];

        self::create($params);
    }

    static function logCreatedEvent (Model $model)
    {
        foreach ($model->toArray() as $prop => $value) {
            $params = [
                'user_id' => Auth::user()->id,
                'entity_id' => Entity::where('table_name', $model->getTable())->first()->id,
                'entity_row_key' => $model->id,
                'field' => $prop,
                'new_value' => $model->$prop,
                'old_value' => null,
                'url_formatter' => null,
                'batch_uuid' => self::$UUID
            ];

            self::create($params);
        }
    }

}
