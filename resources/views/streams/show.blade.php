<h1>{{ $stream->title }} ({{ $stream->status }})</h1>

<h3>Local Cameras</h3>
<video id="camera1" autoplay muted playsinline width="320"></video>
<video id="camera2" autoplay muted playsinline width="320"></video>

<h3>Guest Videos</h3>
<div id="guest-videos"></div>

<script>
const streamId = {{ $stream->id }};
const localStreams = [];
const peers = {}; // guest UUID => RTCPeerConnection

async function initCameras() {
    const devices = await navigator.mediaDevices.enumerateDevices();
    const videoDevices = devices.filter(d => d.kind === 'videoinput');

    for (let i = 0; i < Math.min(2, videoDevices.length); i++) {
        const stream = await navigator.mediaDevices.getUserMedia({ video: { deviceId: videoDevices[i].deviceId }, audio: true });
        localStreams.push(stream);
        document.getElementById(`camera${i+1}`).srcObject = stream;
    }
}

async function createPeerConnection(guestUUID) {
    const pc = new RTCPeerConnection();

    // Add host streams
    localStreams.forEach(stream => {
        stream.getTracks().forEach(track => pc.addTrack(track, stream));
    });

    // Remote guest video
    const videoEl = document.createElement('video');
    videoEl.autoplay = true;
    videoEl.playsInline = true;
    videoEl.id = 'guest_' + guestUUID;
    document.getElementById('guest-videos').appendChild(videoEl);

    pc.ontrack = e => {
        videoEl.srcObject = e.streams[0];
    };

    // Send ICE candidates to guest
    pc.onicecandidate = event => {
        if (event.candidate) {
            fetch('/api/webrtc/send', {
                method: 'POST',
                headers: {'Content-Type':'application/json'},
                body: JSON.stringify({
                    stream_id: streamId,
                    sender_type: 'host',
                    receiver_type: 'guest',
                    data: { type:'ice', candidate: event.candidate, guest: guestUUID }
                })
            });
        }
    };

    peers[guestUUID] = pc;

    // Create offer
    const offer = await pc.createOffer();
    await pc.setLocalDescription(offer);

    await fetch('/api/webrtc/send', {
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body: JSON.stringify({
            stream_id: streamId,
            sender_type: 'host',
            receiver_type: 'guest',
            data:{ type:'offer', sdp: offer.sdp, guest: guestUUID }
        })
    });
}

// Polling to receive answers and ICE candidates
setInterval(async () => {
    const res = await fetch(`/api/webrtc/receive/${streamId}/host`);
    const signals = await res.json();

    signals.forEach(signal => {
        const guest = signal.data.guest;
        const pc = peers[guest];
        if (!pc) return;

        if (signal.data.type === 'answer') {
            pc.setRemoteDescription({ type: 'answer', sdp: signal.data.sdp });
        } else if (signal.data.type === 'ice') {
            pc.addIceCandidate(signal.data.candidate);
        }
    });
}, 1000);

initCameras();
</script>
