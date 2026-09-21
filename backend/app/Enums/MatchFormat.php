<?php

namespace App\Enums;

enum MatchFormat: string
{
    case T10 = 't10';
    case T20 = 't20';
    case Odi = 'odi';
    case Custom = 'custom';
}
