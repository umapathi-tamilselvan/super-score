<?php

namespace App\Services;

use App\Models\Player;
use App\Models\Team;
use App\Models\User;
use App\Services\Contracts\PlayerServiceInterface;

class PlayerService implements PlayerServiceInterface
{
    public function saveOwnProfile(User $user, array $data): Player
    {
        // A fresh DB-backed upsert rather than branching on $user->player,
        // which may be a stale cached relation (e.g. resolved earlier by
        // the request's own authorization check).
        return Player::updateOrCreate(['user_id' => $user->id], $data);
    }

    public function attachToTeam(Player $player, Team $team): void
    {
        $team->players()->syncWithoutDetaching([$player->id]);
    }

    public function detachFromTeam(Player $player, Team $team): void
    {
        $team->players()->detach($player->id);
    }
}
