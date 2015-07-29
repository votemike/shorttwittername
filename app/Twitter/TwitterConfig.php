<?php

namespace App\Twitter;

use Illuminate\Database\Eloquent\Model;

class TwitterConfig extends Model
{
    public $table = 'twitter_config';
    public $timestamps = false;

    public function scopeGetConfig($query, $name) {
        return $query->whereName($name)->get()->pluck('value');
    }
}
