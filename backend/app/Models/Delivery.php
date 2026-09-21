<?php

namespace App\Models;

use App\Enums\DismissalType;
use App\Enums\ExtraType;
use Database\Factories\DeliveryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'over_id', 'innings_id', 'striker_id', 'non_striker_id', 'bowler_id',
    'runs_off_bat', 'extra_type', 'extra_runs', 'is_wicket', 'dismissal_type',
    'dismissed_player_id', 'fielder_id', 'is_legal_delivery', 'is_free_hit',
    'sequence_in_innings',
])]
class Delivery extends Model
{
    /** @use HasFactory<DeliveryFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'extra_type' => ExtraType::class,
            'dismissal_type' => DismissalType::class,
            'is_wicket' => 'boolean',
            'is_legal_delivery' => 'boolean',
            'is_free_hit' => 'boolean',
        ];
    }

    public function innings(): BelongsTo
    {
        return $this->belongsTo(Innings::class);
    }

    public function over(): BelongsTo
    {
        return $this->belongsTo(Over::class);
    }

    public function striker(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'striker_id');
    }

    public function nonStriker(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'non_striker_id');
    }

    public function bowler(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'bowler_id');
    }

    public function dismissedPlayer(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'dismissed_player_id');
    }

    public function fielder(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'fielder_id');
    }

    public function totalRuns(): int
    {
        return $this->runs_off_bat + $this->extra_runs;
    }

    /**
     * Short scorecard-style label, e.g. "4", "W", "2wd", "1nb".
     */
    public function label(): string
    {
        if ($this->is_wicket) {
            return 'W';
        }

        if ($this->extra_type) {
            $short = match ($this->extra_type) {
                ExtraType::Wide => 'wd',
                ExtraType::NoBall => 'nb',
                ExtraType::Bye => 'b',
                ExtraType::LegBye => 'lb',
                ExtraType::Penalty => 'pen',
            };

            return $this->totalRuns().$short;
        }

        return (string) $this->runs_off_bat;
    }
}
