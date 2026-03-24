<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sector extends Model
{
    /** @use HasFactory<\Database\Factories\SectorFactory> */
    use HasFactory;
    public $timestamps = false;

    protected $primaryKey = 'id';
    public $incrementing = false; // Kikapcsoljuk az auto-incrementet
    protected $keyType = 'string'; // Megadjuk, hogy string az ID

    protected $fillable = ['id', 'name', 'price'];
}
