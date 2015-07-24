<?php

namespace App\Twitter;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class TwitterUser extends Model
{
    public $dates = ['date_registered', 'last_checked'];
    public $timestamps = false;

    public function getDateRegisteredAttribute($value) {
        if(is_null($value)) {
            return $value;
        }
        $date = new Carbon($value);
        return $date->format('d/m/y');
    }

    public function getLastCheckedAttribute($value) {
        if(is_null($value)) {
            return $value;
        }
        $date = new Carbon($value);
        return $date->format('d/m/y');
    }

    public function getProfilePicAttribute($value) {
        if(empty($value)) {
            return asset('images/icons/notallowed.svg');
        }

        return $value;
    }

    public function getStatusAttribute($value)
    {
        return TwitterAccountStatus::toText($value);
    }

    public function scopeNeverQueried($query)
    {
        return $query->whereNull('last_checked');
    }

    public function scopeFree($query) {
        return $query->whereStatus(TwitterAccountStatus::FREE);
    }
}