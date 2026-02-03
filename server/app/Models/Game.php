<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Game extends Model
{
    /** @use HasFactory<\Database\Factories\GameFactory> */
    use HasFactory;
    public $timestamps = false;

    protected $fillable = [
        'team_home_id',
        'team_away_id',
        'game_date'
    ];
    public function team_home(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_home_id');
    }

    public function team_away(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_away_id');
    }
}
