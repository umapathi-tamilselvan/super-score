<?php

namespace App\Policies;

use App\Models\Player;
use App\Models\User;

class PlayerPolicy
{
    /**
     * A user may only ever create their own player profile, and only
     * once — set up is done via saveOwnProfile's update path after that.
     */
    public function create(User $user): bool
    {
        return ! $user->isPlayer();
    }

    public function update(User $user, Player $player): bool
    {
        return $user->id === $player->user_id;
    }
}
