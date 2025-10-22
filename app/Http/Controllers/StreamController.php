<?php

namespace App\Http\Controllers;

use App\Models\Stream;
use App\Models\Video;
use Illuminate\Http\Request;

class StreamController extends Controller
{
    public function index() {
        $streams = Stream::all();
        return view('streams.index', compact('streams'));
    }

    public function show($id) {
        $stream = Stream::with('videos', 'guests')->findOrFail($id);
        return view('streams.show', compact('stream'));
    }

    public function store(Request $request) {
        $stream = Stream::create([
            'title' => $request->title,
            'host_id' => 1, // Assume host
            'scheduled_at' => $request->scheduled_at,
            'status' => 'upcoming'
        ]);
        return redirect('/stream/' . $stream->id);
    }

    public function uploadVideo(Request $request) {
        $request->validate([
            'stream_id' => 'required|exists:streams,id',
            'video' => 'required|mimes:mp4,mov,webm|max:20000'
        ]);

        $file = $request->file('video');
        $filename = time().'_'.$file->getClientOriginalName();
        $file->move(public_path('uploads'), $filename);

        Video::create([
            'stream_id' => $request->stream_id,
            'filename' => $filename,
            'title' => $request->title ?? 'Untitled'
        ]);

        return back();
    }
}

