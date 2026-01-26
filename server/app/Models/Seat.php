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

    protected $fillable = [
        'sector_id',
        'seat_number',
        'row',
        'col'
    ];
    public function sector(): BelongsTo
    {
        return $this->belongsTo(Sector::class);
    }
}
