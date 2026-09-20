<?php

namespace App\Services;

use App\Enums\TeamRole;
use App\Models\Player;
use App\Models\Team;
use App\Models\User;
use App\Services\Contracts\TeamServiceInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class TeamService implements TeamServiceInterface
{
    public function create(User $owner, array $data): Team
    {
        if (isset($data['logo']) && $data['logo'] instanceof UploadedFile) {
            $data['logo_path'] = $data['logo']->store('team-logos', 'public');
        }
        unset($data['logo']);

        return $owner->teams()->create($data);
    }

    public function update(Team $team, array $data): Team
    {
        if (isset($data['logo']) && $data['logo'] instanceof UploadedFile) {
            if ($team->logo_path) {
                Storage::disk('public')->delete($team->logo_path);
            }

            $data['logo_path'] = $data['logo']->store('team-logos', 'public');
        }
        unset($data['logo']);

        $team->update($data);

        return $team->refresh();
    }

    public function assignRole(Team $team, Player $player, TeamRole $role): void
    {
        if (! $team->players()->where('players.id', $player->id)->exists()) {
            throw new InvalidArgumentException('The player is not part of this team\'s squad.');
        }

        $column = $role->pivotColumn();

        $team->players()->newPivotStatement()
            ->where('team_id', $team->id)
            ->update([$column => false]);

        $team->players()->updateExistingPivot($player->id, [$column => true]);
    }
}
