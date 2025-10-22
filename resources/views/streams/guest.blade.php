<h1>Guest: {{ $guest->name }}</h1>

<video id="remote" autoplay playsinline></video>
<video id="local" autoplay muted playsinline width="320"></video>

<script>
const streamId = {{ $stream->id }};
const guestUUID = '{{ $guest->uuid }}';

let localStream, pc;

async function init() {
    localStream = await navigator.mediaDevices.getUserMedia({ video:true, audio:true });
    document.getElementById('local').srcObject = localStream;

    pc = new RTCPeerConnection();

    localStream.getTracks().forEach(track => pc.addTrack(track, localStream));

    pc.ontrack = e => {
        document.getElementById('remote').srcObject = e.streams[0];
    };

    pc.onicecandidate = event => {
        if (event.candidate) {
            fetch('/api/webrtc/send', {
                method:'POST',
                headers:{'Content-Type':'application/json'},
                body: JSON.stringify({
                    stream_id: streamId,
                    sender_type:'guest',
                    receiver_type:'host',
                    data:{ type:'ice', candidate:event.candidate, guest:guestUUID }
                })
            });
        }
    };

    // Poll host offer
    const pollOffer = async () => {
        const res = await fetch(`/api/webrtc/receive/${streamId}/guest`);
        const signals = await res.json();

        for (let signal of signals) {
            if (signal.data.type === 'offer' && signal.data.guest === guestUUID) {
                await pc.setRemoteDescription({ type:'offer', sdp: signal.data.sdp });
                const answer = await pc.createAnswer();
                await pc.setLocalDescription(answer);

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
            } else if(signal.data.type === 'ice') {
                pc.addIceCandidate(signal.data.candidate);
            }
        }
    };

    setInterval(pollOffer, 1000);
}

init();
</script>
