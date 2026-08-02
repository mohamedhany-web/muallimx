/**
 * Muallimx Classroom — Ambient noise isolation (Jitsi RNNoise + clean mic constraints)
 * + light meeting performance defaults (lastN / share FPS / adaptive resolution).
 * Works via External API setNoiseSuppressionEnabled; independent of physical mic brand.
 */
(function (global) {
  'use strict';

  var STORAGE_KEY = 'mx_classroom_noise_isolation_v1';

  /** WebRTC + Chrome legacy constraints for strongest browser-level cleanup */
  function getCleanMicAudioConstraints() {
    return {
      echoCancellation: true,
      noiseSuppression: true,
      autoGainControl: true,
      channelCount: { ideal: 1 },
      sampleRate: { ideal: 48000 },
      googEchoCancellation: true,
      googExperimentalEchoCancellation: true,
      googAutoGainControl: true,
      googExperimentalAutoGainControl: true,
      googNoiseSuppression: true,
      googExperimentalNoiseSuppression: true,
      googHighpassFilter: true,
      googTypingNoiseDetection: true,
      googAudioMirroring: false,
    };
  }

  /** Network-only save mode — do NOT use CPU core count (many laptops have ≤4 cores on good Wi‑Fi). */
  function prefersSaveBandwidth() {
    try {
      var conn = global.navigator && global.navigator.connection;
      if (conn) {
        if (conn.saveData) return true;
        var et = String(conn.effectiveType || '');
        if (et === '2g' || et === 'slow-2g' || et === '3g') return true;
      }
    } catch (e) {}
    return false;
  }

  function getVideoConstraintsForDevice() {
    // Camera stays below share priority; text/slides need the bitrate budget.
    if (prefersSaveBandwidth()) {
      return {
        height: { ideal: 360, max: 480 },
        width: { ideal: 640, max: 854 },
        frameRate: { ideal: 20, max: 24 },
      };
    }
    return {
      height: { ideal: 720, max: 720 },
      width: { ideal: 1280, max: 1280 },
      frameRate: { ideal: 30, max: 30 },
    };
  }

  function getJitsiAudioConfigPatch() {
    var save = prefersSaveBandwidth();
    var lastN = save ? 8 : 12;
    return {
      disableAP: false,
      disableAEC: false,
      disableNS: false,
      disableAGC: false,
      disableHPF: false,
      enableNoisyMicDetection: true,
      enableTalkWhileMuted: true,
      enableOpusRed: true,
      enableLayerSuspension: false,
      maxFullResolutionParticipants: 2,
      resolution: save ? 360 : 720,
      p2p: { enabled: false },
      audioQuality: {
        stereo: false,
        opusMaxAverageBitrate: 64000,
      },
      channelLastN: lastN,
      startLastN: lastN,
      // Force crisp durable screen share (overrides weak server/default caps).
      desktopSharingFrameRate: {
        min: save ? 20 : 24,
        max: 30,
      },
      videoQuality: {
        // Adaptive mode was crushing share clarity under tiny BWE dips.
        enableAdaptiveMode: false,
        codecPreferenceOrder: ['VP8', 'VP9', 'H264'],
        screenshareCodec: 'VP8',
        maxBitratesVideo: {
          low: 200000,
          standard: 500000,
          high: 1500000,
          fullHd: 2500000,
          ssHigh: save ? 2500000 : 3500000,
        },
        vp8: {
          maxBitratesVideo: {
            low: 200000,
            standard: 500000,
            high: 1500000,
            fullHd: 2500000,
            ssHigh: save ? 2500000 : 3500000,
          },
        },
        minHeightForQualityLvl: {
          180: 'low',
          360: 'standard',
          720: 'high',
        },
      },
      constraints: {
        audio: getCleanMicAudioConstraints(),
        video: getVideoConstraintsForDevice(),
      },
    };
  }

  function readSavedEnabled() {
    try {
      var raw = localStorage.getItem(STORAGE_KEY);
      if (raw === null || raw === undefined || raw === '') return true;
      var parsed = JSON.parse(raw);
      if (typeof parsed === 'boolean') return parsed;
      if (parsed && typeof parsed.enabled === 'boolean') return parsed.enabled;
    } catch (e) {}
    return true;
  }

  function saveEnabled(enabled) {
    try {
      localStorage.setItem(STORAGE_KEY, JSON.stringify({ enabled: !!enabled }));
    } catch (e) {}
  }

  function setNoiseSuppression(api, enabled) {
    if (!api || typeof api.executeCommand !== 'function') return false;
    var on = !!enabled;
    try {
      api.executeCommand('setNoiseSuppressionEnabled', { enabled: on });
      return true;
    } catch (e1) {
      try {
        api.executeCommand('setNoiseSuppressionEnabled', on);
        return true;
      } catch (e2) {
        try {
          if (on) api.executeCommand('toggleNoiseSuppression');
          return true;
        } catch (e3) {
          console.warn('MxNoiseIsolation: setNoiseSuppression failed', e3);
          return false;
        }
      }
    }
  }

  function reattachNoiseAfterTrackChange(api, enabled, delays) {
    var on = !!enabled;
    var times = Array.isArray(delays) && delays.length ? delays : [350, 1000, 2400];
    var okAny = false;
    times.forEach(function (ms) {
      setTimeout(function () {
        if (setNoiseSuppression(api, on)) okAny = true;
      }, ms);
    });
    if (setNoiseSuppression(api, on)) okAny = true;
    return okAny;
  }

  function enhanceMicStreamForRecording(micStream) {
    if (!micStream || !micStream.getAudioTracks().length) return null;
    var AudioCtx = global.AudioContext || global.webkitAudioContext;
    if (!AudioCtx) return null;
    try {
      var ctx = new AudioCtx();
      if (ctx.state === 'suspended' && typeof ctx.resume === 'function') {
        ctx.resume().catch(function () {});
      }
      var src = ctx.createMediaStreamSource(micStream);
      var hp = ctx.createBiquadFilter();
      hp.type = 'highpass';
      hp.frequency.value = 85;
      hp.Q.value = 0.7;

      var comp = ctx.createDynamicsCompressor();
      comp.threshold.value = -28;
      comp.knee.value = 18;
      comp.ratio.value = 3.5;
      comp.attack.value = 0.003;
      comp.release.value = 0.18;

      var gain = ctx.createGain();
      gain.gain.value = 1.05;

      var dest = ctx.createMediaStreamDestination();
      src.connect(hp);
      hp.connect(comp);
      comp.connect(gain);
      gain.connect(dest);

      var track = dest.stream.getAudioTracks()[0] || null;
      if (!track) {
        try { ctx.close(); } catch (eClose) {}
        return null;
      }
      return { stream: dest.stream, track: track, ctx: ctx };
    } catch (err) {
      console.warn('MxNoiseIsolation enhanceMicStreamForRecording', err);
      return null;
    }
  }

  function updateToggleUi(btn, enabled, theme) {
    if (!btn) return;
    btn.setAttribute('aria-pressed', enabled ? 'true' : 'false');
    if (enabled) btn.classList.add('is-active');
    else btn.classList.remove('is-active');
    var icon = btn.querySelector('i');
    var isDark = theme === 'dark';
    if (icon) {
      if (isDark) {
        icon.className = enabled
          ? 'fas fa-ear-listen text-cyan-300'
          : 'fas fa-ear-listen text-slate-400';
      } else {
        icon.className = enabled
          ? 'fas fa-ear-listen text-[#0065fd]'
          : 'fas fa-ear-listen text-[#717171]';
      }
    }
    btn.title = enabled
      ? 'عزل الضوضاء: مفعّل (صوت نقي)'
      : 'عزل الضوضاء: متوقف — اضغط للتفعيل';
  }

  function bindUi(opts) {
    opts = opts || {};
    var btn = opts.toggleBtn;
    var getApi = opts.getApi || function () { return null; };
    var onToast = typeof opts.onToast === 'function' ? opts.onToast : function () {};
    var theme = opts.theme === 'dark' ? 'dark' : 'light';
    var enabled = typeof opts.defaultEnabled === 'boolean' ? opts.defaultEnabled : readSavedEnabled();
    var shareReattachTimer = null;
    var joined = false;
    var failToastShown = false;

    function apiReady() {
      var api = getApi();
      return !!(api && typeof api.executeCommand === 'function');
    }

    function apply(next, announce) {
      enabled = !!next;
      saveEnabled(enabled);
      updateToggleUi(btn, enabled, theme);
      if (!apiReady()) {
        if (announce && joined === false) {
          // قبل دخول الغرفة: لا رسالة خطأ مزعجة
          onToast(enabled ? 'عزل الضوضاء سيُفعَّل بعد دخول الغرفة' : 'عزل الضوضاء متوقف');
        }
        return false;
      }
      var ok = setNoiseSuppression(getApi(), enabled);
      if (announce) {
        if (ok) {
          onToast(enabled ? 'عزل الضوضاء مفعّل — الصوت أنقى' : 'عزل الضوضاء متوقف');
        } else if (joined && !failToastShown) {
          failToastShown = true;
          onToast('تعذر تطبيق عزل الضوضاء على هذا المتصفح — الميكروفون يعمل بالإعدادات الافتراضية');
        }
      }
      return ok;
    }

    if (btn) {
      updateToggleUi(btn, enabled, theme);
      btn.addEventListener('click', function () {
        apply(!enabled, true);
      });
    }

    return {
      isEnabled: function () { return enabled; },
      setEnabled: function (v, announce) { return apply(!!v, !!announce); },
      markJoined: function () {
        joined = true;
        failToastShown = false;
      },
      enableOnJoin: function () {
        var self = this;
        joined = true;
        setTimeout(function () { apply(enabled, false); }, 400);
        setTimeout(function () { apply(enabled, false); }, 1600);
        return self;
      },
      applyToApi: function (api) {
        return setNoiseSuppression(api, enabled);
      },
      onScreenShareChanged: function (sharing) {
        if (!joined || !apiReady()) return;
        reattachNoiseAfterTrackChange(getApi(), enabled, [350, 1000, 2400]);
        if (shareReattachTimer) clearTimeout(shareReattachTimer);
        // بدون توست متكرر أثناء الشير
      },
      reattachAfterTrackChange: function () {
        return reattachNoiseAfterTrackChange(getApi(), enabled);
      },
    };
  }

  global.MxClassroomNoiseIsolation = {
    getCleanMicAudioConstraints: getCleanMicAudioConstraints,
    getJitsiAudioConfigPatch: getJitsiAudioConfigPatch,
    getVideoConstraintsForDevice: getVideoConstraintsForDevice,
    prefersSaveBandwidth: prefersSaveBandwidth,
    setNoiseSuppression: setNoiseSuppression,
    reattachNoiseAfterTrackChange: reattachNoiseAfterTrackChange,
    enhanceMicStreamForRecording: enhanceMicStreamForRecording,
    readSavedEnabled: readSavedEnabled,
    bindUi: bindUi,
  };
})(typeof window !== 'undefined' ? window : this);
