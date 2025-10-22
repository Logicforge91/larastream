<h1>Guest: {{ $guest->name }}</h1>

<div style="display:flex; gap:10px;">
    <video id="hostVideo" autoplay playsinline width="320"></video>
    <video id="localVideo" autoplay muted playsinline width="320"></video>
</div>

<script>
const streamId = {{ $stream->id }};
const guestUUID = '{{ $guest->uuid }}';

let localStream, pc;

// Initialize guest
async function initGuest() {
    // Get guest camera and microphone
    localStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
    document.getElementById('localVideo').srcObject = localStream;

    // Create PeerConnection
    pc = new RTCPeerConnection();

    // Send local tracks
    localStream.getTracks().forEach(track => pc.addTrack(track, localStream));

    // Receive host tracks
    pc.ontrack = event => {
        document.getElementById('hostVideo').srcObject = event.streams[0];
    };

    // Send ICE candidates to host
    pc.onicecandidate = event => {
        if(event.candidate){
            fetch('/api/webrtc/send', {
                method:'POST',
                headers:{'Content-Type':'application/json'},
                body: JSON.stringify({
                    stream_id: streamId,
                    sender_type: 'guest',
                    receiver_type: 'host',
                    data: { type:'ice', candidate:event.candidate, guest:guestUUID }
                })
            });
        }
    };

    // Poll host offers
    setInterval(async ()=>{
        const res = await fetch(`/api/webrtc/receive/${streamId}/guest`);
        const signals = await res.json();

        for(let signal of signals){
            if(signal.data.guest !== guestUUID) continue;

            if(signal.data.type === 'offer'){
                // Set remote description from host
                await pc.setRemoteDescription({ type:'offer', sdp: signal.data.sdp });

                // Create answer
                const answer = await pc.createAnswer();
                await pc.setLocalDescription(answer);

                // Send answer back to host
                await fetch('/api/webrtc/send', {
                    method:'POST',
                    headers:{'Content-Type':'application/json'},
                    body: JSON.stringify({
                        stream_id: streamId,
                        sender_type:'guest',
                        receiver_type:'host',
                        data:{ type:'answer', sdp: answer.sdp, guest:guestUUID }
                    })
                });
            } else if(signal.data.type === 'ice'){
                pc.addIceCandidate(signal.data.candidate);
            }
        }
    }, 1000);
}

initGuest();
</script>
