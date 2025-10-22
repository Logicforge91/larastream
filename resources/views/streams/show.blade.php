<h1>{{ $stream->title }} ({{ $stream->status }})</h1>

<h3>Camera Selection & Switching</h3>
<select id="cameraSelect1"></select>
<button onclick="switchCamera(0)">Switch Camera 1</button>

<select id="cameraSelect2"></select>
<button onclick="switchCamera(1)">Switch Camera 2</button>

<div style="display:flex; gap:10px;">
    <video id="camera1" autoplay muted playsinline width="320"></video>
    <video id="camera2" autoplay muted playsinline width="320"></video>
</div>

<h3>Guests</h3>
<div id="guest-videos" style="display:flex; flex-wrap:wrap; gap:10px;"></div>

<h3>Pre-recorded Video Injection</h3>
<select id="videoSelect">
    @foreach($stream->videos as $video)
        <option value="{{ $video->filename }}">{{ $video->title }}</option>
    @endforeach
</select>
<button onclick="playVideo()">Play Video</button>
<video id="preVideo" src="" width="320" style="display:none"></video>

<script>
const streamId = {{ $stream->id }};
let localStreams = [];
let currentDevices = [];
const peers = {}; // guestUUID => RTCPeerConnection

// Initialize cameras
async function initCameras() {
    const devices = await navigator.mediaDevices.enumerateDevices();
    const videoDevices = devices.filter(d => d.kind === 'videoinput');
    currentDevices = [videoDevices[0]?.deviceId, videoDevices[1]?.deviceId];

    const select1 = document.getElementById('cameraSelect1');
    const select2 = document.getElementById('cameraSelect2');

    videoDevices.forEach(d => {
        select1.append(new Option(d.label, d.deviceId));
        select2.append(new Option(d.label, d.deviceId));
    });

    for (let i = 0; i < 2; i++) {
        if(currentDevices[i]) {
            const stream = await navigator.mediaDevices.getUserMedia({ video:{deviceId: currentDevices[i]}, audio:true });
            localStreams[i] = stream;
            document.getElementById(`camera${i+1}`).srcObject = stream;
        }
    }
}

// Switch camera dynamically
async function switchCamera(index) {
    const select = document.getElementById(`cameraSelect${index+1}`);
    const deviceId = select.value;
    const newStream = await navigator.mediaDevices.getUserMedia({ video:{deviceId}, audio:true });

    // Stop old tracks
    localStreams[index].getTracks().forEach(track => track.stop());
    localStreams[index] = newStream;
    document.getElementById(`camera${index+1}`).srcObject = newStream;

    // Replace tracks in all peer connections
    for (let guest in peers) {
        const sender = peers[guest].getSenders().find(s => s.track.kind === 'video');
        if(sender) sender.replaceTrack(newStream.getVideoTracks()[0]);
    }
}

// Inject pre-recorded video
async function playVideo() {
    const select = document.getElementById('videoSelect');
    const videoEl = document.getElementById('preVideo');
    videoEl.src = '/uploads/' + select.value;
    await videoEl.play();

    const preStream = videoEl.captureStream();

    for(let guest in peers) {
        const sender = peers[guest].getSenders().find(s => s.track.kind === 'video');
        if(sender) sender.replaceTrack(preStream.getVideoTracks()[0]);
    }

    // Restore cameras after video ends
    videoEl.onended = () => {
        for(let i=0; i<localStreams.length; i++){
            for(let guest in peers){
                const sender = peers[guest].getSenders().find(s=>s.track.kind==='video');
                if(sender) sender.replaceTrack(localStreams[i].getVideoTracks()[0]);
            }
        }
    };
}

// Create PeerConnection for a guest
async function createPeerConnection(guestUUID){
    const pc = new RTCPeerConnection();

    // Add all camera tracks
    localStreams.forEach(stream => {
        stream.getTracks().forEach(track => pc.addTrack(track, stream));
    });

    // Guest video element
    const videoEl = document.createElement('video');
    videoEl.autoplay = true;
    videoEl.playsInline = true;
    videoEl.id = 'guest_' + guestUUID;
    document.getElementById('guest-videos').appendChild(videoEl);

    pc.ontrack = e => videoEl.srcObject = e.streams[0];

    pc.onicecandidate = event => {
        if(event.candidate){
            fetch('/api/webrtc/send', {
                method:'POST',
                headers:{'Content-Type':'application/json'},
                body: JSON.stringify({
                    stream_id: streamId,
                    sender_type:'host',
                    receiver_type:'guest',
                    data:{ type:'ice', candidate:event.candidate, guest:guestUUID }
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
            sender_type:'host',
            receiver_type:'guest',
            data:{ type:'offer', sdp: offer.sdp, guest: guestUUID }
        })
    });
}

// Polling for answers and ICE
setInterval(async ()=>{
    const res = await fetch(`/api/webrtc/receive/${streamId}/host`);
    const signals = await res.json();

    signals.forEach(signal=>{
        const guest = signal.data.guest;
        const pc = peers[guest];
        if(!pc) return;

        if(signal.data.type==='answer'){
            pc.setRemoteDescription({ type:'answer', sdp: signal.data.sdp });
        } else if(signal.data.type==='ice'){
            pc.addIceCandidate(signal.data.candidate);
        }
    });
}, 1000);

initCameras();
</script>
