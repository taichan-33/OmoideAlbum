<?php

namespace App\Enums;

enum ReactionType: string
{
    case LIKE = 'like';
    case FUN = 'fun';
    case WANT_TO_GO = 'want_to_go';
    case ON_HOLD = 'on_hold';
    case INTERESTED = 'interested';

    public function label(): string
    {
        return match ($this) {
            self::LIKE => 'いいね',
            self::FUN => '楽しそう',
            self::WANT_TO_GO => '行きたい',
            self::ON_HOLD => '保留',
            self::INTERESTED => '気になる',
        };
    }
}
