<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use Illuminate\Http\Request;

class GuestController extends Controller
{
    public function join($uuid) {
        $guest = Guest::where('uuid', $uuid)->firstOrFail();
        $stream = $guest->stream;
        return view('streams.guest', compact('guest', 'stream'));
    }
}
