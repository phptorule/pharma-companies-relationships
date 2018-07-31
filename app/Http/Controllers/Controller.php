<?php

namespace App\Http\Controllers;

use App\Models\AppVersion;
use App\Models\UserEdit;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;


    function __construct()
    {
        UserEdit::setUUID();
    }


    function getAppVersion()
    {
        $appVersion = AppVersion::last();

        return response()->json($appVersion->main.'.'.$appVersion->major.'.'.$appVersion->minor);
    }
}
