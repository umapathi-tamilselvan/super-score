<?php

namespace App\Services\Contracts;

use App\Enums\TeamRole;
use App\Models\Player;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\UploadedFile;

interface TeamServiceInterface
{
    /**
     * @param  array{name: string, short_name: string, city?: string, logo?: UploadedFile}  $data
     */
    public function create(User $owner, array $data): Team;

    /**
     * @param  array{name?: string, short_name?: string, city?: string, logo?: UploadedFile}  $data
     */
    public function update(Team $team, array $data): Team;

    /**
     * Assign a leadership role (captain/vice-captain/wicket-keeper) to a
     * player already in the team's squad, clearing whoever previously
     * held that role for this team.
     *
     * @throws \InvalidArgumentException when the player is not part of the team's squad.
     */
    public function assignRole(Team $team, Player $player, TeamRole $role): void;
}
