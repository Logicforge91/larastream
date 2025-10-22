<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stream extends Model
{
    protected $fillable = ['title', 'host_id', 'status', 'scheduled_at'];

    public function videos() {
        return $this->hasMany(Video::class);
    }

    public function guests() {
        return $this->hasMany(Guest::class);
    }

    public function host() {
        return $this->belongsTo(User::class, 'host_id');
    }
}

