<?php

namespace App\Enums;

enum PlayerRole: string
{
    case Batter = 'batter';
    case Bowler = 'bowler';
    case AllRounder = 'all_rounder';
    case WicketKeeper = 'wicket_keeper';
}
