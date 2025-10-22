
<style>
body {
    font-family: Arial, sans-serif;
    background-color: #f9f9f9;
    color: #333;
    margin: 20px;
}
h1 { font-size: 28px; margin-bottom: 20px; }
video { border-radius: 8px; background-color: #000; object-fit: cover; border: 2px solid #ddd; margin-bottom: 10px; }
#videos { display: flex; gap: 15px; flex-wrap: wrap; }
#hostVideo, #localVideo { width: 320px; height: 240px; }
@media(max-width:768px){
    #hostVideo, #localVideo { width: 100%; height: auto; }
}
</style>


<h1>Guest: {{ $guest->name }}</h1>

<div id="videos">
    <video id="hostVideo" autoplay playsinline></video>
    <video id="localVideo" autoplay muted playsinline></video>
</div>

<script>
const streamId = "{{ $stream->id }}"; // wrapped in quotes
const guestUUID = '{{ $guest->uuid }}';

let localStream, pc;

// Helper to send signals to backend
async function sendSignal(payload){
    try{
        const res = await fetch('/api/webrtc/send',{
            method:'POST',
            headers:{'Content-Type':'application/json'},
            body: JSON.stringify(payload)
        });
        if(!res.ok){
            console.error('Signal send error', await res.text());
        }
    } catch(e){
        console.error('Signal send exception', e);
    }
}

// Initialize guest
async function initGuest() {
    try {
        // Get camera and mic
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

        // Poll host offers every second
        setInterval(async ()=>{
            try {
                const res = await fetch(`/api/webrtc/receive/${streamId}/guest`);
                const signals = await res.json();

                for(let signal of signals){
                    // Only process signals for this guest
                    if(signal.data.guest !== guestUUID) continue;

                    if(signal.data.type === 'offer'){
                        await pc.setRemoteDescription({ type:'offer', sdp: signal.data.sdp });

                        // Create answer
                        const answer = await pc.createAnswer();
                        await pc.setLocalDescription(answer);

                        sendSignal({
                            stream_id: streamId,
                            sender_type:'guest',
                            receiver_type:'host',
                            data:{ type:'answer', sdp: answer.sdp, guest:guestUUID }
                        });
                    } else if(signal.data.type === 'ice'){
                        pc.addIceCandidate(signal.data.candidate).catch(e=>console.error("ICE add error:", e));
                    }
                }
            } catch(e){
                console.error("Polling error:", e);
            }
        }, 1000);

    } catch(e){
        console.error("Camera access error:", e);
        alert("Cannot access camera or microphone: " + e.message);
    }
}

initGuest();
</script>

