<?php

namespace App\Policies;

use App\Models\CricketMatch;
use App\Models\User;

class CricketMatchPolicy
{
    public function create(User $user): bool
    {
        return true;
    }

    public function view(User $user, CricketMatch $match): bool
    {
        return $user->id === $match->user_id;
    }

    public function update(User $user, CricketMatch $match): bool
    {
        return $user->id === $match->user_id;
    }
}
