<?php

namespace App\Services\Contracts;

use App\Models\Player;
use App\Models\Team;
use App\Models\User;

interface PlayerServiceInterface
{
    /**
     * Create or update the user's own player profile. A player profile
     * belongs to exactly one user and can only ever be created or
     * changed by that user — there is no "add a player for someone
     * else" operation. The player's name and photo are the user's own
     * account fields (see ProfileService), not duplicated here.
     *
     * @param  array{date_of_birth?: string, role: string, batting_style?: string, bowling_style?: string}  $data
     */
    public function saveOwnProfile(User $user, array $data): Player;

    /**
     * Add an existing player (any registered user's profile) to a
     * team's squad. Players are independent of any one team and can be
     * attached to several.
     */
    public function attachToTeam(Player $player, Team $team): void;

    /**
     * Remove a player from a team's squad.
     */
    public function detachFromTeam(Player $player, Team $team): void;
}
