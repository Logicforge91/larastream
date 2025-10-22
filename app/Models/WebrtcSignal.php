<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebrtcSignal extends Model
{
   protected $fillable = ['stream_id', 'sender_type', 'receiver_type', 'data'];
    protected $casts = ['data' => 'array'];

    public function stream() {
        return $this->belongsTo(\App\Models\Stream::class);
    }
}
