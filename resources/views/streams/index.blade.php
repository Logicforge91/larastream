{{-- @extends('layouts.app')
@section('content') --}}
<div class="container">

    <h1>Live Streams</h1>

    @foreach($streams as $stream)
        <div class="stream-item">
            <div>
                <a href="/stream/{{ $stream->id }}">{{ $stream->title }}</a>
                <span class="stream-date">
                    @if($stream->scheduled_at)
                        Scheduled at: {{ \Carbon\Carbon::parse($stream->scheduled_at)->format('d M Y, H:i') }}
                    @endif
                    (Status: {{ $stream->status }})
                </span>
            </div>
            <div class="stream-actions">
                {{-- Host link --}}
                <a href="/stream/{{ $stream->id }}" class="btn-link">Host View</a> |

                {{-- Guest link (first guest or create new) --}}
                @if($stream->guests->count() > 0)
                    <a href="/guest/join/{{ $stream->guests->first()->uuid }}" class="btn-link">Join as Guest</a>
                @else
                    <form method="POST" action="{{route('guest.create')}}" style="display:inline">
                        @csrf
                        <input type="hidden" name="stream_id" value="{{ $stream->id }}">
                        <input type="hidden" name="name" value="Guest {{ rand(1000,9999) }}">
                        <button type="submit" class="btn-link">Create Guest</button>
                    </form>
                @endif
            </div>

            {{-- Video Upload Form --}}
            <div style="margin-top:10px;">
                <form action="{{ route('stream.uploadVideo', $stream->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="file" name="video" accept="video/*" required>
                    <button type="submit" style="padding:6px 12px; border:none; border-radius:5px; background-color:#28a745; color:white; cursor:pointer;">
                        Upload Video
                    </button>
                </form>

                @if($stream->videos->count() > 0)
                    <small>Uploaded Videos:</small>
                    <ul>
                        @foreach($stream->videos as $video)
                            <li>{{ $video->title }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    @endforeach

    <h2>Create New Stream</h2>
    <form method="POST" action="/stream">
        @csrf
        <input type="text" name="title" placeholder="Stream Title" required>
        <input type="datetime-local" name="scheduled_at">
        <button type="submit">Create Stream</button>
    </form>

</div>

<style>
/* General page styling */
body {
    font-family: Arial, sans-serif;
    background-color: #f5f5f5;
    margin: 0;
    padding: 20px;
    color: #333;
}
.container { max-width: 800px; margin: auto; }
h1, h2 { color: #222; margin-bottom: 15px; }
.stream-item {
    background-color: #fff;
    padding: 12px 16px;
    margin-bottom: 15px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: transform 0.2s, box-shadow 0.2s;
}
.stream-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}
.stream-item a {
    text-decoration: none;
    color: #007BFF;
    font-weight: bold;
    font-size: 16px;
}
.stream-item a:hover {
    text-decoration: underline;
}
.stream-date { font-size: 12px; color: #555; margin-left: 8px; }
form {
    background-color: #fff;
    padding: 15px;
    border-radius: 8px;
    max-width: 400px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-top: 10px;
}
form input[type="text"],
form input[type="datetime-local"],
form input[type="file"] {
    width: 100%;
    padding: 8px;
    margin-bottom: 10px;
    border: 1px solid #ccc;
    border-radius: 6px;
    box-sizing: border-box;
    font-size: 14px;
}
form button {
    background-color: #28a745;
    color: #fff;
    padding: 8px 12px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
    transition: background-color 0.2s;
}
form button:hover { background-color: #218838; }
.stream-actions .btn-link {
    background:none;
    border:none;
    color:#007BFF;
    text-decoration:underline;
    cursor:pointer;
    font-size:14px;
    padding:0;
}
.stream-actions .btn-link:hover { color:#0056b3; }
@media (max-width: 600px) {
    .stream-item { flex-direction: column; align-items: flex-start; }
}
</style>
{{-- @endsection --}}
