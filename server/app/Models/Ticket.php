<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

}
