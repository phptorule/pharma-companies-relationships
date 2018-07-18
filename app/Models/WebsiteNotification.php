<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class WebsiteNotification extends Model
{

    protected $table = 'rl_website_notifications';

    protected $dates = ['expired_at'];

    protected $fillable = ['key', 'title', 'body', 'expired_at'];


    function scopeActiveNotifications($q) {
        return $q->whereDate('expired_at', '>', Carbon::now());
    }

}