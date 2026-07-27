/**
 * Muallimx Classroom — Ambient noise isolation (Jitsi RNNoise + clean mic constraints)
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
      // Legacy Chrome keys — ignored when unsupported
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

  /** Shared Jitsi configOverwrite audio block for host + guest */
  function getJitsiAudioConfigPatch() {
    return {
      disableAP: false,
      disableAEC: false,
      disableNS: false,
      disableAGC: false,
      disableHPF: false,
      enableNoisyMicDetection: true,
      enableTalkWhileMuted: true,
      enableOpusRed: true,
      audioQuality: {
        stereo: false,
        opusMaxAverageBitrate: 64000,
      },
      constraints: {
        audio: getCleanMicAudioConstraints(),
        video: {
          height: { ideal: 720, max: 1080 },
          width: { ideal: 1280, max: 1920 },
        },
      },
    };
  }

  function readSavedEnabled() {
    try {
      var raw = localStorage.getItem(STORAGE_KEY);
      if (raw === null || raw === undefined || raw === '') return true; // default ON
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
        // Older builds: boolean arg
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

  /**
   * Extra cleanup for MediaRecorder (lecture/report): high-pass + mild compression.
   * Returns { stream, track, ctx } or null on failure (caller keeps original mic).
   */
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
          ? 'fas fa-ear-listen text-[#0065fd] text-sm'
          : 'fas fa-ear-listen text-[#717171] text-sm';
      }
    }
    btn.title = enabled
      ? 'عزل الضوضاء: مفعّل (صوت نقي)'
      : 'عزل الضوضاء: متوقف — اضغط للتفعيل';
  }

  /**
   * @param {object} opts
   * @param {HTMLElement} opts.toggleBtn
   * @param {function(): any} opts.getApi
   * @param {function(string)=} opts.onToast
   * @param {boolean=} opts.defaultEnabled
   * @param {string=} opts.theme 'light' | 'dark'
   */
  function bindUi(opts) {
    opts = opts || {};
    var btn = opts.toggleBtn;
    var getApi = opts.getApi || function () { return null; };
    var onToast = typeof opts.onToast === 'function' ? opts.onToast : function () {};
    var theme = opts.theme === 'dark' ? 'dark' : 'light';
    var enabled = typeof opts.defaultEnabled === 'boolean' ? opts.defaultEnabled : readSavedEnabled();

    function apply(next, announce) {
      enabled = !!next;
      saveEnabled(enabled);
      updateToggleUi(btn, enabled, theme);
      var api = getApi();
      var ok = setNoiseSuppression(api, enabled);
      if (announce) {
        if (ok) {
          onToast(enabled ? 'عزل الضوضاء مفعّل — الصوت أنقى' : 'عزل الضوضاء متوقف');
        } else {
          onToast('تعذر تطبيق عزل الضوضاء — أعد المحاولة بعد دخول الغرفة');
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
      enableOnJoin: function () {
        // Re-apply after join (track may not exist before)
        var self = this;
        setTimeout(function () { apply(enabled, false); }, 400);
        setTimeout(function () { apply(enabled, false); }, 1600);
        return self;
      },
      applyToApi: function (api) {
        return setNoiseSuppression(api, enabled);
      },
    };
  }

  global.MxClassroomNoiseIsolation = {
    getCleanMicAudioConstraints: getCleanMicAudioConstraints,
    getJitsiAudioConfigPatch: getJitsiAudioConfigPatch,
    setNoiseSuppression: setNoiseSuppression,
    enhanceMicStreamForRecording: enhanceMicStreamForRecording,
    readSavedEnabled: readSavedEnabled,
    bindUi: bindUi,
  };
})(typeof window !== 'undefined' ? window : this);
