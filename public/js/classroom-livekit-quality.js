/**
 * LiveKit Classroom quality defaults (screen-share first).
 * Keep adaptiveStream OFF while comparing vs Jitsi — it was downscaling share to tile size.
 */
window.MX_LIVEKIT_QUALITY = {
    roomOptions: {
        adaptiveStream: false,
        dynacast: false,
        videoCaptureDefaults: {
            resolution: { width: 1280, height: 720, frameRate: 30 },
        },
        publishDefaults: {
            videoCodec: 'vp8',
            videoSimulcastLayers: [],
            // Crisp classroom screen share (text/slides) — ~6 Mbps @ 1080p30
            screenShareEncoding: { maxBitrate: 6_000_000, maxFramerate: 30 },
            screenShareSimulcastLayers: [],
            scalabilityMode: undefined,
        },
    },
    screenShareCapture: {
        audio: false,
        resolution: { width: 1920, height: 1080, frameRate: 30 },
        contentHint: 'detail',
    },
    screenSharePublish: {
        source: 'screen_share',
        name: 'screen',
        simulcast: false,
        videoCodec: 'vp8',
        screenShareEncoding: { maxBitrate: 6_000_000, maxFramerate: 30 },
    },
};

window.mxLiveKitForceHighQuality = function (publication) {
    try {
        if (!publication) return;
        if (typeof publication.setVideoQuality === 'function' && window.VideoQuality) {
            publication.setVideoQuality(window.VideoQuality.HIGH);
        }
    } catch (_) {}
};
