<?php

namespace App\Twitter;


class TwitterAccountStatus
{
    /**
     * The account status hasn't been looked up yet
     */
    const NOT_RETRIEVED = 0;
    /**
     * The account is active and in use
     */
    const ACTIVE = 10;
    /**
     * The account has been suspended
     */
    const SUSPENDED = 20;
    /**
     * The account is available
     */
    const FREE = 30;
    /**
     * The account is deactivated
     */
    const DEACTIVATED = 40;

    /**
     * @param int $status
     * @return string
     */
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