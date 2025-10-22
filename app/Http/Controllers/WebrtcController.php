<?php

namespace App\Http\Controllers;

use App\Models\WebrtcSignal;
use Illuminate\Http\Request;

class WebrtcController extends Controller
{
    // Store SDP or ICE candidate
    public function send(Request $request) {
        $request->validate([
            'stream_id' => 'required|exists:streams,id',
            'sender_type' => 'required|in:host,guest',
            'receiver_type' => 'required|in:host,guest',
            'data' => 'required|array'
        ]);

        WebrtcSignal::create([
            'stream_id' => $request->stream_id,
            'sender_type' => $request->sender_type,
            'receiver_type' => $request->receiver_type,
            'data' => $request->data
        ]);

        return response()->json(['status' => 'ok']);
    }

    // Receive signals for a particular type
    public function receive($stream_id, $receiver_type) {
        $signals = WebrtcSignal::where('stream_id', $stream_id)
            ->where('receiver_type', $receiver_type)
            ->get();

        // Optionally, delete after reading to avoid duplicates
        WebrtcSignal::where('stream_id', $stream_id)
            ->where('receiver_type', $receiver_type)
            ->delete();

        return response()->json($signals);
    }
}
