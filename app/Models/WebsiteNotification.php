<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class WebsiteNotification extends CountryDependantBaseModel
{

    protected $table = 'rl_website_notifications';

    protected $dates = ['expired_at'];

    protected $fillable = ['key', 'title', 'body', 'type', 'expired_at'];


    function scopeActiveNotifications($q)
    {
        return $q->whereDate('expired_at', '>', Carbon::now());
    }


    function setExpiredAtAttribute($value)
    {
        $this->attributes['expired_at'] = $value ? Carbon::parse($value)->toDateTimeString() : null;
    }


}