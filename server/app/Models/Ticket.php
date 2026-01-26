<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ticket extends Model
{
    /** @use HasFactory<\Database\Factories\TicketFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'game_id',
        'seat_id',
        'status'
    ];
    /**
     * A jegyhez tartozó mérkőzés
     */
    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    /**
     * A jegyhez tartozó szék
     */
    public function seat(): BelongsTo
    {
        return $this->belongsTo(Seat::class);
    }

    /**
     * A jegy tulajdonosa (felhasználó)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}