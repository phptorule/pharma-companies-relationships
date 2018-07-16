<?php
/**
 * Created by PhpStorm.
 * User: developer
 * Date: 13.07.18
 * Time: 17:24
 */

namespace App\Services;


class GlobalHelper
{

    static function findIndexInObjectsArray($items, $needle, $propName) {
        foreach ($items as $i => $item) {
            if($item->$propName == $needle) {
                return $i;
            }
        }

        return null;
    }
}

