<?php

namespace App\Twitter;

use Carbon\Carbon;
use DB;
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
            if($this->getAttributeFromArray('status') == TwitterAccountStatus::FREE) {
                return asset('images/icons/available.png');
            }
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

    public function scopeNotRetrieved($query) {
        return $query->whereStatus(TwitterAccountStatus::NOT_RETRIEVED);
    }

    public function scopeSelectUsernameLength($query) {
        return $query->select(DB::raw('CHAR_LENGTH(username) as length'));
    }

    public function scopeWhereUsernameLength($query, $length) {
        return $query->whereRaw('LENGTH(username) = ?', [$length]);
    }
}