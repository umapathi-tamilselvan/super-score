<?php

namespace App\Services\Scoring;

use App\DataTransferObjects\BatterStat;
use App\DataTransferObjects\BowlerStat;
use App\DataTransferObjects\DeliveryInput;
use App\DataTransferObjects\FallOfWicket;
use App\DataTransferObjects\InningsProjection;
use App\Enums\DismissalType;
use App\Enums\ExtraType;
use App\Models\Delivery;
use App\Models\Innings;
use App\Models\Player;
use App\Services\Scoring\Contracts\ScoreProjectorInterface;
use App\Services\Scoring\Dismissals\DismissalHandlerFactory;

class ScoreProjectionService implements ScoreProjectorInterface
{
    private const NOT_CHARGED_TO_BOWLER = [ExtraType::Bye, ExtraType::LegBye, ExtraType::Penalty];

    public function __construct(
        private readonly DismissalHandlerFactory $dismissalHandlerFactory,
    ) {}

    public function project(Innings $innings): InningsProjection
    {
        $deliveries = $innings->deliveries()
            ->with(['striker', 'bowler', 'dismissedPlayer', 'fielder'])
            ->orderBy('sequence_in_innings')
            ->get();

        $teamRuns = 0;
        $wickets = 0;
        $legalBalls = 0;
        $extras = ['wide' => 0, 'no_ball' => 0, 'bye' => 0, 'leg_bye' => 0, 'penalty' => 0, 'total' => 0];

        /** @var array<int, array{player: Player, runs: int, balls: int, fours: int, sixes: int, isOut: bool, dismissalDescription: ?string}> $batters */
        $batters = [];
        /** @var array<int, array{player: Player, legalBalls: int, runsConceded: int, wickets: int}> $bowlers */
        $bowlers = [];
        /** @var FallOfWicket[] $fallOfWickets */
        $fallOfWickets = [];

        foreach ($deliveries as $delivery) {
            $teamRuns += $delivery->totalRuns();

            if ($delivery->is_legal_delivery) {
                $legalBalls++;
            }

            if ($delivery->extra_type) {
                $extras[$delivery->extra_type->value] += $delivery->extra_runs;
                $extras['total'] += $delivery->extra_runs;
            }

            $batters[$delivery->striker_id] ??= $this->newBatterRow($delivery->striker);

            if ($delivery->extra_type !== ExtraType::Wide) {
                $batters[$delivery->striker_id]['balls']++;
            }
            $batters[$delivery->striker_id]['runs'] += $delivery->runs_off_bat;
            if ($delivery->runs_off_bat === 4) {
                $batters[$delivery->striker_id]['fours']++;
            } elseif ($delivery->runs_off_bat === 6) {
                $batters[$delivery->striker_id]['sixes']++;
            }

            $bowlers[$delivery->bowler_id] ??= $this->newBowlerRow($delivery->bowler);
            if ($delivery->is_legal_delivery) {
                $bowlers[$delivery->bowler_id]['legalBalls']++;
            }
            $bowlers[$delivery->bowler_id]['runsConceded'] += in_array($delivery->extra_type, self::NOT_CHARGED_TO_BOWLER, true)
                ? 0
                : $delivery->totalRuns();

            if ($delivery->is_wicket && $delivery->dismissed_player_id) {
                $wickets++;

                $batters[$delivery->dismissed_player_id] ??= $this->newBatterRow($delivery->dismissedPlayer);
                $batters[$delivery->dismissed_player_id]['isOut'] = true;
                $batters[$delivery->dismissed_player_id]['dismissalDescription'] = $this->describeDismissal($delivery);

                $outcome = $this->dismissalHandlerFactory->forType($delivery->dismissal_type)->apply(new DeliveryInput);
                if ($outcome->creditedToBowler) {
                    $bowlers[$delivery->bowler_id]['wickets']++;
                }

                $fallOfWickets[] = new FallOfWicket(
                    wicketNumber: $wickets,
                    teamScoreAtFall: $teamRuns,
                    playerOut: $delivery->dismissedPlayer,
                    oversDisplay: intdiv($legalBalls, 6).'.'.($legalBalls % 6),
                );
            }
        }

        return new InningsProjection(
            teamRuns: $teamRuns,
            wickets: $wickets,
            legalBallsBowled: $legalBalls,
            batterStats: array_map(
                fn (array $row) => new BatterStat($row['player'], $row['runs'], $row['balls'], $row['fours'], $row['sixes'], $row['isOut'], $row['dismissalDescription']),
                $batters
            ),
            bowlerStats: array_map(
                fn (array $row) => new BowlerStat($row['player'], $row['legalBalls'], $row['runsConceded'], $row['wickets']),
                $bowlers
            ),
            extras: $extras,
            fallOfWickets: $fallOfWickets,
        );
    }

    /**
     * @return array{player: Player, runs: int, balls: int, fours: int, sixes: int, isOut: bool, dismissalDescription: ?string}
     */
    private function newBatterRow(Player $player): array
    {
        return ['player' => $player, 'runs' => 0, 'balls' => 0, 'fours' => 0, 'sixes' => 0, 'isOut' => false, 'dismissalDescription' => null];
    }

    /**
     * @return array{player: Player, legalBalls: int, runsConceded: int, wickets: int}
     */
    private function newBowlerRow(Player $player): array
    {
        return ['player' => $player, 'legalBalls' => 0, 'runsConceded' => 0, 'wickets' => 0];
    }

    private function describeDismissal(Delivery $delivery): string
    {
        $bowlerName = $delivery->bowler->user->name;
        $fielderName = $delivery->fielder?->user->name;

        return match ($delivery->dismissal_type) {
            DismissalType::Bowled => "b {$bowlerName}",
            DismissalType::Caught => $fielderName ? "c {$fielderName} b {$bowlerName}" : "c b {$bowlerName}",
            DismissalType::Lbw => "lbw b {$bowlerName}",
            DismissalType::Stumped => $fielderName ? "st {$fielderName} b {$bowlerName}" : "st b {$bowlerName}",
            DismissalType::HitWicket => "hit wicket b {$bowlerName}",
            DismissalType::RunOut => $fielderName ? "run out ({$fielderName})" : 'run out',
            DismissalType::RetiredHurt => 'retired hurt',
            DismissalType::RetiredOut => 'retired out',
            DismissalType::ObstructingField => 'obstructing the field',
            null => '',
        };
    }
}
