<?php

namespace App\Enums;

enum TeamRole: string
{
    case Captain = 'captain';
    case ViceCaptain = 'vice_captain';
    case WicketKeeper = 'wicket_keeper';

    /**
     * The team_player pivot column this role toggles.
     */
    public function pivotColumn(): string
    {
        return match ($this) {
            self::Captain => 'is_captain',
            self::ViceCaptain => 'is_vice_captain',
            self::WicketKeeper => 'is_wicket_keeper',
        };
    }
}
