/**
 * Muallimx Classroom — Floating share controls (Zoom-like) + Document PiP chrome.
 * Does NOT open a second Jitsi conference (that broke host hearing students).
 */
(function (global) {
  'use strict';

  function clamp(n, min, max) {
    return Math.max(min, Math.min(max, n));
  }

  function bindDrag(el, handle) {
    if (!el || !handle) return function () {};
    var dragging = false;
    var ox = 0;
    var oy = 0;
    var pointerId = null;

    function onDown(e) {
      if (e.target && e.target.closest && e.target.closest('button,a,input,label')) return;
      var pt = e.touches && e.touches[0] ? e.touches[0] : e;
      if (typeof e.button === 'number' && e.button !== 0) return;
      dragging = true;
      pointerId = e.pointerId;
      var r = el.getBoundingClientRect();
      ox = pt.clientX - r.left;
      oy = pt.clientY - r.top;
      el.classList.add('is-dragging');
      try { if (handle.setPointerCapture && e.pointerId != null) handle.setPointerCapture(e.pointerId); } catch (err) {}
      e.preventDefault();
    }

    function onMove(e) {
      if (!dragging) return;
      if (pointerId != null && e.pointerId != null && e.pointerId !== pointerId) return;
      var pt = e.touches && e.touches[0] ? e.touches[0] : e;
      var w = el.offsetWidth || 280;
      var h = el.offsetHeight || 56;
      var x = clamp(pt.clientX - ox, 8, (window.innerWidth || 800) - w - 8);
      var y = clamp(pt.clientY - oy, 8, (window.innerHeight || 600) - h - 8);
      el.style.left = x + 'px';
      el.style.top = y + 'px';
      el.style.right = 'auto';
      el.style.bottom = 'auto';
      if (e.cancelable) e.preventDefault();
    }

    function onUp(e) {
      if (!dragging) return;
      if (pointerId != null && e.pointerId != null && e.pointerId !== pointerId) return;
      dragging = false;
      pointerId = null;
      el.classList.remove('is-dragging');
    }

    handle.addEventListener('pointerdown', onDown);
    window.addEventListener('pointermove', onMove, { passive: false });
    window.addEventListener('pointerup', onUp);
    window.addEventListener('pointercancel', onUp);
    // touch fallback
    handle.addEventListener('touchstart', onDown, { passive: false });
    window.addEventListener('touchmove', onMove, { passive: false });
    window.addEventListener('touchend', onUp);

    return function unbind() {
      handle.removeEventListener('pointerdown', onDown);
      window.removeEventListener('pointermove', onMove);
      window.removeEventListener('pointerup', onUp);
      window.removeEventListener('pointercancel', onUp);
    };
  }

  function syncBtnFromSource(floatBtn, sourceBtn, iconSel) {
    if (!floatBtn || !sourceBtn) return;
    var pressed = sourceBtn.getAttribute('aria-pressed') === 'true';
    var active = sourceBtn.classList.contains('is-active');
    floatBtn.setAttribute('aria-pressed', pressed ? 'true' : 'false');
    floatBtn.classList.toggle('is-active', active || (!pressed && floatBtn.dataset.invertActive !== '1'));
    if (floatBtn.id === 'mx-sf-mic' || floatBtn.id === 'mx-sf-cam') {
      floatBtn.classList.toggle('is-danger', pressed);
    }
    var srcIcon = sourceBtn.querySelector(iconSel || 'i');
    var dstIcon = floatBtn.querySelector('i');
    if (srcIcon && dstIcon) {
      dstIcon.className = srcIcon.className;
    }
  }

  /**
   * @param {object} opts
   * @param {HTMLElement} opts.root
   * @param {function(string, *=): boolean} opts.cmd
   * @param {function(): void=} opts.onStopShare
   * @param {function(): void=} opts.onOpenPeople
   * @param {function(string)=} opts.onToast
   */
  function bindShareFloat(opts) {
    opts = opts || {};
    var root = opts.root;
    if (!root) return null;
    var handle = root.querySelector('[data-sf-handle]') || root;
    var cmd = typeof opts.cmd === 'function' ? opts.cmd : function () { return false; };
    var onToast = typeof opts.onToast === 'function' ? opts.onToast : function () {};
    var visible = false;
    var pipWin = null;

    bindDrag(root, handle);

    function setVisible(on) {
      visible = !!on;
      if (visible) {
        root.classList.add('is-open');
        root.setAttribute('aria-hidden', 'false');
        document.body.classList.add('mx-sharing');
        // default position: bottom center-ish
        if (!root.style.left && !root.style.top) {
          var w = root.offsetWidth || 320;
          root.style.left = Math.max(8, ((window.innerWidth || 800) - w) / 2) + 'px';
          root.style.top = Math.max(8, (window.innerHeight || 600) - 96) + 'px';
          root.style.right = 'auto';
          root.style.bottom = 'auto';
        }
        syncAll();
      } else {
        root.classList.remove('is-open');
        root.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('mx-sharing');
        closeControlsPip();
      }
    }

    function syncAll() {
      syncBtnFromSource(root.querySelector('#mx-sf-mic'), document.getElementById('mx-ml-btn-mic'), '#mx-ml-mic-icon, i');
      syncBtnFromSource(root.querySelector('#mx-sf-cam'), document.getElementById('mx-ml-btn-cam'), '#mx-ml-cam-icon, i');
      syncBtnFromSource(root.querySelector('#mx-sf-noise'), document.getElementById('mx-ml-btn-noise'), 'i');
      var shareBtn = document.getElementById('mx-ml-btn-share');
      var sfShare = root.querySelector('#mx-sf-share');
      if (sfShare && shareBtn) {
        sfShare.classList.toggle('is-active', shareBtn.getAttribute('aria-pressed') === 'true');
      }
    }

    function clickSource(id) {
      var el = document.getElementById(id);
      if (el) el.click();
      setTimeout(syncAll, 220);
    }

    root.querySelectorAll('[data-sf-action]').forEach(function (btn) {
      btn.addEventListener('click', function (e) {
        e.stopPropagation();
        var act = btn.getAttribute('data-sf-action');
        if (act === 'mic') clickSource('mx-ml-btn-mic');
        else if (act === 'cam') clickSource('mx-ml-btn-cam');
        else if (act === 'noise') clickSource('mx-ml-btn-noise');
        else if (act === 'share') {
          if (typeof opts.onStopShare === 'function') opts.onStopShare();
          else clickSource('mx-ml-btn-share');
        } else if (act === 'people') {
          if (typeof opts.onOpenPeople === 'function') opts.onOpenPeople();
          else clickSource('mx-ml-btn-pip');
        } else if (act === 'tile') clickSource('mx-ml-btn-tile');
        else if (act === 'wb') clickSource('btn-wb-popup-open');
        else if (act === 'end') {
          var endBtn = document.getElementById('btn-end-meeting') || document.querySelector('[data-mx-end-meeting]');
          if (endBtn) endBtn.click();
        }
      });
    });

    function controlsPipHtml() {
      return '<!DOCTYPE html><html lang="ar" dir="rtl"><head><meta charset="utf-8"><title>تحكم المشاركة</title>' +
        '<style>' +
        'html,body{margin:0;height:100%;background:transparent;overflow:hidden;font-family:Tahoma,sans-serif}' +
        '.bar{display:flex;align-items:center;gap:6px;height:100%;padding:8px 10px;background:#171717ee;border:1px solid #404040;border-radius:14px;box-sizing:border-box;color:#fff}' +
        '.grip{padding:0 6px;color:#a3a3a3;user-select:none;font-size:12px}' +
        'button{width:40px;height:40px;border-radius:12px;border:1px solid #525252;background:#292929;color:#fff;cursor:pointer;font-size:16px}' +
        'button:hover{background:#0065fd;border-color:#0065fd}' +
        'button.danger{background:#7f1d1d;border-color:#fd0000}' +
        'button.danger:hover{background:#fd0000}' +
        '.label{font-size:11px;color:#d4d4d4;margin-inline-start:4px;white-space:nowrap}' +
        '</style></head><body>' +
        '<div class="bar" id="mx-pip-bar">' +
        '<span class="grip">⋮⋮</span><span class="label">مشاركة الشاشة</span>' +
        '<button type="button" data-act="mic" title="ميكروفون">🎙</button>' +
        '<button type="button" data-act="cam" title="كاميرا">📷</button>' +
        '<button type="button" data-act="noise" title="عزل الضوضاء">🎧</button>' +
        '<button type="button" data-act="share" class="danger" title="إيقاف المشاركة">⏹</button>' +
        '<button type="button" data-act="people" title="المشاركون">👥</button>' +
        '<button type="button" data-act="tile" title="شبكة">▦</button>' +
        '</div></body></html>';
    }

    async function openControlsPip() {
      if (!global.documentPictureInPicture || typeof global.documentPictureInPicture.requestWindow !== 'function') {
        return false;
      }
      try {
        if (pipWin && !pipWin.closed) return true;
        pipWin = await global.documentPictureInPicture.requestWindow({
          width: 460,
          height: 64,
          disallowReturnToOpener: false,
        });
        pipWin.document.open();
        pipWin.document.write(controlsPipHtml());
        pipWin.document.close();
        // Document PiP لا يملك window.opener — اربط الأزرار من الصفحة الأم
        try {
          var btns = pipWin.document.querySelectorAll('[data-act]');
          btns.forEach(function (b) {
            b.addEventListener('click', function () {
              handleExternalCmd(b.getAttribute('data-act'));
            });
          });
        } catch (bindErr) {
          console.warn('Share PiP bind failed', bindErr);
        }
        pipWin.addEventListener('pagehide', function () { pipWin = null; });
        onToast('شريط التحكم العائم فوق التطبيقات — حرّكه لأي زاوية');
        return true;
      } catch (err) {
        console.warn('Share controls PiP failed', err);
        pipWin = null;
        return false;
      }
    }

    function closeControlsPip() {
      try {
        if (pipWin && !pipWin.closed) pipWin.close();
      } catch (e) {}
      pipWin = null;
      try {
        if (global.documentPictureInPicture && global.documentPictureInPicture.window) {
          global.documentPictureInPicture.window.close();
        }
      } catch (e2) {}
    }

    function handleExternalCmd(action) {
      if (action === 'mic') clickSource('mx-ml-btn-mic');
      else if (action === 'cam') clickSource('mx-ml-btn-cam');
      else if (action === 'noise') clickSource('mx-ml-btn-noise');
      else if (action === 'share') {
        if (typeof opts.onStopShare === 'function') opts.onStopShare();
        else clickSource('mx-ml-btn-share');
      } else if (action === 'people') {
        if (typeof opts.onOpenPeople === 'function') opts.onOpenPeople();
      } else if (action === 'tile') clickSource('mx-ml-btn-tile');
      else if (action === 'wb') clickSource('btn-wb-popup-open');
    }

    global.addEventListener('message', function (ev) {
      if (!ev.data || ev.data.type !== 'mx-share-float-cmd') return;
      handleExternalCmd(ev.data.action);
    });

    return {
      show: function () { setVisible(true); },
      hide: function () { setVisible(false); },
      isVisible: function () { return visible; },
      sync: syncAll,
      openControlsPip: openControlsPip,
      closeControlsPip: closeControlsPip,
      handleExternalCmd: handleExternalCmd,
    };
  }

  /**
   * Keep host receiving student audio while sharing (no second join / no audioOnly).
   */
  function preserveReceiveAudio(api) {
    if (!api || typeof api.executeCommand !== 'function') return;
    try { api.executeCommand('setAudioOnly', false); } catch (e1) {}
    try {
      // Ensure speaker path is not stuck muted if API exposes it
      if (typeof api.isAudioMuted === 'function') {
        /* local mic mute is separate from receiving */
      }
    } catch (e2) {}
  }

  global.MxClassroomShareControls = {
    bindShareFloat: bindShareFloat,
    bindDrag: bindDrag,
    preserveReceiveAudio: preserveReceiveAudio,
  };
})(typeof window !== 'undefined' ? window : this);
