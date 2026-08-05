/**
 * Muallimx Classroom — LiveKit Meet.Line client (shared host/guest).
 * Boot: window.MxLiveKitClassroom.boot(LivekitClient, config)
 * Inlined into blades (production often 404s /js/*.js static).
 */
(function (global) {
  'use strict';

  var PROTO = 1;

  function encodeMsg(type, payload) {
    return JSON.stringify({ v: PROTO, t: type, p: payload || {}, ts: Date.now() });
  }

  function decodeMsg(data) {
    try {
      var raw = typeof data === 'string' ? data : new TextDecoder().decode(data);
      var msg = JSON.parse(raw);
      if (!msg || msg.v !== PROTO || !msg.t) return null;
      return msg;
    } catch (e) {
      return null;
    }
  }

  function $(id) {
    return document.getElementById(id);
  }

  function toast(text, ms) {
    var el = $('mx-lk-toast');
    if (!el) return;
    el.textContent = text;
    el.classList.add('is-visible');
    clearTimeout(toast._t);
    toast._t = setTimeout(function () {
      el.classList.remove('is-visible');
    }, ms || 2400);
  }

  function formatBytes(bytes) {
    var n = Number(bytes) || 0;
    if (n < 1024) return n + ' B';
    if (n < 1048576) return (n / 1024).toFixed(0) + ' KB';
    if (n < 1073741824) return (n / 1048576).toFixed(1) + ' MB';
    return (n / 1073741824).toFixed(2) + ' GB';
  }

  var uploadModal = {
    ensure: function () {
      var el = $('mx-lk-upload-modal');
      if (el) return el;
      el = document.createElement('div');
      el.id = 'mx-lk-upload-modal';
      el.setAttribute('role', 'dialog');
      el.setAttribute('aria-modal', 'true');
      el.innerHTML =
        '<div class="mx-lk-up-card">' +
        '<div class="mx-lk-up-icon" id="mx-lk-up-icon"><i class="fa-solid fa-cloud-arrow-up"></i></div>' +
        '<div class="mx-lk-up-title" id="mx-lk-up-title">جاري رفع التسجيل</div>' +
        '<div class="mx-lk-up-sub" id="mx-lk-up-sub">يرجى عدم إغلاق الصفحة حتى اكتمال الرفع.</div>' +
        '<div class="mx-lk-up-bar"><div class="mx-lk-up-fill" id="mx-lk-up-fill"></div></div>' +
        '<div class="mx-lk-up-meta"><span id="mx-lk-up-percent">0%</span><span id="mx-lk-up-size"></span></div>' +
        '<button type="button" class="mx-lk-up-close" id="mx-lk-up-close">إغلاق</button>' +
        '</div>';
      document.body.appendChild(el);
      var closeBtn = $('mx-lk-up-close');
      if (closeBtn) {
        closeBtn.addEventListener('click', function () {
          uploadModal.hide();
        });
      }
      return el;
    },
    open: function (title, sub) {
      var el = this.ensure();
      el.classList.remove('is-error', 'is-done');
      el.classList.add('is-open', 'is-indeterminate');
      var t = $('mx-lk-up-title');
      if (t) t.textContent = title || 'جاري رفع التسجيل';
      var s = $('mx-lk-up-sub');
      if (s) s.textContent = sub || 'يرجى عدم إغلاق الصفحة حتى اكتمال الرفع.';
      var icon = $('mx-lk-up-icon');
      if (icon) icon.innerHTML = '<i class="fa-solid fa-cloud-arrow-up"></i>';
      var fill = $('mx-lk-up-fill');
      if (fill) fill.style.width = '0%';
      var pct = $('mx-lk-up-percent');
      if (pct) pct.textContent = '—';
      var size = $('mx-lk-up-size');
      if (size) size.textContent = '';
    },
    status: function (sub) {
      var s = $('mx-lk-up-sub');
      if (s) s.textContent = sub;
    },
    progress: function (percent, loaded, total) {
      var el = $('mx-lk-upload-modal');
      if (!el) return;
      var p = Math.max(0, Math.min(100, Math.round(percent)));
      el.classList.remove('is-indeterminate');
      var fill = $('mx-lk-up-fill');
      if (fill) fill.style.width = p + '%';
      var pct = $('mx-lk-up-percent');
      if (pct) pct.textContent = p + '%';
      var size = $('mx-lk-up-size');
      if (size && total) size.textContent = formatBytes(loaded) + ' / ' + formatBytes(total);
    },
    indeterminate: function (sub) {
      var el = $('mx-lk-upload-modal');
      if (!el) return;
      el.classList.add('is-indeterminate');
      var pct = $('mx-lk-up-percent');
      if (pct) pct.textContent = '—';
      if (sub) this.status(sub);
    },
    done: function (title, sub) {
      var el = this.ensure();
      el.classList.remove('is-indeterminate', 'is-error');
      el.classList.add('is-open', 'is-done');
      var t = $('mx-lk-up-title');
      if (t) t.textContent = title || 'تم رفع التسجيل';
      var s = $('mx-lk-up-sub');
      if (s) s.textContent = sub || 'يمكنك الآن متابعة الجلسة.';
      var icon = $('mx-lk-up-icon');
      if (icon) icon.innerHTML = '<i class="fa-solid fa-circle-check"></i>';
      var fill = $('mx-lk-up-fill');
      if (fill) fill.style.width = '100%';
      var pct = $('mx-lk-up-percent');
      if (pct) pct.textContent = '100%';
      clearTimeout(uploadModal._t);
      uploadModal._t = setTimeout(function () {
        uploadModal.hide();
      }, 4000);
    },
    fail: function (message) {
      var el = this.ensure();
      el.classList.remove('is-indeterminate', 'is-done');
      el.classList.add('is-open', 'is-error');
      var t = $('mx-lk-up-title');
      if (t) t.textContent = 'تعذر رفع التسجيل';
      var s = $('mx-lk-up-sub');
      if (s) s.textContent = message || 'حدث خطأ غير متوقع أثناء الرفع.';
      var icon = $('mx-lk-up-icon');
      if (icon) icon.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i>';
    },
    hide: function () {
      var el = $('mx-lk-upload-modal');
      if (!el) return;
      clearTimeout(uploadModal._t);
      el.classList.remove('is-open', 'is-indeterminate', 'is-done', 'is-error');
    },
  };

  function warnBeforeUnload(ev) {
    ev.preventDefault();
    ev.returnValue = '';
    return '';
  }

  function beginUploadGuard() {
    window.addEventListener('beforeunload', warnBeforeUnload);
  }

  function endUploadGuard() {
    window.removeEventListener('beforeunload', warnBeforeUnload);
  }

  function qualityLabel(q, ConnectionQuality) {
    if (!ConnectionQuality) return '—';
    if (q === ConnectionQuality.Excellent) return 'ممتاز';
    if (q === ConnectionQuality.Good) return 'جيد';
    if (q === ConnectionQuality.Poor) return 'ضعيف';
    if (q === ConnectionQuality.Lost) return 'مقطوع';
    return '—';
  }

  function qualityLevel(q, ConnectionQuality) {
    if (!ConnectionQuality) return 0;
    if (q === ConnectionQuality.Excellent) return 4;
    if (q === ConnectionQuality.Good) return 3;
    if (q === ConnectionQuality.Poor) return 2;
    if (q === ConnectionQuality.Lost) return 1;
    return 0;
  }

  async function boot(LK, config) {
    config = config || {};
    var Room = LK.Room;
    var RoomEvent = LK.RoomEvent;
    var Track = LK.Track;
    var createLocalTracks = LK.createLocalTracks;
    var createLocalScreenTracks = LK.createLocalScreenTracks;
    var VideoPresets = LK.VideoPresets;
    var VideoQuality = LK.VideoQuality;
    var ScreenSharePresets = LK.ScreenSharePresets;
    var ConnectionQuality = LK.ConnectionQuality;
    var DataPacket_Kind = LK.DataPacket_Kind || { RELIABLE: 0 };

    var isHost = !!config.isHost;
    var stage = $(config.stageId || 'lk-stage');
    var peopleList = $(config.peopleListId || 'mx-lk-people-list');
    var chatLog = $(config.chatLogId || 'mx-lk-chat-log');
    var chatInput = $(config.chatInputId || 'mx-lk-chat-input');
    var statusEl = $(config.statusId || 'lk-status');
    var qualityEl = $(config.qualityId || 'mx-ml-quality');
    var qualityLabelEl = $(config.qualityLabelId || 'mx-ml-quality-label');
    var countEl = $(config.countId || 'mx-lk-count');

    var perms = Object.assign(
      {
        waiting_room_enabled: false,
        allow_participant_whiteboard: true,
        allow_participant_screen_share: true,
        allow_participant_chat: true,
        allow_participant_raise_hand: true,
        allow_participant_virtual_background: true,
      },
      config.permissions || {}
    );

    var room = new Room({
      adaptiveStream: false,
      dynacast: false,
      videoCaptureDefaults: { resolution: VideoPresets.h720.resolution },
      publishDefaults: {
        videoCodec: 'vp8',
        videoSimulcastLayers: [VideoPresets.h180, VideoPresets.h360],
        screenShareEncoding: { maxBitrate: 6_000_000, maxFramerate: 30 },
        screenShareSimulcastLayers: [],
      },
    });

    var tileMap = new Map();
    var raisedHands = new Set();
    var curriculumActive = false;
    var curriculumHandler = null;
    var curriculumSnapshot = null;
    var localAudio = null;
    var localVideo = null;
    var localScreenTracks = [];
    var micOn = false;
    var camOn = false;
    var shareOn = false;
    var handRaised = false;
    var mediaRecorder = null;
    var recordedChunks = [];
    var recordingKind = null;
    var recordingStream = null;
    var api = {};
    var layoutMode = 'grid'; // grid | speaker
    var noiseOn = true;
    var krispProcessor = null;
    var krispReady = false;
    var activeSpeakerId = '';
    var REACTIONS = ['👍', '👏', '❤️', '😂', '😮', '🎉', '🔥', '✋'];
    var waitingRoomPollTimer = null;
    var participantFloatMode = 'grid';
    var participantFloatWindow = null;
    var participantFloatCanvasTimer = null;
    var participantFloatCanvasVideo = null;
    var participantFloatCanvasEl = null;
    var participantPipOpen = false;
    var participantPipManual = false;
    var participantPipAutoOpened = false;
    var participantPipSurface = 'none';
    var participantPipRefreshPending = false;
    var participantFloatCanvasDirty = true;
    var participantFloatCanvasLastSig = '';

    function waitingRoomBaseUrl() {
      return (config.waitingRoomListUrl || '').replace(/\/$/, '');
    }

    function updateWaitingBadge(count) {
      var badge = $('mx-waiting-badge');
      var countEl = $('mx-waiting-count');
      var n = Math.max(0, parseInt(count, 10) || 0);
      if (countEl) countEl.textContent = String(n);
      if (!badge) return;
      if (n > 0) {
        badge.textContent = n > 99 ? '99+' : String(n);
        badge.classList.remove('hidden');
      } else {
        badge.classList.add('hidden');
      }
    }

    function renderWaitingGuests(guests) {
      var list = $('mx-waiting-room-list');
      var empty = $('mx-waiting-room-empty');
      if (!list) return;
      list.innerHTML = '';
      var items = Array.isArray(guests) ? guests : [];
      if (empty) empty.classList.toggle('hidden', items.length > 0);
      items.forEach(function (guest) {
        var row = document.createElement('div');
        row.className = 'mx-waiting-guest-row';
        var name = document.createElement('span');
        name.className = 'mx-waiting-guest-name';
        name.textContent = guest.display_name || 'ضيف';
        var actions = document.createElement('div');
        actions.className = 'mx-waiting-guest-actions';
        var admit = document.createElement('button');
        admit.type = 'button';
        admit.className = 'mx-waiting-btn is-admit';
        admit.textContent = 'قبول';
        admit.addEventListener('click', function () {
          actOnWaitingGuest(guest.id, 'admit');
        });
        var deny = document.createElement('button');
        deny.type = 'button';
        deny.className = 'mx-waiting-btn is-deny';
        deny.textContent = 'رفض';
        deny.addEventListener('click', function () {
          actOnWaitingGuest(guest.id, 'deny');
        });
        actions.appendChild(admit);
        actions.appendChild(deny);
        row.appendChild(name);
        row.appendChild(actions);
        list.appendChild(row);
      });
    }

    function actOnWaitingGuest(guestId, action) {
      var base = waitingRoomBaseUrl();
      if (!base || !guestId) return;
      var url = base + '/' + guestId + '/' + (action === 'admit' ? 'admit' : 'deny');
      fetch(url, {
        method: 'POST',
        headers: {
          Accept: 'application/json',
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': config.csrfToken || '',
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({}),
      })
        .then(function (r) {
          return r.json().then(function (d) {
            return { ok: r.ok, data: d };
          });
        })
        .then(function (res) {
          if (!res.ok) throw new Error((res.data && res.data.message) || 'فشل تنفيذ الإجراء');
          updateWaitingBadge(res.data.pending_count);
          return pollWaitingRoom();
        })
        .catch(function (e) {
          toast(e.message || 'تعذر تحديث غرفة الانتظار');
        });
    }

    function pollWaitingRoom() {
      var url = config.waitingRoomListUrl;
      if (!url || !isHost) return Promise.resolve();
      return fetch(url, {
        headers: {
          Accept: 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
      })
        .then(function (r) {
          return r.json();
        })
        .then(function (data) {
          if (!data.ok) return;
          var enabled = !!data.waiting_room_enabled;
          var section = $('mx-waiting-room-section');
          if (section) section.classList.toggle('hidden', !enabled);
          var toggle = document.querySelector('[data-perm-key="waiting_room_enabled"]');
          if (toggle) toggle.checked = enabled;
          updateWaitingBadge(data.pending_count);
          renderWaitingGuests(data.guests);
        })
        .catch(function () {});
    }

    function startWaitingRoomPoll() {
      if (waitingRoomPollTimer) return;
      pollWaitingRoom();
      waitingRoomPollTimer = setInterval(pollWaitingRoom, 3000);
    }

    function stopWaitingRoomPoll() {
      if (waitingRoomPollTimer) {
        clearInterval(waitingRoomPollTimer);
        waitingRoomPollTimer = null;
      }
    }

    function cleanMicConstraints() {
      if (window.MxClassroomNoiseIsolation && typeof window.MxClassroomNoiseIsolation.getCleanMicAudioConstraints === 'function') {
        return window.MxClassroomNoiseIsolation.getCleanMicAudioConstraints();
      }
      return {
        echoCancellation: true,
        noiseSuppression: true,
        autoGainControl: true,
        channelCount: { ideal: 1 },
      };
    }

    async function ensureKrisp() {
      if (krispProcessor) return krispProcessor;
      try {
        var mod = await import('https://cdn.jsdelivr.net/npm/@livekit/krisp-noise-filter@0.3.4/+esm');
        var supported = typeof mod.isKrispNoiseFilterSupported === 'function'
          ? mod.isKrispNoiseFilterSupported()
          : true;
        if (!supported) return null;
        krispProcessor = typeof mod.KrispNoiseFilter === 'function' ? mod.KrispNoiseFilter() : null;
        krispReady = !!krispProcessor;
        return krispProcessor;
      } catch (e) {
        krispReady = false;
        return null;
      }
    }

    async function applyNoiseToLocalAudio(on) {
      noiseOn = !!on;
      refreshLocalTrackRefs();
      if (!localAudio) {
        paintNoise();
        return;
      }
      try {
        if (localAudio.mediaStreamTrack && localAudio.mediaStreamTrack.applyConstraints) {
          await localAudio.mediaStreamTrack.applyConstraints(on ? cleanMicConstraints() : {
            echoCancellation: true,
            noiseSuppression: false,
            autoGainControl: true,
          });
        }
      } catch (e) {}
      try {
        var proc = await ensureKrisp();
        if (proc && typeof localAudio.setProcessor === 'function') {
          if (on) {
            await localAudio.setProcessor(proc);
            if (typeof proc.setEnabled === 'function') await proc.setEnabled(true);
          } else if (typeof proc.setEnabled === 'function') {
            await proc.setEnabled(false);
          } else if (typeof localAudio.stopProcessor === 'function') {
            await localAudio.stopProcessor();
          }
        }
      } catch (e2) {
        // Krisp may require LiveKit Cloud — WebRTC NS still applied above
      }
      paintNoise();
    }

    function paintNoise() {
      var btn = $('mx-ml-btn-noise');
      if (!btn) return;
      btn.classList.toggle('is-active', noiseOn);
      btn.setAttribute('aria-pressed', noiseOn ? 'true' : 'false');
      btn.title = noiseOn
        ? (krispReady ? 'عزل الضوضاء Krisp: مفعّل' : 'عزل الضوضاء: مفعّل (WebRTC)')
        : 'عزل الضوضاء: متوقف';
      var icon = btn.querySelector('i');
      if (icon) icon.className = noiseOn ? 'fas fa-ear-listen text-[#0065fd]' : 'fas fa-ear-listen text-[#171717]';
      var sf = $('mx-sf-noise');
      if (sf) {
        sf.classList.toggle('is-active', noiseOn);
        sf.setAttribute('aria-pressed', noiseOn ? 'true' : 'false');
      }
    }

    function setLayoutMode(mode) {
      layoutMode = mode === 'speaker' ? 'speaker' : 'grid';
      if (stage) {
        stage.dataset.layout = layoutMode;
        stage.classList.toggle('lk-layout-speaker', layoutMode === 'speaker');
        stage.classList.toggle('lk-layout-grid', layoutMode === 'grid');
      }
      var tileBtn = $('mx-ml-btn-tile');
      if (tileBtn) {
        tileBtn.classList.toggle('is-active', layoutMode === 'grid');
        tileBtn.title = layoutMode === 'grid' ? 'عرض الشبكة (مفعّل) — اضغط للمتحدث' : 'عرض المتحدث — اضغط للشبكة';
      }
      applySpeakerLayout();
    }

    function applySpeakerLayout() {
      if (!stage || stage.classList.contains('has-screen')) return;
      var tiles = Array.from(stage.querySelectorAll('.lk-tile:not(.is-screen)'));
      tiles.forEach(function (t) {
        t.classList.remove('is-speaker-main', 'is-speaker-side');
      });
      if (layoutMode !== 'speaker' || tiles.length < 2) return;
      var main = null;
      if (activeSpeakerId) {
        main = tiles.find(function (t) {
          return (t.dataset.key || '').indexOf(activeSpeakerId + ':') === 0;
        });
      }
      if (!main) main = tiles[0];
      tiles.forEach(function (t) {
        if (t === main) t.classList.add('is-speaker-main');
        else t.classList.add('is-speaker-side');
      });
      if (main && stage.firstChild !== main) stage.insertBefore(main, stage.firstChild);
    }

    function showReactionBurst(emoji, name) {
      if (!stage) return;
      var el = document.createElement('div');
      el.className = 'lk-reaction-burst';
      el.innerHTML = '<span class="lk-reaction-emoji"></span><span class="lk-reaction-name"></span>';
      el.querySelector('.lk-reaction-emoji').textContent = emoji;
      el.querySelector('.lk-reaction-name').textContent = name || '';
      el.style.left = 12 + Math.random() * 70 + '%';
      el.style.bottom = 18 + Math.random() * 40 + '%';
      stage.appendChild(el);
      setTimeout(function () {
        el.remove();
      }, 2200);
    }

    async function sendReaction(emoji) {
      emoji = String(emoji || '').slice(0, 8);
      if (!emoji) return;
      showReactionBurst(emoji, room.localParticipant.name || 'أنت');
      await sendData('reaction', { emoji: emoji }, false);
    }

    function toggleFocusMode() {
      document.body.classList.toggle('mx-ml-focus');
      var on = document.body.classList.contains('mx-ml-focus');
      var btn = $('mx-ml-btn-focus');
      if (btn) btn.classList.toggle('is-active', on);
      toast(on ? 'وضع التركيز' : 'إظهار الأدوات');
    }

    function participantCameraTrack(participant) {
      if (!participant || typeof participant.getTrackPublication !== 'function') return null;
      var publication = participant.getTrackPublication(Track.Source.Camera);
      return publication && publication.track && publication.track.mediaStreamTrack
        ? publication.track.mediaStreamTrack
        : null;
    }

    function participantMicOn(participant) {
      if (!participant || typeof participant.getTrackPublication !== 'function') return false;
      var publication = participant.getTrackPublication(Track.Source.Microphone);
      return !!(publication && publication.track && !publication.isMuted && !publication.track.isMuted);
    }

    function participantFloatParticipants() {
      var all = [room.localParticipant].concat(Array.from(room.remoteParticipants.values()));
      if (participantFloatMode === 'speaker' && activeSpeakerId) {
        all.sort(function (a, b) {
          if (a.identity === activeSpeakerId) return -1;
          if (b.identity === activeSpeakerId) return 1;
          return 0;
        });
      }
      return all;
    }

    function participantFloatSignature() {
      return participantFloatParticipants()
        .map(function (p) {
          var cam = participantCameraTrack(p);
          return [
            p.identity,
            p.name || '',
            participantMicOn(p) ? '1' : '0',
            raisedHands.has(p.identity) ? '1' : '0',
            p.identity === activeSpeakerId ? '1' : '0',
            cam && cam.readyState === 'live' ? '1' : '0',
          ].join(':');
        })
        .join('|');
    }

    function isParticipantPipOpen() {
      if (participantPipSurface === 'document') {
        return !!(participantFloatWindow && !participantFloatWindow.closed);
      }
      if (participantPipSurface === 'canvas') {
        return !!(
          participantFloatCanvasVideo &&
          document.pictureInPictureElement === participantFloatCanvasVideo
        );
      }
      if (participantPipSurface === 'inline') {
        var root = $('mx-participant-float');
        return !!(root && root.classList.contains('is-open'));
      }
      return false;
    }

    function paintPipButtons() {
      var open = isParticipantPipOpen();
      participantPipOpen = open;
      ['mx-ml-btn-pip', 'mx-sf-people'].forEach(function (id) {
        var btn = $(id);
        if (!btn) return;
        btn.classList.toggle('is-active', open);
        btn.setAttribute('aria-pressed', open ? 'true' : 'false');
      });
    }

    function scheduleParticipantPipRefresh() {
      if (!isParticipantPipOpen() && !participantPipOpen) return;
      participantFloatCanvasDirty = true;
      if (participantPipRefreshPending) return;
      participantPipRefreshPending = true;
      requestAnimationFrame(function () {
        participantPipRefreshPending = false;
        if (!isParticipantPipOpen()) return;
        renderParticipantFloat();
        paintPipControlBar();
      });
    }

    function showPipInvite() {
      if (!shareOn) return;
      var el = $('mx-pip-invite');
      if (!el) {
        el = document.createElement('button');
        el.id = 'mx-pip-invite';
        el.type = 'button';
        el.className = 'mx-pip-invite';
        el.setAttribute('aria-label', 'فتح نافذة المشاركين العائمة');
        el.innerHTML = '<i class="fas fa-users" aria-hidden="true"></i><span>اضغط لفتح نافذة المشاركين</span>';
        el.addEventListener('click', function () {
          openParticipantPip({ manual: true }).catch(function (e) {
            toast(e.message || 'تعذر فتح النافذة');
          });
        });
        document.body.appendChild(el);
      }
      el.classList.add('is-visible');
    }

    function hidePipInvite() {
      var el = $('mx-pip-invite');
      if (el) el.classList.remove('is-visible');
    }

    function createParticipantFloatCard(doc, participant) {
      var card = doc.createElement('div');
      card.className = 'mx-pfloat-card';
      if (participant.identity === activeSpeakerId) card.classList.add('is-speaking');
      if (raisedHands.has(participant.identity)) card.classList.add('is-hand');
      var track = participantCameraTrack(participant);
      if (track && track.readyState === 'live') {
        var video = doc.createElement('video');
        video.autoplay = true;
        video.playsInline = true;
        video.muted = true;
        video.srcObject = new MediaStream([track]);
        video.play().catch(function () {});
        card.appendChild(video);
      } else {
        var avatar = doc.createElement('div');
        avatar.className = 'mx-pfloat-avatar';
        var displayName = participant.name || participant.identity || 'مشارك';
        avatar.textContent = displayName.trim().charAt(0).toUpperCase() || '؟';
        card.appendChild(avatar);
      }
      var footer = doc.createElement('div');
      footer.className = 'mx-pfloat-card-footer';
      var name = doc.createElement('span');
      name.className = 'mx-pfloat-name';
      name.textContent = (participant.name || participant.identity || 'مشارك') + (participant.isLocal ? ' · أنت' : '');
      footer.appendChild(name);
      var badges = doc.createElement('span');
      badges.className = 'mx-pfloat-badges';
      var mic = doc.createElement('i');
      mic.className = participantMicOn(participant)
        ? 'fas fa-microphone'
        : 'fas fa-microphone-slash is-muted';
      mic.setAttribute('aria-hidden', 'true');
      badges.appendChild(mic);
      if (raisedHands.has(participant.identity)) {
        var hand = doc.createElement('i');
        hand.className = 'fas fa-hand-paper mx-pfloat-hand';
        hand.setAttribute('aria-hidden', 'true');
        badges.appendChild(hand);
      }
      footer.appendChild(badges);
      card.appendChild(footer);
      return card;
    }

    function renderParticipantFloatInto(doc, root) {
      if (!doc || !root) return;
      var grid = root.querySelector('.mx-pfloat-grid');
      if (!grid) return;
      grid.innerHTML = '';
      var participants = participantFloatParticipants();
      grid.dataset.count = String(participants.length);
      grid.classList.toggle('is-speaker', participantFloatMode === 'speaker');
      participants.forEach(function (participant) {
        grid.appendChild(createParticipantFloatCard(doc, participant));
      });
      var count = root.querySelector('.mx-pfloat-count');
      if (count) count.textContent = String(participants.length);
      var modeIcon = root.querySelector('[data-pfloat-action="layout"] i, [data-pfloat-control="layout"] i');
      if (modeIcon) {
        modeIcon.className = participantFloatMode === 'grid' ? 'fas fa-grip' : 'fas fa-user-large';
      }
      participantFloatCanvasDirty = true;
    }

    function participantFloatMarkup(options) {
      options = options || {};
      var withControls = !!options.withControls;
      var hidePopout = !!options.hidePopout;
      var toolbar = withControls
        ? '<div class="mx-pfloat-toolbar">' +
          '<button type="button" data-pfloat-control="mic" title="ميكروفون" aria-label="ميكروفون"><i class="fas fa-microphone"></i></button>' +
          '<button type="button" data-pfloat-control="cam" title="كاميرا" aria-label="كاميرا"><i class="fas fa-video"></i></button>' +
          '<button type="button" data-pfloat-control="stop-share" class="is-danger" title="إيقاف الشير" aria-label="إيقاف الشير"><i class="fas fa-desktop"></i></button>' +
          '<button type="button" data-pfloat-control="layout" title="شبكة / متحدث" aria-label="تبديل العرض"><i class="fas fa-grip"></i></button>' +
          '</div>'
        : '';
      var popoutBtn = hidePopout
        ? ''
        : '<button type="button" data-pfloat-action="popout" title="نافذة خارج الصفحة" aria-label="نافذة خارج الصفحة"><i class="fas fa-up-right-from-square"></i></button>';
      return (
        '<div class="mx-pfloat-head">' +
        '<div class="mx-pfloat-title"><span class="mx-pfloat-live"></span><strong>المشاركون</strong><span class="mx-pfloat-count">0</span></div>' +
        '<div class="mx-pfloat-actions">' +
        '<button type="button" data-pfloat-action="layout" title="شبكة / متحدث" aria-label="تبديل العرض"><i class="fas fa-grip"></i></button>' +
        popoutBtn +
        '<button type="button" data-pfloat-action="minimize" title="تصغير" aria-label="تصغير"><i class="fas fa-minus"></i></button>' +
        '<button type="button" data-pfloat-action="close" title="إغلاق" aria-label="إغلاق"><i class="fas fa-xmark"></i></button>' +
        '</div></div>' +
        toolbar +
        '<div class="mx-pfloat-grid"></div>'
      );
    }

    function paintPipControlBar() {
      function update(doc, root) {
        if (!root) return;
        var micBtn = root.querySelector('[data-pfloat-control="mic"] i');
        if (micBtn) {
          micBtn.className = micOn ? 'fas fa-microphone' : 'fas fa-microphone-slash is-muted';
        }
        var camBtn = root.querySelector('[data-pfloat-control="cam"] i');
        if (camBtn) {
          camBtn.className = camOn ? 'fas fa-video' : 'fas fa-video-slash is-muted';
        }
        var shareBtn = root.querySelector('[data-pfloat-control="stop-share"]');
        if (shareBtn) shareBtn.classList.toggle('hidden', !shareOn);
      }
      update(document, $('mx-participant-float'));
      if (participantFloatWindow && !participantFloatWindow.closed) {
        update(
          participantFloatWindow.document,
          participantFloatWindow.document.getElementById('mx-participant-popout')
        );
      }
    }

    function wireParticipantFloatControls(root) {
      if (!root || root.dataset.controlsReady === '1') return;
      root.dataset.controlsReady = '1';
      root.querySelectorAll('[data-pfloat-control]').forEach(function (button) {
        button.addEventListener('click', function (ev) {
          ev.preventDefault();
          ev.stopPropagation();
          var action = button.getAttribute('data-pfloat-control');
          if (action === 'mic') toggleMic().catch(function () {});
          else if (action === 'cam') toggleCam().catch(function () {});
          else if (action === 'stop-share') stopScreenShare().catch(function () {});
          else if (action === 'layout') {
            participantFloatMode = participantFloatMode === 'grid' ? 'speaker' : 'grid';
            renderParticipantFloat();
          }
          scheduleParticipantPipRefresh();
        });
      });
    }

    function wireParticipantFloatActions(root, isPopout) {
      if (!root) return;
      wireParticipantFloatControls(root);
      root.querySelectorAll('[data-pfloat-action]').forEach(function (button) {
        button.addEventListener('click', function (ev) {
          ev.preventDefault();
          ev.stopPropagation();
          var action = button.getAttribute('data-pfloat-action');
          if (action === 'layout') {
            participantFloatMode = participantFloatMode === 'grid' ? 'speaker' : 'grid';
            renderParticipantFloat();
          } else if (action === 'popout') {
            openParticipantPip({ manual: true, preferDocument: true }).catch(function (e) {
              toast(e.message || 'تعذر فتح النافذة الخارجية');
            });
          } else if (action === 'minimize') {
            root.classList.toggle('is-minimized');
            var icon = button.querySelector('i');
            if (icon) icon.className = root.classList.contains('is-minimized') ? 'fas fa-expand' : 'fas fa-minus';
          } else if (action === 'close') {
            closeParticipantPip();
          }
        });
      });
    }

    function makeParticipantFloatDraggable(root) {
      var head = root && root.querySelector('.mx-pfloat-head');
      if (!root || !head || root.dataset.dragReady === '1') return;
      root.dataset.dragReady = '1';
      head.addEventListener('pointerdown', function (ev) {
        if (ev.target.closest('button')) return;
        var rect = root.getBoundingClientRect();
        var startX = ev.clientX;
        var startY = ev.clientY;
        var left = rect.left;
        var top = rect.top;
        root.style.right = 'auto';
        root.style.bottom = 'auto';
        root.setPointerCapture(ev.pointerId);
        function move(moveEv) {
          var nextLeft = Math.max(6, Math.min(window.innerWidth - root.offsetWidth - 6, left + moveEv.clientX - startX));
          var nextTop = Math.max(6, Math.min(window.innerHeight - root.offsetHeight - 6, top + moveEv.clientY - startY));
          root.style.left = nextLeft + 'px';
          root.style.top = nextTop + 'px';
        }
        function up() {
          root.removeEventListener('pointermove', move);
          root.removeEventListener('pointerup', up);
          root.removeEventListener('pointercancel', up);
        }
        root.addEventListener('pointermove', move);
        root.addEventListener('pointerup', up);
        root.addEventListener('pointercancel', up);
      });
    }

    function ensureParticipantFloat() {
      var root = $('mx-participant-float');
      if (root) return root;
      root = document.createElement('section');
      root.id = 'mx-participant-float';
      root.setAttribute('dir', 'rtl');
      root.setAttribute('aria-label', 'نافذة المشاركين العائمة');
      root.innerHTML = participantFloatMarkup({ withControls: true });
      document.body.appendChild(root);
      wireParticipantFloatActions(root, false);
      makeParticipantFloatDraggable(root);
      return root;
    }

    function renderParticipantFloat() {
      var root = $('mx-participant-float');
      if (root) renderParticipantFloatInto(document, root);
      if (participantFloatWindow && !participantFloatWindow.closed) {
        var pipRoot = participantFloatWindow.document.getElementById('mx-participant-popout');
        if (pipRoot) renderParticipantFloatInto(participantFloatWindow.document, pipRoot);
      }
      paintPipControlBar();
    }

    function openParticipantFloat() {
      var root = ensureParticipantFloat();
      root.classList.add('is-open');
      participantPipSurface = 'inline';
      renderParticipantFloat();
      paintPipButtons();
    }

    function closeParticipantFloatInline() {
      var root = $('mx-participant-float');
      if (root) root.classList.remove('is-open');
      if (participantPipSurface === 'inline') participantPipSurface = 'none';
    }

    function closeParticipantPip(opts) {
      opts = opts || {};
      if (opts.onlyAuto && participantPipManual) return;
      closeParticipantFloatInline();
      if (participantFloatWindow && !participantFloatWindow.closed) {
        try {
          participantFloatWindow.close();
        } catch (e) {}
      }
      participantFloatWindow = null;
      stopCanvasPictureInPicture();
      participantPipOpen = false;
      participantPipManual = false;
      participantPipAutoOpened = false;
      participantPipSurface = 'none';
      hidePipInvite();
      paintPipButtons();
    }

    function participantPopoutCss() {
      return (
        'html,body{margin:0;width:100%;height:100%;overflow:hidden;background:#07101f;color:#fff;font-family:Arial,sans-serif}' +
        '#mx-participant-popout{display:flex;flex-direction:column;width:100%;height:100%}' +
        '.mx-pfloat-head{height:42px;display:flex;align-items:center;justify-content:space-between;padding:0 10px;background:#0f1c30;border-bottom:1px solid rgba(255,255,255,.1);flex-shrink:0}' +
        '.mx-pfloat-title,.mx-pfloat-actions{display:flex;align-items:center;gap:7px}.mx-pfloat-title{font-size:12px}.mx-pfloat-count{background:#263750;padding:2px 7px;border-radius:10px}' +
        '.mx-pfloat-live{width:8px;height:8px;border-radius:50%;background:#22c55e;box-shadow:0 0 0 4px rgba(34,197,94,.12)}' +
        '.mx-pfloat-actions button{width:29px;height:29px;border:0;border-radius:8px;background:rgba(255,255,255,.08);color:#fff;cursor:pointer}' +
        '.mx-pfloat-toolbar{display:flex;align-items:center;justify-content:center;gap:6px;padding:6px 8px;background:#0a1528;border-bottom:1px solid rgba(255,255,255,.08);flex-shrink:0}' +
        '.mx-pfloat-toolbar button{width:34px;height:34px;border:0;border-radius:9px;background:rgba(255,255,255,.1);color:#fff;cursor:pointer}' +
        '.mx-pfloat-toolbar button.is-danger{background:rgba(253,0,0,.22);color:#fca5a5}' +
        '.mx-pfloat-toolbar button.hidden{display:none}' +
        '.mx-pfloat-grid{flex:1;min-height:0;display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:5px;padding:6px;overflow:auto}' +
        '.mx-pfloat-grid[data-count="1"]{grid-template-columns:1fr}' +
        '.mx-pfloat-grid[data-count="3"],.mx-pfloat-grid[data-count="5"],.mx-pfloat-grid[data-count="6"]{grid-template-columns:repeat(3,minmax(0,1fr))}' +
        '.mx-pfloat-grid.is-speaker .mx-pfloat-card:first-child{grid-column:1/-1;min-height:55%}' +
        '.mx-pfloat-card{position:relative;min-height:80px;overflow:hidden;border-radius:10px;background:#14243b;border:1px solid rgba(255,255,255,.08)}' +
        '.mx-pfloat-card.is-speaking{border-color:#22c55e;box-shadow:inset 0 0 0 2px #22c55e}' +
        '.mx-pfloat-card.is-hand .mx-pfloat-hand{color:#fbbf24}' +
        '.mx-pfloat-card video{width:100%;height:100%;object-fit:cover;transform:scaleX(-1)}' +
        '.mx-pfloat-avatar{position:absolute;inset:0;display:grid;place-items:center;font-size:28px;font-weight:800;color:#bfdbfe;background:linear-gradient(145deg,#1e3a5f,#172554)}' +
        '.mx-pfloat-card-footer{position:absolute;inset:auto 0 0;display:flex;justify-content:space-between;align-items:center;gap:5px;padding:18px 7px 6px;background:linear-gradient(transparent,rgba(2,6,23,.88));font-size:10px}' +
        '.mx-pfloat-name{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;flex:1;min-width:0}' +
        '.mx-pfloat-badges{display:flex;align-items:center;gap:4px;flex-shrink:0}' +
        '.is-muted{color:#f87171}.mx-pfloat-hand{color:#fbbf24}' +
        '.is-minimized .mx-pfloat-grid,.is-minimized .mx-pfloat-toolbar{display:none}.is-minimized{height:42px!important}'
      );
    }

    async function openParticipantDocumentPip() {
      if (participantFloatWindow && !participantFloatWindow.closed) {
        participantFloatWindow.focus();
        participantPipSurface = 'document';
        return;
      }
      participantFloatWindow = await window.documentPictureInPicture.requestWindow({ width: 460, height: 360 });
      var doc = participantFloatWindow.document;
      doc.title = 'مشاركو Muallimx';
      var style = doc.createElement('style');
      style.textContent = participantPopoutCss();
      doc.head.appendChild(style);
      var root = doc.createElement('section');
      root.id = 'mx-participant-popout';
      root.setAttribute('dir', 'rtl');
      root.innerHTML = participantFloatMarkup({ withControls: true, hidePopout: true });
      doc.body.appendChild(root);
      wireParticipantFloatActions(root, true);
      participantFloatWindow.addEventListener('pagehide', function () {
        participantFloatWindow = null;
        if (participantPipSurface === 'document') {
          participantPipSurface = 'none';
          participantPipOpen = false;
          if (!participantPipManual) participantPipAutoOpened = false;
          paintPipButtons();
        }
      });
      renderParticipantFloatInto(doc, root);
      paintPipControlBar();
      participantPipSurface = 'document';
    }

    async function openParticipantPip(opts) {
      opts = opts || {};
      var manual = opts.manual !== false;
      if (isParticipantPipOpen()) {
        paintPipButtons();
        return true;
      }
      if (
        opts.preferDocument !== false &&
        window.documentPictureInPicture &&
        typeof window.documentPictureInPicture.requestWindow === 'function'
      ) {
        try {
          await openParticipantDocumentPip();
          participantPipOpen = true;
          participantPipManual = manual;
          participantPipAutoOpened = !manual;
          hidePipInvite();
          paintPipButtons();
          return true;
        } catch (e) {
          if (manual) {
            /* fall through */
          }
        }
      }
      if (document.pictureInPictureEnabled) {
        try {
          await openCanvasPictureInPicture();
          participantPipOpen = true;
          participantPipManual = manual;
          participantPipAutoOpened = !manual;
          hidePipInvite();
          paintPipButtons();
          return true;
        } catch (e) {
          if (manual) {
            /* fall through */
          }
        }
      }
      openParticipantFloat();
      participantPipOpen = true;
      participantPipManual = manual;
      participantPipAutoOpened = !manual;
      if (!manual && shareOn) showPipInvite();
      paintPipButtons();
      return true;
    }

    async function toggleParticipantPip() {
      if (isParticipantPipOpen()) {
        participantPipManual = false;
        closeParticipantPip();
        return;
      }
      await openParticipantPip({ manual: true });
    }

    function drawParticipantCanvas(canvas, ctx) {
      if (!canvas || !ctx) return;
      var participants = participantFloatParticipants();
      var count = Math.max(1, participants.length);
      var cols = count === 1 ? 1 : count <= 4 ? 2 : 3;
      var rows = Math.ceil(count / cols);
      var gap = 8;
      var width = (canvas.width - gap * (cols + 1)) / cols;
      var height = (canvas.height - gap * (rows + 1)) / rows;
      ctx.fillStyle = '#07101f';
      ctx.fillRect(0, 0, canvas.width, canvas.height);
      participants.forEach(function (participant, index) {
        var col = index % cols;
        var row = Math.floor(index / cols);
        var x = gap + col * (width + gap);
        var y = gap + row * (height + gap);
        ctx.fillStyle = '#14243b';
        ctx.fillRect(x, y, width, height);
        var key = tileKey(participant, Track.Source.Camera);
        var tile = tileMap.get(key);
        var video = tile && tile.querySelector('video');
        if (video && video.readyState >= 2 && video.videoWidth > 0) {
          ctx.save();
          ctx.translate(x + width, y);
          ctx.scale(-1, 1);
          ctx.drawImage(video, 0, 0, width, height);
          ctx.restore();
        } else {
          ctx.fillStyle = '#bfdbfe';
          ctx.font = 'bold 44px Arial';
          ctx.textAlign = 'center';
          ctx.textBaseline = 'middle';
          var n = participant.name || participant.identity || '؟';
          ctx.fillText(n.charAt(0).toUpperCase(), x + width / 2, y + height / 2);
        }
        if (participant.identity === activeSpeakerId) {
          ctx.strokeStyle = '#22c55e';
          ctx.lineWidth = 4;
          ctx.strokeRect(x + 2, y + 2, width - 4, height - 4);
        }
        ctx.fillStyle = 'rgba(2,6,23,.82)';
        ctx.fillRect(x, y + height - 30, width, 30);
        ctx.fillStyle = '#fff';
        ctx.font = '14px Arial';
        ctx.textAlign = 'right';
        ctx.textBaseline = 'middle';
        var label = (participant.name || participant.identity || 'مشارك').slice(0, 22);
        if (raisedHands.has(participant.identity)) label += ' ✋';
        ctx.fillText(label, x + width - 8, y + height - 15);
        if (!participantMicOn(participant)) {
          ctx.fillStyle = '#f87171';
          ctx.font = '12px Arial';
          ctx.textAlign = 'left';
          ctx.fillText('🔇', x + 8, y + height - 15);
        }
      });
    }

    async function openCanvasPictureInPicture() {
      if (!document.pictureInPictureEnabled) {
        throw new Error('المتصفح لا يدعم النافذة الخارجية؛ استخدم Chrome أو Edge حديثًا.');
      }
      if (participantFloatCanvasVideo && document.pictureInPictureElement === participantFloatCanvasVideo) {
        participantPipSurface = 'canvas';
        return;
      }
      var canvas = participantFloatCanvasEl;
      if (!canvas) {
        canvas = document.createElement('canvas');
        canvas.width = 640;
        canvas.height = 360;
        canvas.style.display = 'none';
        document.body.appendChild(canvas);
        participantFloatCanvasEl = canvas;
      }
      var ctx = canvas.getContext('2d');
      participantFloatCanvasVideo = document.createElement('video');
      participantFloatCanvasVideo.muted = true;
      participantFloatCanvasVideo.playsInline = true;
      participantFloatCanvasVideo.srcObject = canvas.captureStream(10);
      participantFloatCanvasVideo.style.display = 'none';
      document.body.appendChild(participantFloatCanvasVideo);
      await participantFloatCanvasVideo.play();
      participantFloatCanvasLastSig = '';
      participantFloatCanvasDirty = true;
      drawParticipantCanvas(canvas, ctx);
      participantFloatCanvasTimer = setInterval(function () {
        var sig = participantFloatSignature();
        if (!participantFloatCanvasDirty && sig === participantFloatCanvasLastSig) return;
        participantFloatCanvasLastSig = sig;
        participantFloatCanvasDirty = false;
        drawParticipantCanvas(canvas, ctx);
      }, 120);
      participantFloatCanvasVideo.addEventListener(
        'leavepictureinpicture',
        function () {
          if (participantPipSurface === 'canvas') {
            participantPipSurface = 'none';
            participantPipOpen = false;
            paintPipButtons();
          }
          stopCanvasPictureInPicture();
        },
        { once: true }
      );
      await participantFloatCanvasVideo.requestPictureInPicture();
      participantPipSurface = 'canvas';
    }

    function stopCanvasPictureInPicture() {
      if (participantFloatCanvasTimer) {
        clearInterval(participantFloatCanvasTimer);
        participantFloatCanvasTimer = null;
      }
      if (participantFloatCanvasVideo) {
        if (document.pictureInPictureElement === participantFloatCanvasVideo) {
          document.exitPictureInPicture().catch(function () {});
        }
        var stream = participantFloatCanvasVideo.srcObject;
        if (stream && typeof stream.getTracks === 'function') {
          stream.getTracks().forEach(function (track) {
            track.stop();
          });
        }
        participantFloatCanvasVideo.remove();
        participantFloatCanvasVideo = null;
      }
      if (participantPipSurface === 'canvas') participantPipSurface = 'none';
    }

    function syncShareFloat() {
      var bar = $('mx-share-float');
      document.body.classList.toggle('mx-sharing', !!shareOn);
      if (bar) bar.classList.toggle('is-open', !!shareOn);
      if (shareOn) {
        openParticipantPip({ manual: false }).catch(function () {
          openParticipantFloat();
          showPipInvite();
        });
      } else {
        closeParticipantPip({ onlyAuto: true });
        hidePipInvite();
      }
    }

    function ensureReactMenu() {
      var host = $('mx-ml-react-wrap');
      if (!host || host.querySelector('#mx-ml-react-menu')) return;
      var menu = document.createElement('div');
      menu.id = 'mx-ml-react-menu';
      menu.className = 'mx-lk-react-menu hidden';
      menu.setAttribute('role', 'menu');
      var handBtn = document.createElement('button');
      handBtn.type = 'button';
      handBtn.className = 'mx-lk-react-item';
      handBtn.innerHTML = '<i class="fas fa-hand-paper"></i><span>رفع اليد</span>';
      handBtn.addEventListener('click', function () {
        menu.classList.add('hidden');
        toggleHand().catch(function () {});
      });
      menu.appendChild(handBtn);
      REACTIONS.forEach(function (em) {
        var b = document.createElement('button');
        b.type = 'button';
        b.className = 'mx-lk-react-emoji';
        b.textContent = em;
        b.title = em;
        b.addEventListener('click', function () {
          menu.classList.add('hidden');
          sendReaction(em).catch(function () {});
        });
        menu.appendChild(b);
      });
      host.appendChild(menu);
    }

    function setStatus(t) {
      if (statusEl) statusEl.textContent = t;
    }

    function setQualityUI(q) {
      var level = qualityLevel(q, ConnectionQuality);
      if (qualityEl) qualityEl.setAttribute('data-level', String(level || 0));
      if (qualityLabelEl) qualityLabelEl.textContent = qualityLabel(q, ConnectionQuality);
    }

    function updateCount() {
      if (!countEl) return;
      var n = 1 + (room.remoteParticipants ? room.remoteParticipants.size : 0);
      countEl.textContent = String(n);
    }

    function tileKey(participant, source) {
      return participant.identity + ':' + source;
    }

    function getFilmstrip() {
      if (!stage) return null;
      var strip = stage.querySelector('.lk-filmstrip');
      if (!strip) {
        strip = document.createElement('div');
        strip.className = 'lk-filmstrip';
        stage.appendChild(strip);
      }
      return strip;
    }

    function layoutStage() {
      if (!stage) return;
      var hasScreen = !!stage.querySelector('.lk-tile.is-screen');
      stage.classList.toggle('has-screen', hasScreen);
      var strip = stage.querySelector('.lk-filmstrip');
      if (!hasScreen) {
        if (strip) {
          Array.from(strip.querySelectorAll('.lk-tile')).forEach(function (t) {
            stage.appendChild(t);
          });
          strip.remove();
        }
        syncShareTools(false);
        return;
      }
      strip = getFilmstrip();
      Array.from(stage.querySelectorAll('.lk-tile:not(.is-screen)')).forEach(function (t) {
        if (t.parentElement !== strip) strip.appendChild(t);
      });
      var screenTile = stage.querySelector('.lk-tile.is-screen');
      if (screenTile && stage.firstChild !== screenTile) {
        stage.insertBefore(screenTile, stage.firstChild);
      }
      if (strip && strip.parentElement === stage) {
        stage.appendChild(strip);
      }
      syncShareTools(true);
      applySpeakerLayout();
    }

    function syncShareTools(on) {
      var layer = document.getElementById('mx-share-ann-layer');
      var hold = document.getElementById('mx-share-ann-hold');
      var screenTile = stage && stage.querySelector('.lk-tile.is-screen');
      if (layer) {
        if (on && screenTile) {
          screenTile.appendChild(layer);
          if (typeof window.__mxShareAnnSetAllowed === 'function') {
            window.__mxShareAnnSetAllowed(isHost || !!perms.allow_participant_whiteboard);
          }
          if (isHost) {
            layer.classList.remove('hidden');
          }
          if (typeof window.__mxShareAnnRemounted === 'function') {
            window.__mxShareAnnRemounted();
          }
          try {
            window.dispatchEvent(new Event('resize'));
          } catch (e) {}
        } else {
          if (hold) hold.appendChild(layer);
          if (typeof window.__mxShareAnnSetAllowed === 'function') {
            window.__mxShareAnnSetAllowed(false);
          }
        }
      }
      ensurePointerLayer(on ? screenTile : null);
      var drawBtn = $('mx-ml-btn-annotate');
      if (drawBtn) {
        drawBtn.disabled = !(on && (isHost || perms.allow_participant_whiteboard));
        drawBtn.classList.toggle('is-active', false);
      }
      var laserBtn = $('mx-ml-btn-laser');
      if (laserBtn) {
        laserBtn.disabled = !on;
        if (!on) {
          laserBtn.classList.remove('is-active');
          document.body.classList.remove('lk-laser-on');
          laserOn = false;
        }
      }
    }

    var laserOn = false;
    var pointerDots = new Map();
    var pointerLayer = null;
    var lastPointerSent = 0;

    function ensurePointerLayer(screenTile) {
      if (!screenTile) {
        if (pointerLayer && pointerLayer.parentElement) pointerLayer.remove();
        pointerLayer = null;
        pointerDots.clear();
        return;
      }
      pointerLayer = screenTile.querySelector('#lk-pointer-layer');
      if (!pointerLayer) {
        pointerLayer = document.createElement('div');
        pointerLayer.id = 'lk-pointer-layer';
        screenTile.appendChild(pointerLayer);
        pointerLayer.addEventListener('pointermove', onLaserMove);
        pointerLayer.addEventListener('pointerdown', onLaserMove);
        pointerLayer.addEventListener('pointerleave', function () {
          if (!laserOn) return;
          sendData('pointer', { on: false }, false).catch(function () {});
        });
      }
    }

    function onLaserMove(ev) {
      if (!laserOn || !pointerLayer) return;
      var now = Date.now();
      if (now - lastPointerSent < 40) return;
      lastPointerSent = now;
      var rect = pointerLayer.getBoundingClientRect();
      if (rect.width < 1 || rect.height < 1) return;
      var x = (ev.clientX - rect.left) / rect.width;
      var y = (ev.clientY - rect.top) / rect.height;
      x = Math.max(0, Math.min(1, x));
      y = Math.max(0, Math.min(1, y));
      showPointer(room.localParticipant.identity, room.localParticipant.name || 'أنت', x, y, true);
      sendData('pointer', { on: true, x: x, y: y }, false).catch(function () {});
    }

    function showPointer(identity, name, x, y, on) {
      if (!pointerLayer) return;
      var key = String(identity || '');
      var el = pointerDots.get(key);
      if (!on) {
        if (el) {
          el.remove();
          pointerDots.delete(key);
        }
        return;
      }
      if (!el) {
        el = document.createElement('div');
        el.className = 'lk-laser-dot';
        el.innerHTML = '<span class="lk-laser-name"></span>';
        pointerLayer.appendChild(el);
        pointerDots.set(key, el);
      }
      el.style.left = (x * 100) + '%';
      el.style.top = (y * 100) + '%';
      var nameEl = el.querySelector('.lk-laser-name');
      if (nameEl) nameEl.textContent = name || '';
    }

    function ensureTile(participant, source) {
      var key = tileKey(participant, source);
      var tile = tileMap.get(key);
      if (tile) return tile;
      tile = document.createElement('div');
      tile.className = 'lk-tile' + (source === Track.Source.ScreenShare ? ' is-screen' : '');
      tile.dataset.key = key;
      var video = document.createElement('video');
      video.autoplay = true;
      video.playsInline = true;
      video.muted = participant.isLocal;
      var label = document.createElement('div');
      label.className = 'label';
      label.textContent =
        (participant.name || participant.identity) +
        (source === Track.Source.ScreenShare ? ' · شاشة' : '') +
        (raisedHands.has(participant.identity) ? ' ✋' : '');
      tile.appendChild(video);
      tile.appendChild(label);
      if (source === Track.Source.ScreenShare) {
        if (stage.firstChild) stage.insertBefore(tile, stage.firstChild);
        else stage.appendChild(tile);
      } else if (stage.classList.contains('has-screen') || stage.querySelector('.lk-tile.is-screen')) {
        getFilmstrip().appendChild(tile);
      } else {
        stage.appendChild(tile);
      }
      tileMap.set(key, tile);
      layoutStage();
      return tile;
    }

    function removeTile(participant, source) {
      var key = tileKey(participant, source);
      var tile = tileMap.get(key);
      if (!tile) return;
      tile.remove();
      tileMap.delete(key);
      layoutStage();
    }

    function refreshHandLabels() {
      tileMap.forEach(function (tile, key) {
        var identity = key.split(':')[0];
        var label = tile.querySelector('.label');
        if (!label) return;
        var base = label.textContent.replace(/\s*✋\s*$/, '');
        label.textContent = raisedHands.has(identity) ? base + ' ✋' : base;
      });
      renderPeople();
      scheduleParticipantPipRefresh();
    }

    function attachTrack(track, participant) {
      if (track.kind === Track.Kind.Audio) {
        if (participant.isLocal) return;
        var el = track.attach();
        el.style.display = 'none';
        document.body.appendChild(el);
        return;
      }
      if (track.kind !== Track.Kind.Video) return;
      if (track.source === Track.Source.ScreenShare) {
        try {
          if (track.mediaStreamTrack) track.mediaStreamTrack.contentHint = 'detail';
        } catch (e) {}
      }
      var tile = ensureTile(participant, track.source);
      track.attach(tile.querySelector('video'));
    }

    function detachTrack(track, participant) {
      track.detach().forEach(function (el) {
        el.remove();
      });
      if (track.kind === Track.Kind.Video) removeTile(participant, track.source);
    }

    function sendData(type, payload, reliable) {
      var bytes = new TextEncoder().encode(encodeMsg(type, payload));
      var kind = reliable === false ? DataPacket_Kind.LOSSY : DataPacket_Kind.RELIABLE;
      return room.localParticipant.publishData(bytes, { reliable: reliable !== false, kind: kind });
    }

    function appendChat(name, text, self) {
      if (!chatLog) return;
      var row = document.createElement('div');
      row.className = 'mx-lk-chat-row' + (self ? ' is-self' : '');
      row.innerHTML =
        '<strong></strong><span></span>';
      row.querySelector('strong').textContent = name + ':';
      row.querySelector('span').textContent = ' ' + text;
      chatLog.appendChild(row);
      chatLog.scrollTop = chatLog.scrollHeight;
    }

    function renderPeople() {
      if (!peopleList) return;
      peopleList.innerHTML = '';
      var all = [room.localParticipant].concat(Array.from(room.remoteParticipants.values()));
      all.forEach(function (p) {
        if (!p) return;
        var row = document.createElement('div');
        row.className = 'mx-lk-person';
        var name = document.createElement('div');
        name.className = 'mx-lk-person-name';
        name.textContent =
          (p.name || p.identity) +
          (p.isLocal ? ' (أنت)' : '') +
          (raisedHands.has(p.identity) ? ' ✋' : '');
        row.appendChild(name);
        if (isHost && !p.isLocal) {
          var actions = document.createElement('div');
          actions.className = 'mx-lk-person-actions';
          var muteA = document.createElement('button');
          muteA.type = 'button';
          muteA.textContent = 'كتم صوت';
          muteA.onclick = function () {
            muteRemote(p, Track.Source.Microphone, true);
          };
          var muteV = document.createElement('button');
          muteV.type = 'button';
          muteV.textContent = 'كتم فيديو';
          muteV.onclick = function () {
            muteRemote(p, Track.Source.Camera, true);
          };
          var kick = document.createElement('button');
          kick.type = 'button';
          kick.className = 'is-danger';
          kick.textContent = 'إزالة';
          kick.onclick = function () {
            removeParticipant(p);
          };
          actions.appendChild(muteA);
          actions.appendChild(muteV);
          actions.appendChild(kick);
          row.appendChild(actions);
        }
        peopleList.appendChild(row);
      });
      updateCount();
    }

    async function muteRemote(participant, source, muted) {
      try {
        var pub = participant.getTrackPublication(source);
        if (pub && typeof room.localParticipant.setTrackSubscriptionPermissions === 'function') {
          /* fall through */
        }
        if (typeof participant.setTrackEnabled === 'function') {
          /* not available on remote */
        }
        // LiveKit admin: mute published track via room service isn't in browser —
        // send data command; also try setRemoteTrackPublicationSubscription / mutePublishedTrack if available
        if (typeof room.localParticipant.mutePublishedTrack === 'function' && pub) {
          await room.localParticipant.mutePublishedTrack(pub.trackSid, muted);
        } else {
          await sendData('moderation', {
            action: muted ? 'mute' : 'unmute',
            identity: participant.identity,
            source: source,
          });
        }
        toast((muted ? 'تم كتم ' : 'تم إلغاء كتم ') + (participant.name || participant.identity));
      } catch (e) {
        await sendData('moderation', {
          action: muted ? 'mute' : 'unmute',
          identity: participant.identity,
          source: source,
        });
        toast('أُرسل طلب الكتم للمشارك');
      }
    }

    async function removeParticipant(participant) {
      try {
        if (typeof room.localParticipant.removeParticipant === 'function') {
          await room.localParticipant.removeParticipant(participant.identity);
        } else {
          await sendData('moderation', { action: 'kick', identity: participant.identity });
        }
        toast('تمت إزالة ' + (participant.name || participant.identity));
      } catch (e) {
        await sendData('moderation', { action: 'kick', identity: participant.identity });
        toast('أُرسل طلب الإزالة');
      }
    }

    function applyPermissions(next) {
      perms = Object.assign({}, perms, next || {});
      var shareBtn = $('mx-ml-btn-share');
      var chatBtn = $('mx-ml-btn-chat');
      var handBtn = $('mx-ml-btn-react');
      var wbBtn = $('btn-wb-popup-open') || $('btn-guest-whiteboard');
      var vbgBtn = $('mx-ml-btn-bg');
      var annotateBtn = $('mx-ml-btn-annotate');
      if (!isHost) {
        if (shareBtn) shareBtn.disabled = !perms.allow_participant_screen_share;
        if (chatBtn) chatBtn.disabled = !perms.allow_participant_chat;
        if (handBtn) handBtn.disabled = !perms.allow_participant_raise_hand;
        if (wbBtn) {
          wbBtn.classList.toggle('mx-guest-wb-writable', !!perms.allow_participant_whiteboard);
        }
        if (vbgBtn) vbgBtn.disabled = !perms.allow_participant_virtual_background;
        if (shareOn && !perms.allow_participant_screen_share) {
          stopScreenShare();
        }
      }
      if (annotateBtn) {
        var hasScreen = !!(stage && stage.querySelector('.lk-tile.is-screen'));
        annotateBtn.disabled = !(hasScreen && (isHost || perms.allow_participant_whiteboard));
      }
      if (typeof config.onPermissions === 'function') {
        try {
          config.onPermissions(perms);
        } catch (e) {}
      }
    }

    async function handleData(payload, participant) {
      var msg = decodeMsg(payload);
      if (!msg) return;
      var from = (participant && (participant.name || participant.identity)) || 'مشارك';
      var identity = participant ? participant.identity : '';

      if (msg.t === 'chat') {
        if (!isHost && !perms.allow_participant_chat && identity !== room.localParticipant.identity) return;
        appendChat(from, String(msg.p.text || ''), false);
        return;
      }
      if (msg.t === 'hand') {
        if (msg.p.raised) raisedHands.add(identity);
        else raisedHands.delete(identity);
        refreshHandLabels();
        if (msg.p.raised) toast(from + ' رفع يده');
        return;
      }
      if (msg.t === 'permissions') {
        if (!isHost) {
          applyPermissions(msg.p || {});
          toast('تم تحديث صلاحيات الجلسة');
        }
        return;
      }
      if (msg.t === 'meeting_ended') {
        toast('أنهى المعلم الاجتماع');
        try {
          room.disconnect();
        } catch (e) {}
        if (typeof config.onMeetingEnded === 'function') config.onMeetingEnded();
        else setTimeout(function () {
          location.href = config.exitUrl || '/';
        }, 800);
        return;
      }
      if (msg.t === 'moderation' && msg.p) {
        if (msg.p.identity && msg.p.identity !== room.localParticipant.identity) return;
        if (msg.p.action === 'kick') {
          toast('تمت إزالتك من الاجتماع');
          try {
            room.disconnect();
          } catch (e) {}
          if (typeof config.onKicked === 'function') config.onKicked();
          else location.reload();
          return;
        }
        if (msg.p.action === 'mute') {
          if (msg.p.source === Track.Source.Microphone || msg.p.source === 'microphone') {
            if (localAudio) {
              await localAudio.setEnabled(false);
              micOn = false;
              paintMic();
            }
          }
          if (msg.p.source === Track.Source.Camera || msg.p.source === 'camera') {
            if (localVideo) {
              await localVideo.setEnabled(false);
              camOn = false;
              paintCam();
            }
          }
          toast('قام المعلم بكتم جهازك');
        }
      }
      if (msg.t === 'pointer' && msg.p) {
        showPointer(
          identity,
          from,
          Number(msg.p.x) || 0,
          Number(msg.p.y) || 0,
          !!msg.p.on
        );
      }
      if (msg.t === 'reaction' && msg.p && msg.p.emoji) {
        showReactionBurst(String(msg.p.emoji), from);
      }
      if (msg.t === 'annotate' && msg.p) {
        if (typeof window.__mxShareAnnApplyRemoteLayer === 'function') {
          window.__mxShareAnnApplyRemoteLayer(identity || 'guest', msg.p.polylines || []);
        }
      }
      if (msg.t === 'annotate_clear') {
        if (typeof window.__mxShareAnnClearRemote === 'function') {
          window.__mxShareAnnClearRemote();
        }
        if (typeof window.__mxShareAnnForceLocalClear === 'function') {
          window.__mxShareAnnForceLocalClear();
        }
      }
      if (typeof msg.t === 'string' && msg.t.indexOf('curriculum_') === 0) {
        if (msg.t === 'curriculum_state_req' && isHost && curriculumActive && curriculumSnapshot) {
          sendData('curriculum_state', curriculumSnapshot).catch(function () {});
        }
        if (curriculumHandler) {
          try {
            curriculumHandler(msg, participant);
          } catch (e) {}
        }
      }
    }

    function paintMic() {
      var btn = $('mx-ml-btn-mic');
      var icon = $('mx-ml-mic-icon');
      if (!btn) return;
      btn.setAttribute('aria-pressed', micOn ? 'true' : 'false');
      btn.classList.toggle('is-active', micOn);
      if (icon) {
        icon.className = micOn ? 'fas fa-microphone text-[#0065fd]' : 'fas fa-microphone-slash text-[#fd0000]';
      }
      scheduleParticipantPipRefresh();
    }
    function paintCam() {
      var btn = $('mx-ml-btn-cam');
      var icon = $('mx-ml-cam-icon');
      if (!btn) return;
      btn.setAttribute('aria-pressed', camOn ? 'true' : 'false');
      btn.classList.toggle('is-active', camOn);
      if (icon) {
        icon.className = camOn ? 'fas fa-video text-[#0065fd]' : 'fas fa-video-slash text-[#fd0000]';
      }
      syncLocalMediaTile(Track.Source.Camera, camOn);
      scheduleParticipantPipRefresh();
    }
    function paintShare() {
      var btn = $('mx-ml-btn-share');
      var icon = $('mx-ml-share-icon');
      if (btn) {
        btn.setAttribute('aria-pressed', shareOn ? 'true' : 'false');
        btn.classList.toggle('is-active', shareOn);
      }
      if (icon) icon.className = shareOn ? 'fas fa-desktop text-[#0065fd]' : 'fas fa-desktop text-[#171717]';
      layoutStage();
      syncShareFloat();
      scheduleParticipantPipRefresh();
    }
    function paintHand() {
      var btn = $('mx-ml-btn-react');
      if (!btn) return;
      btn.classList.toggle('is-active', handRaised);
    }

    function syncLocalMediaTile(source, enabled) {
      try {
        var key = tileKey(room.localParticipant, source);
        var tile = tileMap.get(key);
        if (!tile) return;
        tile.classList.toggle('is-muted', !enabled);
        var video = tile.querySelector('video');
        if (video) {
          video.style.opacity = enabled ? '1' : '0';
        }
        if (!enabled && source === Track.Source.Camera) {
          // Soft-hide empty camera slot so layout stays balanced
          tile.classList.add('is-off');
        } else {
          tile.classList.remove('is-off');
        }
      } catch (e) {}
    }

    function refreshLocalTrackRefs() {
      try {
        var camPub = room.localParticipant.getTrackPublication(Track.Source.Camera);
        // Do not keep a stale track when the camera/mic is unpublished
        localVideo = (camPub && camPub.track) || null;
        var micPub = room.localParticipant.getTrackPublication(Track.Source.Microphone);
        localAudio = (micPub && micPub.track) || null;
        if (typeof room.localParticipant.isCameraEnabled === 'boolean') {
          camOn = !!room.localParticipant.isCameraEnabled;
        } else {
          camOn = !!(localVideo && !localVideo.isMuted);
        }
        if (typeof room.localParticipant.isMicrophoneEnabled === 'boolean') {
          micOn = !!room.localParticipant.isMicrophoneEnabled;
        } else {
          micOn = !!(localAudio && !localAudio.isMuted);
        }
        shareOn = !!room.localParticipant.isScreenShareEnabled;
      } catch (e) {}
    }

    async function toggleMic() {
      var next = !micOn;
      try {
        if (typeof room.localParticipant.setMicrophoneEnabled === 'function') {
          await room.localParticipant.setMicrophoneEnabled(next);
        } else if (!localAudio) {
          var tracks = await createLocalTracks({ audio: true, video: false });
          localAudio = tracks[0];
          await room.localParticipant.publishTrack(localAudio);
        } else {
          await localAudio.setEnabled(next);
          if (localAudio.mediaStreamTrack) localAudio.mediaStreamTrack.enabled = next;
        }
      } finally {
        refreshLocalTrackRefs();
        // Prefer explicit intent if SDK state lags one tick
        if (typeof room.localParticipant.isMicrophoneEnabled === 'boolean') {
          micOn = !!room.localParticipant.isMicrophoneEnabled;
        } else {
          micOn = next;
        }
        paintMic();
        if (micOn && noiseOn) applyNoiseToLocalAudio(true).catch(function () {});
      }
    }

    async function toggleCam() {
      var next = !camOn;
      try {
        if (typeof room.localParticipant.setCameraEnabled === 'function') {
          // Official path: disables + stops camera hardware reliably
          await room.localParticipant.setCameraEnabled(next, {
            resolution: VideoPresets.h720.resolution,
          });
        } else if (!localVideo && next) {
          var tracks = await createLocalTracks({
            audio: false,
            video: { resolution: VideoPresets.h720.resolution },
          });
          localVideo = tracks[0];
          await room.localParticipant.publishTrack(localVideo);
        } else if (localVideo) {
          await localVideo.setEnabled(next);
          if (localVideo.mediaStreamTrack) {
            localVideo.mediaStreamTrack.enabled = next;
          }
          if (!next) {
            try {
              await room.localParticipant.unpublishTrack(localVideo, true);
            } catch (e) {}
            try {
              localVideo.stop();
            } catch (e2) {}
            localVideo = null;
            removeTile(room.localParticipant, Track.Source.Camera);
          }
        }
      } finally {
        refreshLocalTrackRefs();
        if (typeof room.localParticipant.isCameraEnabled === 'boolean') {
          camOn = !!room.localParticipant.isCameraEnabled;
        } else {
          camOn = next;
        }
        if (!camOn) {
          syncLocalMediaTile(Track.Source.Camera, false);
          // Ensure local preview tile disappears when camera is off
          try {
            removeTile(room.localParticipant, Track.Source.Camera);
          } catch (e3) {}
        }
        paintCam();
      }
    }

    async function startScreenShare() {
      if (!isHost && !perms.allow_participant_screen_share) {
        toast('المعلم لم يُتح مشاركة الشاشة');
        return;
      }
      if (curriculumActive) {
        if (typeof api.closeCurriculumPresenter === 'function') {
          try {
            api.closeCurriculumPresenter();
          } catch (e) {}
        }
      }
      if (typeof room.localParticipant.setScreenShareEnabled === 'function') {
        await room.localParticipant.setScreenShareEnabled(true, {
          audio: false,
          resolution: ScreenSharePresets.h1080fps30.resolution,
          contentHint: 'detail',
        });
        refreshLocalTrackRefs();
        var pub = room.localParticipant.getTrackPublication(Track.Source.ScreenShare);
        if (pub && pub.track) {
          localScreenTracks = [pub.track];
          try {
            if (pub.track.mediaStreamTrack) pub.track.mediaStreamTrack.contentHint = 'detail';
          } catch (e) {}
        }
      } else {
        var tracks = await createLocalScreenTracks({
          audio: false,
          resolution: ScreenSharePresets.h1080fps30.resolution,
          contentHint: 'detail',
        });
        localScreenTracks = tracks;
        for (var i = 0; i < tracks.length; i++) {
          var t = tracks[i];
          try {
            if (t.mediaStreamTrack) t.mediaStreamTrack.contentHint = 'detail';
          } catch (e) {}
          await room.localParticipant.publishTrack(t, {
            source: Track.Source.ScreenShare,
            name: 'screen',
            simulcast: false,
            videoCodec: 'vp8',
            screenShareEncoding: { maxBitrate: 6_000_000, maxFramerate: 30 },
          });
        }
      }
      shareOn = true;
      paintShare();
      setStatus('شير شاشة · 1080p');
    }

    async function stopScreenShare() {
      if (typeof room.localParticipant.setScreenShareEnabled === 'function') {
        try {
          await room.localParticipant.setScreenShareEnabled(false);
        } catch (e) {}
      }
      for (var i = 0; i < localScreenTracks.length; i++) {
        var t = localScreenTracks[i];
        try {
          await room.localParticipant.unpublishTrack(t);
        } catch (e2) {}
        try {
          t.stop();
        } catch (e3) {}
      }
      localScreenTracks = [];
      shareOn = false;
      paintShare();
      setStatus('متصل');
    }

    async function toggleShare() {
      if (shareOn) await stopScreenShare();
      else await startScreenShare();
    }

    async function toggleHand() {
      if (!isHost && !perms.allow_participant_raise_hand) {
        toast('المعلم لم يُتح رفع اليد');
        return;
      }
      handRaised = !handRaised;
      if (handRaised) raisedHands.add(room.localParticipant.identity);
      else raisedHands.delete(room.localParticipant.identity);
      paintHand();
      refreshHandLabels();
      await sendData('hand', { raised: handRaised });
    }

    async function sendChat(text) {
      text = String(text || '').trim();
      if (!text) return;
      if (!isHost && !perms.allow_participant_chat) {
        toast('المعلم لم يُتح الدردشة');
        return;
      }
      appendChat(room.localParticipant.name || 'أنت', text, true);
      await sendData('chat', { text: text.slice(0, 500) });
    }

    async function broadcastPermissions(next) {
      applyPermissions(next);
      await sendData('permissions', perms);
    }

    async function announceMeetingEnded() {
      try {
        if (typeof api.closeCurriculumPresenter === 'function') {
          api.closeCurriculumPresenter();
        }
      } catch (e0) {}
      try {
        await sendData('meeting_ended', {});
      } catch (e) {}
      try {
        room.disconnect();
      } catch (e2) {}
    }

    function replayCurriculumState() {
      if (!isHost || !curriculumActive || !curriculumSnapshot) return;
      sendData('curriculum_state', curriculumSnapshot).catch(function () {});
    }

    function sendCurriculum(type, payload, reliable) {
      if (isHost) {
        if (type === 'curriculum_open' || type === 'curriculum_state') {
          curriculumSnapshot = Object.assign({}, payload || {}, { active: true });
          curriculumActive = true;
        } else if (type === 'curriculum_slide' && curriculumSnapshot) {
          curriculumSnapshot.index = payload && payload.index != null ? payload.index : curriculumSnapshot.index;
        } else if (type === 'curriculum_viewport' && curriculumSnapshot) {
          curriculumSnapshot.scale = payload.scale;
          curriculumSnapshot.tx = payload.tx;
          curriculumSnapshot.ty = payload.ty;
        } else if (type === 'curriculum_close') {
          curriculumActive = false;
          curriculumSnapshot = null;
        }
      }
      return sendData(type, payload || {}, reliable !== false);
    }

    function pickRecorderMime(preferAudioOnly) {
      var list = preferAudioOnly
        ? ['audio/webm;codecs=opus', 'audio/webm', 'audio/ogg']
        : ['video/webm;codecs=vp8,opus', 'video/webm;codecs=vp9,opus', 'video/webm'];
      for (var i = 0; i < list.length; i++) {
        if (window.MediaRecorder && MediaRecorder.isTypeSupported(list[i])) return list[i];
      }
      return '';
    }

    var recordingStartedAt = 0;

    async function startLocalRecording(kind) {
      if (mediaRecorder && mediaRecorder.state !== 'inactive') {
        toast('التسجيل يعمل بالفعل');
        return;
      }
      recordingKind = kind === 'report' ? 'report' : 'lecture';
      recordedChunks = [];
      recordingStartedAt = Date.now();
      var tracks = [];
      refreshLocalTrackRefs();

      // Always capture mic for lecture + report
      if (localAudio && localAudio.mediaStreamTrack) {
        try {
          if (!localAudio.mediaStreamTrack.enabled) localAudio.mediaStreamTrack.enabled = true;
        } catch (e0) {}
        tracks.push(localAudio.mediaStreamTrack.clone());
      } else {
        var a = await createLocalTracks({ audio: cleanMicConstraints(), video: false });
        localAudio = a[0];
        await room.localParticipant.publishTrack(localAudio);
        micOn = true;
        paintMic();
        if (noiseOn) applyNoiseToLocalAudio(true).catch(function () {});
        tracks.push(localAudio.mediaStreamTrack.clone());
      }

      var hasVideo = false;
      if (recordingKind === 'lecture') {
        // Prefer screen while presenting; else camera
        if (localScreenTracks[0] && localScreenTracks[0].mediaStreamTrack) {
          tracks.push(localScreenTracks[0].mediaStreamTrack.clone());
          hasVideo = true;
        } else if (localVideo && localVideo.mediaStreamTrack) {
          tracks.push(localVideo.mediaStreamTrack.clone());
          hasVideo = true;
        }
        if (!hasVideo) {
          toast('لا توجد كاميرا/شاشة — سيتم تسجيل الصوت فقط. فعّل الكاميرا أو الشير لفيديو.');
          recordingKind = 'report';
        }
      }

      if (!tracks.length) {
        toast('تعذر بدء التسجيل: لا توجد مسارات صوت/فيديو');
        recordingKind = null;
        return;
      }

      recordingStream = new MediaStream(tracks);
      var mime = pickRecorderMime(recordingKind === 'report' || !hasVideo);
      try {
        mediaRecorder = new MediaRecorder(recordingStream, mime ? { mimeType: mime } : undefined);
      } catch (e) {
        try {
          mediaRecorder = new MediaRecorder(recordingStream);
        } catch (e2) {
          recordingStream.getTracks().forEach(function (t) {
            try { t.stop(); } catch (e3) {}
          });
          recordingStream = null;
          recordingKind = null;
          throw new Error('المتصفح لا يدعم MediaRecorder لهذا النوع');
        }
      }
      mediaRecorder.ondataavailable = function (ev) {
        if (ev.data && ev.data.size) recordedChunks.push(ev.data);
      };
      mediaRecorder.onerror = function () {
        toast('خطأ أثناء التسجيل');
      };
      mediaRecorder.start(1000);
      var badge = $('mx-live-rec-badge');
      if (badge) badge.classList.remove('hidden');
      var stopBtn = $('btn-record-stop');
      var idleWrap = $('mx-record-idle-wrap');
      if (stopBtn) stopBtn.classList.remove('hidden');
      if (idleWrap) idleWrap.classList.add('hidden');
      var menuBtn = $('btn-record-menu');
      if (menuBtn) menuBtn.setAttribute('aria-expanded', 'false');
      toast(recordingKind === 'report' ? 'بدأ التسجيل الصوتي' : 'بدأ تسجيل الجلسة');
    }

    function stopLocalRecording() {
      return new Promise(function (resolve) {
        if (!mediaRecorder || mediaRecorder.state === 'inactive') {
          resolve(null);
          return;
        }
        mediaRecorder.onstop = function () {
          var type =
            recordingKind === 'report'
              ? 'audio/webm'
              : (mediaRecorder && mediaRecorder.mimeType) || 'video/webm';
          var blob = new Blob(recordedChunks, { type: type });
          var durationMs = Math.max(1000, Date.now() - (recordingStartedAt || Date.now()));
          var kindDone = recordingKind;
          if (recordingStream) {
            recordingStream.getTracks().forEach(function (t) {
              try {
                t.stop();
              } catch (e) {}
            });
          }
          recordingStream = null;
          mediaRecorder = null;
          var badge = $('mx-live-rec-badge');
          if (badge) badge.classList.add('hidden');
          var stopBtn = $('btn-record-stop');
          var idleWrap = $('mx-record-idle-wrap');
          if (stopBtn) stopBtn.classList.add('hidden');
          if (idleWrap) idleWrap.classList.remove('hidden');
          recordingKind = null;
          if (!blob.size) {
            toast('التسجيل فارغ — لم يُرفع');
            resolve(null);
            return;
          }
          resolve({ blob: blob, kind: kindDone, durationMs: durationMs });
        };
        try {
          mediaRecorder.stop();
        } catch (e) {
          resolve(null);
        }
      });
    }

    function uploadRecordingViaServer(result, isAudio, csrf) {
      var uploadUrl = isAudio ? (config.audioUploadUrl || '') : (config.uploadUrl || '');
      if (!uploadUrl) {
        return Promise.reject(new Error('مسار الرفع عبر الخادم غير مضبوط'));
      }
      var durationSec = Math.max(1, Math.round((result.durationMs || 0) / 1000) || 1);
      var mime = result.blob.type || (isAudio ? 'audio/webm' : 'video/webm');
      var ext = (mime.indexOf('mp4') >= 0) ? 'mp4' : ((mime.indexOf('ogg') >= 0) ? 'ogg' : 'webm');
      var field = isAudio ? 'recording_audio' : 'recording';
      var filename = (isAudio ? 'report' : 'meeting') + '-' + Date.now() + '.' + ext;
      var formData = new FormData();
      formData.append(field, result.blob, filename);
      formData.append('duration_seconds', String(durationSec));
      return new Promise(function (resolve, reject) {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', uploadUrl, true);
        xhr.setRequestHeader('X-CSRF-TOKEN', csrf);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.timeout = 0;
        xhr.upload.onprogress = function (ev) {
          if (ev.lengthComputable && ev.total > 0) {
            uploadModal.progress((ev.loaded / ev.total) * 100, ev.loaded, ev.total);
          }
        };
        xhr.upload.onload = function () {
          uploadModal.indeterminate('جاري حفظ الملف على الخادم…');
        };
        xhr.onload = function () {
          var data = {};
          try { data = xhr.responseText ? JSON.parse(xhr.responseText) : {}; } catch (e) {}
          if (xhr.status >= 200 && xhr.status < 300) {
            resolve(data);
            return;
          }
          reject(new Error((data && data.message) ? data.message : 'فشل الرفع عبر الخادم'));
        };
        xhr.onerror = function () {
          reject(new Error('فشل الاتصال أثناء الرفع عبر الخادم'));
        };
        xhr.send(formData);
      });
    }

    function putBlobWithProgress(url, blob, headers) {
      return new Promise(function (resolve, reject) {
        var xhr = new XMLHttpRequest();
        xhr.open('PUT', url, true);
        Object.keys(headers || {}).forEach(function (k) {
          try { xhr.setRequestHeader(k, headers[k]); } catch (e) {}
        });
        xhr.timeout = 0;
        xhr.upload.onprogress = function (ev) {
          if (ev.lengthComputable && ev.total > 0) {
            uploadModal.progress((ev.loaded / ev.total) * 100, ev.loaded, ev.total);
          }
        };
        xhr.onload = function () {
          if (xhr.status >= 200 && xhr.status < 300) {
            resolve();
            return;
          }
          reject(new Error('فشل رفع الملف للسحابة (' + xhr.status + ')'));
        };
        xhr.onerror = function () {
          reject(new Error('انقطع الاتصال أثناء الرفع للسحابة'));
        };
        xhr.send(blob);
      });
    }

    async function uploadRecording(result) {
      if (!result || !result.blob) {
        toast('لا يوجد ملف للرفع');
        return;
      }
      var csrf = config.csrfToken || '';
      var isAudio = result.kind === 'report';
      var presignUrl = isAudio ? config.audioPresignUrl || config.presignUrl : config.presignUrl;
      var completeUrl = isAudio ? config.audioCompleteUrl || config.completeUrl : config.completeUrl;
      if (!presignUrl || !completeUrl) {
        toast('مسارات الرفع غير مضبوطة');
        return;
      }
      var mime = result.blob.type || (isAudio ? 'audio/webm' : 'video/webm');
      var doneTitle = isAudio ? 'تم رفع التقرير الصوتي' : 'تم رفع تسجيل الجلسة';
      var putSucceeded = false;
      uploadModal.open(
        isAudio ? 'جاري رفع التقرير الصوتي' : 'جاري رفع تسجيل الجلسة',
        'حجم الملف ' + formatBytes(result.blob.size) + ' — جاري تجهيز الرفع…'
      );
      beginUploadGuard();
      try {
        try {
          var presignRes = await fetch(presignUrl, {
            method: 'POST',
            headers: {
              Accept: 'application/json',
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': csrf,
            },
            body: JSON.stringify({ content_type: mime }),
          });
          var presign = await presignRes.json().catch(function () { return {}; });

          if (presignRes.ok && presign.direct_upload === false) {
            uploadModal.status('جاري الرفع عبر الخادم…');
            await uploadRecordingViaServer(result, isAudio, csrf);
            uploadModal.done(doneTitle);
            return;
          }

          if (!presignRes.ok) throw new Error(presign.message || 'فشل تجهيز الرفع');
          if (!presign.upload_url || !presign.upload_token) {
            throw new Error(presign.message || 'رابط الرفع غير متاح');
          }

          var putHeaders = { 'Content-Type': presign.content_type || mime };
          if (presign.headers && typeof presign.headers === 'object') {
            Object.keys(presign.headers).forEach(function (k) {
              putHeaders[k] = presign.headers[k];
            });
          }
          uploadModal.status('جاري الرفع إلى التخزين السحابي…');
          await putBlobWithProgress(presign.upload_url, result.blob, putHeaders);
          putSucceeded = true;
          uploadModal.indeterminate('جاري تأكيد الملف على الخادم…');
          var completeRes = await fetch(completeUrl, {
            method: 'POST',
            headers: {
              Accept: 'application/json',
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': csrf,
            },
            body: JSON.stringify({
              upload_token: presign.upload_token,
              duration_seconds: Math.max(1, Math.round((result.durationMs || 0) / 1000) || 1),
            }),
          });
          var complete = await completeRes.json().catch(function () { return {}; });
          if (!completeRes.ok) throw new Error(complete.message || 'فشل تأكيد الرفع');
          uploadModal.done(doneTitle);
        } catch (err) {
          if (putSucceeded) throw err;
          console.warn('Direct upload failed, falling back to server upload', err);
          uploadModal.indeterminate('تعذر الرفع المباشر — جاري الرفع عبر الخادم…');
          await uploadRecordingViaServer(result, isAudio, csrf);
          uploadModal.done(doneTitle);
        }
      } catch (finalErr) {
        uploadModal.fail(finalErr && finalErr.message ? finalErr.message : 'تعذر رفع التسجيل.');
        throw finalErr;
      } finally {
        endUploadGuard();
      }
    }

    function wireUi() {
      var mic = $('mx-ml-btn-mic');
      var cam = $('mx-ml-btn-cam');
      var share = $('mx-ml-btn-share');
      var hand = $('mx-ml-btn-react');
      var chatSend = $('mx-lk-chat-send');
      var peopleBtn = $('mx-ml-btn-people');
      var chatBtn = $('mx-ml-btn-chat');
      var peoplePanel = $('mx-lk-people-panel');
      var chatPanel = $('mx-lk-chat-panel');

      if (mic) mic.addEventListener('click', function () { toggleMic().catch(function (e) { toast(e.message || 'خطأ ميكروفون'); }); });
      if (cam) cam.addEventListener('click', function () { toggleCam().catch(function (e) { toast(e.message || 'خطأ كاميرا'); }); });
      if (share) share.addEventListener('click', function () { toggleShare().catch(function (e) { toast(e.message || 'خطأ الشير'); }); });
      if (hand) {
        ensureReactMenu();
        hand.addEventListener('click', function (ev) {
          ev.preventDefault();
          var menu = $('mx-ml-react-menu');
          if (!menu) {
            toggleHand().catch(function () {});
            return;
          }
          menu.classList.toggle('hidden');
        });
        document.addEventListener('click', function (ev) {
          var menu = $('mx-ml-react-menu');
          var wrap = $('mx-ml-react-wrap');
          if (!menu || menu.classList.contains('hidden')) return;
          if (wrap && wrap.contains(ev.target)) return;
          menu.classList.add('hidden');
        });
      }
      if (chatSend && chatInput) {
        chatSend.addEventListener('click', function () {
          sendChat(chatInput.value).then(function () {
            chatInput.value = '';
          });
        });
        chatInput.addEventListener('keydown', function (e) {
          if (e.key === 'Enter') {
            e.preventDefault();
            chatSend.click();
          }
        });
      }
      if (peopleBtn && peoplePanel) {
        peopleBtn.addEventListener('click', function () {
          peoplePanel.classList.toggle('hidden');
          if (chatPanel) chatPanel.classList.add('hidden');
          renderPeople();
        });
      }
      if (chatBtn && chatPanel) {
        chatBtn.addEventListener('click', function () {
          if (!isHost && !perms.allow_participant_chat) {
            toast('المعلم لم يُتح الدردشة');
            return;
          }
          chatPanel.classList.toggle('hidden');
          if (peoplePanel) peoplePanel.classList.add('hidden');
        });
      }

      var annotateBtn = $('mx-ml-btn-annotate');
      if (annotateBtn) {
        annotateBtn.addEventListener('click', function () {
          var hasScreen = !!(stage && stage.querySelector('.lk-tile.is-screen'));
          if (!hasScreen) {
            toast('فعّل مشاركة الشاشة أولاً للرسم عليها');
            return;
          }
          if (!isHost && !perms.allow_participant_whiteboard) {
            toast('المعلم لم يُتح الكتابة على الشاشة (صلاحية السبورة)');
            return;
          }
          if (typeof window.__mxShareAnnOpenToolbar === 'function') {
            window.__mxShareAnnOpenToolbar();
            annotateBtn.classList.add('is-active');
            if (isHost) {
              toast('تعرض رسومات الطلاب — استخدم «مسح كل الرسومات» عند الحاجة');
            }
          }
        });
      }

      var laserBtn = $('mx-ml-btn-laser');
      if (laserBtn) {
        laserBtn.addEventListener('click', function () {
          var hasScreen = !!(stage && stage.querySelector('.lk-tile.is-screen'));
          if (!hasScreen) {
            toast('فعّل مشاركة الشاشة أولاً للإشارة عليها');
            return;
          }
          laserOn = !laserOn;
          laserBtn.classList.toggle('is-active', laserOn);
          document.body.classList.toggle('lk-laser-on', laserOn);
          if (!laserOn) {
            sendData('pointer', { on: false }, false).catch(function () {});
            showPointer(room.localParticipant.identity, '', 0, 0, false);
          } else {
            toast('حرّك المؤشر فوق الشاشة للإشارة');
          }
        });
      }

      var noiseBtn = $('mx-ml-btn-noise');
      if (noiseBtn) {
        noiseBtn.addEventListener('click', function () {
          applyNoiseToLocalAudio(!noiseOn).catch(function () {});
        });
      }
      var sfNoise = $('mx-sf-noise');
      if (sfNoise) {
        sfNoise.addEventListener('click', function () {
          applyNoiseToLocalAudio(!noiseOn).catch(function () {});
        });
      }
      var sfStop = $('mx-sf-stop-share');
      if (sfStop) {
        sfStop.addEventListener('click', function () {
          stopScreenShare().catch(function () {});
        });
      }
      var sfMic = $('mx-sf-mic');
      if (sfMic) {
        sfMic.addEventListener('click', function () {
          toggleMic().catch(function () {});
        });
      }
      var sfCam = $('mx-sf-cam');
      if (sfCam) {
        sfCam.addEventListener('click', function () {
          toggleCam().catch(function () {});
        });
      }
      var sfPeople = $('mx-sf-people');
      if (sfPeople) {
        sfPeople.addEventListener('click', function () {
          toggleParticipantPip().catch(function (e) {
            toast(e.message || 'تعذر فتح نافذة المشاركين');
          });
        });
      }
      var sfTile = $('mx-sf-tile');
      if (sfTile) {
        sfTile.addEventListener('click', function () {
          setLayoutMode(layoutMode === 'grid' ? 'speaker' : 'grid');
        });
      }

      var tileBtn = $('mx-ml-btn-tile');
      if (tileBtn) {
        tileBtn.addEventListener('click', function () {
          setLayoutMode(layoutMode === 'grid' ? 'speaker' : 'grid');
          toast(layoutMode === 'grid' ? 'عرض الشبكة' : 'عرض المتحدث');
        });
      }
      var focusBtn = $('mx-ml-btn-focus');
      if (focusBtn) {
        focusBtn.addEventListener('click', function () {
          toggleFocusMode();
        });
      }
      var pipBtn = $('mx-ml-btn-pip');
      if (pipBtn) {
        pipBtn.addEventListener('click', function () {
          toggleParticipantPip().catch(function (e) {
            toast(e.message || 'تعذر فتح نافذة المشاركين');
          });
        });
      }

      document.addEventListener('keydown', function (ev) {
        if (ev.target && (ev.target.tagName === 'INPUT' || ev.target.tagName === 'TEXTAREA')) return;
        var k = (ev.key || '').toLowerCase();
        if (k === 'm') toggleMic().catch(function () {});
        if (k === 'v') toggleCam().catch(function () {});
        if (k === 's' && !ev.metaKey && !ev.ctrlKey) toggleShare().catch(function () {});
        if (k === 'f') toggleFocusMode();
        if (k === 't') setLayoutMode(layoutMode === 'grid' ? 'speaker' : 'grid');
        if (k === 'h') toggleHand().catch(function () {});
      });

      if (isHost) {
        document.querySelectorAll('[data-perm-key]').forEach(function (input) {
          input.addEventListener('change', function () {
            var key = input.getAttribute('data-perm-key');
            var body = {};
            body[key] = !!input.checked;
            fetch(config.permissionsUrl, {
              method: 'POST',
              headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': config.csrfToken || '',
                'X-Requested-With': 'XMLHttpRequest',
              },
              body: JSON.stringify(body),
            })
              .then(function (r) {
                return r.json();
              })
              .then(function (data) {
                if (!data.ok) throw new Error(data.message || 'فشل حفظ الإعداد');
                if (key === 'waiting_room_enabled') {
                  var section = $('mx-waiting-room-section');
                  if (section) section.classList.toggle('hidden', !input.checked);
                  if (input.checked) startWaitingRoomPoll();
                  else {
                    stopWaitingRoomPoll();
                    updateWaitingBadge(0);
                    renderWaitingGuests([]);
                  }
                  updateWaitingBadge(data.waiting_pending_count);
                }
                if (key.indexOf('allow_participant_') === 0) {
                  return broadcastPermissions(data);
                }
                return data;
              })
              .catch(function (e) {
                toast(e.message || 'تعذر حفظ الإعداد');
                input.checked = !input.checked;
              });
          });
        });

        if (config.waitingRoomListUrl && perms.waiting_room_enabled) {
          startWaitingRoomPoll();
        }

        var muteAll = $('mx-lk-mute-all');
        if (muteAll) {
          muteAll.addEventListener('click', function () {
            room.remoteParticipants.forEach(function (p) {
              muteRemote(p, Track.Source.Microphone, true);
            });
          });
        }

        document.querySelectorAll('[data-mx-rec-mode]').forEach(function (btn) {
          btn.addEventListener('click', function (ev) {
            ev.preventDefault();
            ev.stopPropagation();
            var panel = $('mx-record-dd-panel');
            if (panel) panel.classList.add('hidden');
            var menuBtn = $('btn-record-menu');
            if (menuBtn) menuBtn.setAttribute('aria-expanded', 'false');
            startLocalRecording(btn.getAttribute('data-mx-rec-mode')).catch(function (e) {
              toast(e.message || 'تعذر بدء التسجيل');
            });
          });
        });
        var recMenu = $('btn-record-menu');
        var recPanel = $('mx-record-dd-panel');
        var recWrap = $('mx-record-dd-wrap');
        if (recMenu && recPanel) {
          recMenu.addEventListener('click', function (ev) {
            ev.preventDefault();
            ev.stopPropagation();
            var open = recPanel.classList.contains('hidden');
            recPanel.classList.toggle('hidden', !open);
            recMenu.setAttribute('aria-expanded', open ? 'true' : 'false');
            var permsPanel = $('mx-guest-perms-panel');
            if (permsPanel) permsPanel.classList.add('hidden');
          });
          document.addEventListener('click', function (ev) {
            if (!recWrap || recWrap.contains(ev.target)) return;
            recPanel.classList.add('hidden');
            recMenu.setAttribute('aria-expanded', 'false');
          });
        }
        var recStop = $('btn-record-stop');
        if (recStop) {
          recStop.addEventListener('click', function () {
            uploadModal.open('جاري إنهاء التسجيل', 'يتم تجهيز ملف التسجيل…');
            stopLocalRecording().then(function (result) {
              if (!result) {
                uploadModal.hide();
                return;
              }
              return uploadRecording(result);
            }).catch(function (e) {
              uploadModal.fail(e.message || 'تعذر رفع التسجيل');
            });
          });
        }

        var endForm = $('mx-end-meeting-form');
        if (endForm) {
          endForm.addEventListener('submit', function () {
            announceMeetingEnded();
          });
        }
      }

      var copyBtn = $('btn-classroom-copy-join');
      if (copyBtn && config.joinUrl) {
        copyBtn.addEventListener('click', function () {
          navigator.clipboard.writeText(config.joinUrl).then(function () {
            toast('تم نسخ رابط الانضمام');
          });
        });
      }
    }

    room
      .on(RoomEvent.TrackSubscribed, function (track, publication, participant) {
        if (publication && track.source === Track.Source.ScreenShare) {
          try {
            publication.setVideoQuality(VideoQuality.HIGH);
          } catch (e) {}
        }
        attachTrack(track, participant);
        renderPeople();
        scheduleParticipantPipRefresh();
      })
      .on(RoomEvent.TrackUnsubscribed, function (track, _pub, participant) {
        detachTrack(track, participant);
        scheduleParticipantPipRefresh();
      })
      .on(RoomEvent.LocalTrackPublished, function (pub, participant) {
        if (pub.track) attachTrack(pub.track, participant);
        scheduleParticipantPipRefresh();
      })
      .on(RoomEvent.LocalTrackUnpublished, function (pub, participant) {
        if (pub.track) detachTrack(pub.track, participant);
        scheduleParticipantPipRefresh();
      })
      .on(RoomEvent.ParticipantConnected, function () {
        renderPeople();
        scheduleParticipantPipRefresh();
        toast('انضم مشارك');
        replayCurriculumState();
      })
      .on(RoomEvent.ParticipantDisconnected, function (p) {
        raisedHands.delete(p.identity);
        renderPeople();
        scheduleParticipantPipRefresh();
      })
      .on(RoomEvent.DataReceived, function (payload, participant) {
        handleData(payload, participant);
      })
      .on(RoomEvent.ConnectionQualityChanged, function (quality, participant) {
        if (!participant || participant.isLocal) setQualityUI(quality);
      })
      .on(RoomEvent.ActiveSpeakersChanged, function (speakers) {
        activeSpeakerId = speakers && speakers[0] ? speakers[0].identity : '';
        applySpeakerLayout();
        scheduleParticipantPipRefresh();
      })
      .on(RoomEvent.Disconnected, function () {
        setStatus('انقطع الاتصال');
        closeParticipantPip();
        stopWaitingRoomPoll();
        try {
          if (typeof api.closeCurriculumPresenter === 'function') {
            api.closeCurriculumPresenter();
          }
        } catch (e) {}
      })
      .on(RoomEvent.Reconnecting, function () {
        setStatus('إعادة اتصال…');
      })
      .on(RoomEvent.Reconnected, function () {
        setStatus('متصل');
        if (isHost) {
          replayCurriculumState();
        } else {
          sendData('curriculum_state_req', {}).catch(function () {});
        }
      });

    setStatus('جاري الاتصال…');
    await room.connect(config.url, config.token);
    setStatus('متصل');
    applyPermissions(perms);
    wireUi();
    setLayoutMode('grid');
    paintMic();
    paintCam();
    paintShare();
    paintHand();
    paintNoise();
    renderPeople();
    applyNoiseToLocalAudio(true).catch(function () {});

    // Realtime share-annotation bridge (guest → host)
    window.__mxShareAnnBroadcast = function (polylines) {
      sendData('annotate', { polylines: polylines || [] }, false).catch(function () {});
    };
    window.__mxShareAnnBroadcastClear = function () {
      sendData('annotate_clear', {}, true).catch(function () {});
    };

    room.remoteParticipants.forEach(function (p) {
      p.trackPublications.forEach(function (pub) {
        if (pub.track) attachTrack(pub.track, p);
      });
    });

    api.room = room;
    api.sendChat = sendChat;
    api.toggleMic = toggleMic;
    api.toggleCam = toggleCam;
    api.toggleShare = toggleShare;
    api.toggleHand = toggleHand;
    api.broadcastPermissions = broadcastPermissions;
    api.announceMeetingEnded = announceMeetingEnded;
    api.getPermissions = function () {
      return perms;
    };
    api.applyPermissions = applyPermissions;
    api.startLocalRecording = startLocalRecording;
    api.stopLocalRecording = stopLocalRecording;
    api.openParticipantPip = openParticipantPip;
    api.closeParticipantPip = closeParticipantPip;
    api.isParticipantPipOpen = isParticipantPipOpen;
    api.getLocalVideoTrack = function () {
      return localVideo;
    };
    api.getLocalAudioTrack = function () {
      return localAudio;
    };
    api.toast = toast;
    api.sendCurriculum = sendCurriculum;
    api.registerCurriculumHandler = function (fn) {
      curriculumHandler = typeof fn === 'function' ? fn : null;
    };
    api.setCurriculumActive = function (on) {
      curriculumActive = !!on;
      if (!on) curriculumSnapshot = null;
    };
    api.stopScreenShareIfActive = function () {
      if (!shareOn) return Promise.resolve();
      return stopScreenShare();
    };
    api.openCurriculumPresenter = function () {};
    api.closeCurriculumPresenter = function () {};
    api.disconnect = function () {
      closeParticipantPip();
      try {
        if (typeof api.closeCurriculumPresenter === 'function') {
          api.closeCurriculumPresenter();
        }
      } catch (e0) {}
      try {
        room.disconnect();
      } catch (e) {}
    };

    window.addEventListener('beforeunload', function () {
      closeParticipantPip();
      try {
        if (typeof api.closeCurriculumPresenter === 'function') {
          api.closeCurriculumPresenter();
        }
      } catch (e0) {}
      try {
        room.disconnect();
      } catch (e) {}
    });

    return api;
  }

  global.MxLiveKitClassroom = { boot: boot };
})(typeof window !== 'undefined' ? window : globalThis);
