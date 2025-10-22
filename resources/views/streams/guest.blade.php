<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Guest: {{ $guest->name }}</title>
<style>
body {
    font-family: Arial, sans-serif;
    background-color: #f9f9f9;
    color: #333;
    margin: 20px;
}
h1 { font-size: 28px; margin-bottom: 20px; }
#videos {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}
video {
    border-radius: 8px;
    background-color: #000;
    object-fit: cover;
    border: 2px solid #ddd;
    margin-bottom: 10px;
}
#hostVideo, #localVideo {
    width: 320px;
    height: 240px;
}
@media(max-width:768px){
    #hostVideo, #localVideo { width: 100%; height: auto; }
}
</style>
</head>
<body>

<h1>Guest: {{ $guest->name }}</h1>

<div id="videos">
    <video id="hostVideo" autoplay playsinline></video>
    <video id="localVideo" autoplay muted playsinline></video>
</div>

<script>
const streamId = "{{ $stream->id }}";
const guestUUID = '{{ $guest->uuid }}';

let localStream, pc;

// Helper to send signals
async function sendSignal(payload){
    try{
        const res = await fetch('/api/webrtc/send',{
            method:'POST',
            headers:{'Content-Type':'application/json'},
            body: JSON.stringify(payload)
        });
        if(!res.ok) console.error('Signal send error', await res.text());
    } catch(e){ console.error('Signal send exception', e); }
}

// Initialize guest
async function initGuest() {
    try {
        // Access camera and mic
        localStream = await navigator.mediaDevices.getUserMedia({ video:true, audio:true });
        document.getElementById('localVideo').srcObject = localStream;

        // Create PeerConnection
        pc = new RTCPeerConnection();

        // Add local tracks
        localStream.getTracks().forEach(track => pc.addTrack(track, localStream));

        // Receive host tracks
        pc.ontrack = event => {
            document.getElementById('hostVideo').srcObject = event.streams[0];
        };

        // Send ICE candidates
        pc.onicecandidate = event => {
            if(event.candidate){
                sendSignal({
                    stream_id: streamId,
                    sender_type: 'guest',
                    receiver_type: 'host',
                    data: { type:'ice', candidate:event.candidate, guest:guestUUID }
                });
            }
        };

        // Poll host offers every 1s
        setInterval(async ()=>{
            try {
                const res = await fetch(`/api/webrtc/receive/${streamId}/guest`);
                const signals = await res.json();

                for(let signal of signals){
                    if(signal.data.guest !== guestUUID) continue;

                    if(signal.data.type === 'offer'){
                        await pc.setRemoteDescription({ type:'offer', sdp: signal.data.sdp });
                        const answer = await pc.createAnswer();
                        await pc.setLocalDescription(answer);

                        sendSignal({
                            stream_id: streamId,
                            sender_type:'guest',
                            receiver_type:'host',
                            data:{ type:'answer', sdp: answer.sdp, guest:guestUUID }
                        });
                    } else if(signal.data.type === 'ice'){
                        pc.addIceCandidate(signal.data.candidate).catch(e=>console.error("ICE error:", e));
                    }
                }
            } catch(e){ console.error("Polling error:", e); }
        }, 1000);

    } catch(e){
        console.error("Camera access error:", e);
        alert("Cannot access camera or microphone: " + e.message);
    }
}

initGuest();
</script>
</body>
</html>
