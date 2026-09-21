<?php

namespace App\Services;

use App\Models\CricketMatch;
use App\Models\Team;
use App\Services\Contracts\PlayingXiServiceInterface;
use InvalidArgumentException;

class PlayingXiService implements PlayingXiServiceInterface
{
    public function selectXi(
        CricketMatch $match,
        Team $team,
        array $playerIds,
        int $captainPlayerId,
        int $wicketKeeperPlayerId,
        ?int $viceCaptainPlayerId = null,
    ): void {
        $requiredSize = config('cricket.playing_xi_size');

        if (count($playerIds) !== $requiredSize) {
            throw new InvalidArgumentException("Exactly {$requiredSize} players must be selected.");
        }

        // Form submissions arrive as numeric strings; normalize before any
        // strict (===/in_array(..., true)) comparison against DB integers.
        $playerIds = array_map('intval', $playerIds);

        $squadPlayerIds = $team->players()->pluck('players.id')->all();

        foreach ([$captainPlayerId, $wicketKeeperPlayerId, ...($viceCaptainPlayerId ? [$viceCaptainPlayerId] : []), ...$playerIds] as $playerId) {
            if (! in_array($playerId, $squadPlayerIds, true)) {
                throw new InvalidArgumentException('All selected players must be part of the team\'s squad.');
            }
        }

        foreach ([$captainPlayerId, $wicketKeeperPlayerId, ...($viceCaptainPlayerId ? [$viceCaptainPlayerId] : [])] as $playerId) {
            if (! in_array($playerId, $playerIds, true)) {
                throw new InvalidArgumentException('Captain, vice-captain, and wicket keeper must be among the selected XI.');
            }
        }

        $match->playingXi()->where('team_id', $team->id)->delete();

        $rows = array_map(fn (int $playerId) => [
            'match_id' => $match->id,
            'team_id' => $team->id,
            'player_id' => $playerId,
            'is_captain' => $playerId === $captainPlayerId,
            'is_vice_captain' => $playerId === $viceCaptainPlayerId,
            'is_wicket_keeper' => $playerId === $wicketKeeperPlayerId,
            'is_substitute' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ], $playerIds);

        $match->playingXi()->insert($rows);
    }
}
