<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Playing XI Size
    |--------------------------------------------------------------------------
    |
    | Number of non-substitute players required in a team's playing XI,
    | and the minimum squad size a team needs before it can be selected
    | for a match (§11 validation rule).
    |
    */

    'playing_xi_size' => (int) env('CRICKET_PLAYING_XI_SIZE', 11),

];
