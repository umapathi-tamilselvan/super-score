<?php

namespace App\Enums;

enum DismissalType: string
{
    case Bowled = 'bowled';
    case Caught = 'caught';
    case Lbw = 'lbw';
    case RunOut = 'run_out';
    case Stumped = 'stumped';
    case HitWicket = 'hit_wicket';
    case RetiredHurt = 'retired_hurt';
    case RetiredOut = 'retired_out';
    case ObstructingField = 'obstructing_field';
}
