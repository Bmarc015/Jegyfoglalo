<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Seat extends Model
{
    /** @use HasFactory<\Database\Factories\SeatFactory> */
    use HasFactory;

    public $timestamps = false;

    // App/Models/Seat.php
    protected $fillable = ['game_id', 'sector_id', 'row', 'col', 'status'];
    public function sector(): BelongsTo
    {
        return $this->belongsTo(Sector::class);
    }
    public function ticket()
    {
        // Egy székhez egy adott meccsen egy ticket tartozik
        return $this->hasOne(\App\Models\Ticket::class, 'seat_id');
    }
}
