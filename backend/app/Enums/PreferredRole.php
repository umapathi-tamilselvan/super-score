<?php

namespace App\Enums;

enum PreferredRole: string
{
    case Player = 'player';
    case Scorer = 'scorer';
    case TeamManager = 'team_manager';
    case Organizer = 'organizer';
    case All = 'all';
}
