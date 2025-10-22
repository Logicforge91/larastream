<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use App\Models\Stream;
use Illuminate\Http\Request;

class GuestController extends Controller
{
    public function join($uuid) {
        $guest = Guest::where('uuid', $uuid)->firstOrFail();
        $stream = $guest->stream;
        return view('streams.guest', compact('guest', 'stream'));
    }

    public function create(Request $request)
    {
        $stream = Stream::findOrFail($request->stream_id);

        $guest = Guest::create([
            'name' => $request->name ?? 'Guest',
            'uuid' => \Str::uuid(),
            'stream_id' => $stream->id,
        ]);

        // Redirect to guest page
        return redirect()->route('guest.show', ['uuid' => $guest->uuid]);
    }
        public function show($uuid)
    {
        $guest = Guest::where('uuid', $uuid)->firstOrFail();
        $stream = $guest->stream;

        return view('streams.guest', compact('guest', 'stream'));
    }
}
