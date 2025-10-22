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

   public function uploadVideo(Request $request, Stream $stream) {
    $request->validate(['video' => 'required|mimes:mp4,webm,mov|max:51200']);
    $file = $request->file('video');
    $filename = time().'_'.$file->getClientOriginalName();
    $file->storeAs('public/uploads', $filename);

    $stream->videos()->create([
        'title' => $file->getClientOriginalName(),
        'filename' => $filename
    ]);

    return redirect()->back()->with('success','Video uploaded successfully!');
}

}

