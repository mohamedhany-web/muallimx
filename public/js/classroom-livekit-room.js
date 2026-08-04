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

    function togglePipPanel() {
      var panel = $('mx-lk-people-panel');
      if (!panel) return;
      panel.classList.toggle('hidden');
      panel.classList.toggle('lk-pip-float', !panel.classList.contains('hidden'));
      var btn = $('mx-ml-btn-pip');
      if (btn) btn.classList.toggle('is-active', !panel.classList.contains('hidden'));
      if (!panel.classList.contains('hidden')) renderPeople();
    }

    function syncShareFloat() {
      var bar = $('mx-share-float');
      document.body.classList.toggle('mx-sharing', !!shareOn);
      if (bar) bar.classList.toggle('is-open', !!shareOn);
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
            // Host always sees drawings; guests need whiteboard permission to draw
            window.__mxShareAnnSetAllowed(isHost || !!perms.allow_participant_whiteboard);
          }
          if (isHost) {
            layer.classList.remove('hidden');
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
        await sendData('meeting_ended', {});
      } catch (e) {}
      try {
        room.disconnect();
      } catch (e2) {}
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
      if (mediaRecorder && mediaRecorder.state !== 'inactive') return;
      recordingKind = kind === 'report' ? 'report' : 'lecture';
      recordedChunks = [];
      recordingStartedAt = Date.now();
      var tracks = [];
      if (localAudio && localAudio.mediaStreamTrack) {
        tracks.push(localAudio.mediaStreamTrack.clone());
      } else {
        var a = await createLocalTracks({ audio: true, video: false });
        localAudio = a[0];
        await room.localParticipant.publishTrack(localAudio);
        micOn = true;
        paintMic();
        tracks.push(localAudio.mediaStreamTrack.clone());
      }
      if (recordingKind === 'lecture') {
        // Prefer screen share while presenting so the lecture capture matches what students see
        if (localScreenTracks[0] && localScreenTracks[0].mediaStreamTrack) {
          tracks.push(localScreenTracks[0].mediaStreamTrack.clone());
        } else if (localVideo && localVideo.mediaStreamTrack) {
          tracks.push(localVideo.mediaStreamTrack.clone());
        }
      }
      recordingStream = new MediaStream(tracks);
      var mime = pickRecorderMime(recordingKind === 'report');
      mediaRecorder = new MediaRecorder(recordingStream, mime ? { mimeType: mime } : undefined);
      mediaRecorder.ondataavailable = function (ev) {
        if (ev.data && ev.data.size) recordedChunks.push(ev.data);
      };
      mediaRecorder.start(1000);
      var badge = $('mx-live-rec-badge');
      if (badge) badge.classList.remove('hidden');
      var stopBtn = $('btn-record-stop');
      var idleWrap = $('mx-record-idle-wrap');
      if (stopBtn) stopBtn.classList.remove('hidden');
      if (idleWrap) idleWrap.classList.add('hidden');
      toast(recordingKind === 'report' ? 'بدأ التسجيل الصوتي' : 'بدأ تسجيل الجلسة (محلي)');
    }

    function stopLocalRecording() {
      return new Promise(function (resolve) {
        if (!mediaRecorder || mediaRecorder.state === 'inactive') {
          resolve(null);
          return;
        }
        mediaRecorder.onstop = function () {
          var type = recordingKind === 'report' ? 'audio/webm' : 'video/webm';
          var blob = new Blob(recordedChunks, { type: type });
          var durationMs = Math.max(1000, Date.now() - (recordingStartedAt || Date.now()));
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
          resolve({ blob: blob, kind: recordingKind, durationMs: durationMs });
          recordingKind = null;
        };
        try {
          mediaRecorder.stop();
        } catch (e) {
          resolve(null);
        }
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
      var presignRes = await fetch(presignUrl, {
        method: 'POST',
        headers: {
          Accept: 'application/json',
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrf,
        },
        body: JSON.stringify({ content_type: mime }),
      });
      var presign = await presignRes.json();
      if (!presignRes.ok) throw new Error(presign.message || 'فشل تجهيز الرفع');
      if (!presign.direct_upload || !presign.upload_url || !presign.upload_token) {
        toast(presign.message || 'الرفع المباشر غير متاح حالياً');
        return;
      }
      var putHeaders = { 'Content-Type': presign.content_type || mime };
      if (presign.headers && typeof presign.headers === 'object') {
        Object.keys(presign.headers).forEach(function (k) {
          putHeaders[k] = presign.headers[k];
        });
      }
      var put = await fetch(presign.upload_url, {
        method: 'PUT',
        headers: putHeaders,
        body: result.blob,
      });
      if (!put.ok) throw new Error('فشل رفع الملف للسحابة');
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
      toast('تم رفع التسجيل');
      if (config.uploadTabUrl) window.open(config.uploadTabUrl, '_blank');
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
          if (isHost) {
            // Host already polls/views student drawings; open tip
            toast('الطلاب يرسمون على الشاشة عند تفعيل صلاحية السبورة');
            return;
          }
          if (typeof window.__mxShareAnnOpenToolbar === 'function') {
            window.__mxShareAnnOpenToolbar();
            annotateBtn.classList.add('is-active');
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
          togglePipPanel();
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
          togglePipPanel();
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
            var body = {};
            body[input.getAttribute('data-perm-key')] = !!input.checked;
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
                if (!data.ok) throw new Error(data.message || 'فشل حفظ الصلاحية');
                return broadcastPermissions(data);
              })
              .catch(function (e) {
                toast(e.message || 'تعذر حفظ الصلاحية');
                input.checked = !input.checked;
              });
          });
        });

        var muteAll = $('mx-lk-mute-all');
        if (muteAll) {
          muteAll.addEventListener('click', function () {
            room.remoteParticipants.forEach(function (p) {
              muteRemote(p, Track.Source.Microphone, true);
            });
          });
        }

        document.querySelectorAll('[data-mx-rec-mode]').forEach(function (btn) {
          btn.addEventListener('click', function () {
            startLocalRecording(btn.getAttribute('data-mx-rec-mode')).catch(function (e) {
              toast(e.message || 'تعذر بدء التسجيل');
            });
            var panel = $('mx-record-dd-panel');
            if (panel) panel.classList.add('hidden');
          });
        });
        var recMenu = $('btn-record-menu');
        var recPanel = $('mx-record-dd-panel');
        if (recMenu && recPanel) {
          recMenu.addEventListener('click', function () {
            recPanel.classList.toggle('hidden');
          });
        }
        var recStop = $('btn-record-stop');
        if (recStop) {
          recStop.addEventListener('click', function () {
            stopLocalRecording().then(function (result) {
              if (!result) return;
              return uploadRecording(result);
            }).catch(function (e) {
              toast(e.message || 'تعذر رفع التسجيل');
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
      })
      .on(RoomEvent.TrackUnsubscribed, function (track, _pub, participant) {
        detachTrack(track, participant);
      })
      .on(RoomEvent.LocalTrackPublished, function (pub, participant) {
        if (pub.track) attachTrack(pub.track, participant);
      })
      .on(RoomEvent.LocalTrackUnpublished, function (pub, participant) {
        if (pub.track) detachTrack(pub.track, participant);
      })
      .on(RoomEvent.ParticipantConnected, function () {
        renderPeople();
        toast('انضم مشارك');
      })
      .on(RoomEvent.ParticipantDisconnected, function (p) {
        raisedHands.delete(p.identity);
        renderPeople();
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
      })
      .on(RoomEvent.Disconnected, function () {
        setStatus('انقطع الاتصال');
      })
      .on(RoomEvent.Reconnecting, function () {
        setStatus('إعادة اتصال…');
      })
      .on(RoomEvent.Reconnected, function () {
        setStatus('متصل');
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
    api.getLocalVideoTrack = function () {
      return localVideo;
    };
    api.getLocalAudioTrack = function () {
      return localAudio;
    };
    api.disconnect = function () {
      try {
        room.disconnect();
      } catch (e) {}
    };

    window.addEventListener('beforeunload', function () {
      try {
        room.disconnect();
      } catch (e) {}
    });

    return api;
  }

  global.MxLiveKitClassroom = { boot: boot };
})(typeof window !== 'undefined' ? window : globalThis);
