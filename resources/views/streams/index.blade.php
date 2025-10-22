{{-- @extends('layouts.app')

@section('content') --}}
<div class="container">

    <h1>Live Streams</h1>

    @foreach($streams as $stream)
        <div class="stream-item">
            <a href="/stream/{{ $stream->id }}">{{ $stream->title }} ({{ $stream->status }})</a>
            <span class="stream-date">
                @if($stream->scheduled_at)
                    Scheduled at: {{ \Carbon\Carbon::parse($stream->scheduled_at)->format('d M Y, H:i') }}
                @endif
            </span>
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

<!-- Inline CSS -->
<style>
/* General page styling */
body {
    font-family: Arial, sans-serif;
    background-color: #f5f5f5;
    margin: 0;
    padding: 20px;
    color: #333;
}

/* Container */
.container {
    max-width: 800px;
    margin: auto;
}

/* Headings */
h1, h2 {
    color: #222;
    margin-bottom: 15px;
}

/* Stream list container */
.stream-item {
    background-color: #fff;
    padding: 12px 16px;
    margin-bottom: 10px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: transform 0.2s, box-shadow 0.2s;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.stream-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

/* Stream links */
.stream-item a {
    text-decoration: none;
    color: #007BFF;
    font-weight: bold;
    font-size: 16px;
}

.stream-item a:hover {
    text-decoration: underline;
}

/* Stream date text */
.stream-date {
    font-size: 12px;
    color: #555;
}

/* Form container */
form {
    background-color: #fff;
    padding: 20px;
    border-radius: 8px;
    max-width: 400px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-top: 20px;
}

/* Form inputs */
form input[type="text"],
form input[type="datetime-local"] {
    width: 100%;
    padding: 10px;
    margin-bottom: 12px;
    border: 1px solid #ccc;
    border-radius: 6px;
    box-sizing: border-box;
    font-size: 14px;
}

/* Submit button */
form button {
    background-color: #28a745;
    color: #fff;
    padding: 10px 16px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 16px;
    transition: background-color 0.2s;
}

form button:hover {
    background-color: #218838;
}

/* Responsive */
@media (max-width: 600px) {
    .stream-item {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>
{{-- @endsection --}}
