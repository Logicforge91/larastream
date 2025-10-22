<?php

namespace App\Http\Controllers;

use App\Models\WebrtcSignal;
use Illuminate\Http\Request;

class WebrtcController extends Controller
{
    // Store SDP or ICE candidate
     public function send(Request $request)
    {
        WebrtcSignal::create([
            'stream_id' => $request->stream_id,
            'sender_type' => $request->sender_type,
            'receiver_type' => $request->receiver_type,
            'data' => $request->data,
        ]);

        return response()->json(['status'=>'ok']);
    }
    // Receive signals for a particular type
       public function receive($stream, $type)
    {
        $signals = WebrtcSignal::where('stream_id', $stream)
            ->where('receiver_type', $type)
            ->get();

        return response()->json($signals);
    }
}
