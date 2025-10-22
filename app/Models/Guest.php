<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guest extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'uuid',
        'stream_id',
    ];

    // Optional: if you have a relation to Stream
    public function stream()
    {
        return $this->belongsTo(Stream::class);
    }
}
