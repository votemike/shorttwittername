<?php

namespace App\Twitter;


class TwitterAccountStatus
{
    const NOT_RETRIEVED = 0;
    const ACTIVE = 10;
    const SUSPENDED = 20;
    const FREE = 30;
    const DEACTIVATED = 40;

    public static function toText($status) {
        switch($status) {
            case self::ACTIVE:
                return 'active';
            case self::SUSPENDED:
                return 'suspended';
            case self::FREE:
                return 'free';
            case self::DEACTIVATED:
                return 'deactivated';
            case self::NOT_RETRIEVED:
            default:
                return 'info not retrieved';
        }
    }
}