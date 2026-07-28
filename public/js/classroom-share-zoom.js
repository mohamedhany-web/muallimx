/**
 * Muallimx Classroom — Pinch / button zoom on guest meeting iframe (mobile share reading).
 * Transforms the iframe element (cross-origin safe).
 */
(function (global) {
  'use strict';

  function clamp(n, min, max) {
    return Math.max(min, Math.min(max, n));
  }

  /**
   * @param {object} opts
   * @param {HTMLElement} opts.viewport
   * @param {HTMLElement} opts.target  iframe or container to transform
   * @param {HTMLElement=} opts.hud
   * @param {function(string)=} opts.onToast
   */
  function bind(opts) {
    opts = opts || {};
    var viewport = opts.viewport;
    var target = opts.target;
    var hud = opts.hud || null;
    var onToast = typeof opts.onToast === 'function' ? opts.onToast : function () {};
    if (!viewport || !target) return null;

    var scale = 1;
    var tx = 0;
    var ty = 0;
    var minScale = 1;
    var maxScale = 3.5;
    var active = false;
    var pointers = new Map();
    var pinchStartDist = 0;
    var pinchStartScale = 1;
    var panStart = null;

    function apply() {
      target.style.transformOrigin = 'center center';
      target.style.transform = 'translate(' + tx + 'px,' + ty + 'px) scale(' + scale + ')';
      target.style.willChange = scale > 1.01 ? 'transform' : 'auto';
      if (hud) {
        var lbl = hud.querySelector('[data-zoom-label]');
        if (lbl) lbl.textContent = Math.round(scale * 100) + '%';
      }
    }

    function reset() {
      scale = 1;
      tx = 0;
      ty = 0;
      apply();
    }

    function setScale(next, cx, cy) {
      var prev = scale;
      scale = clamp(next, minScale, maxScale);
      if (typeof cx === 'number' && typeof cy === 'number' && prev > 0) {
        // keep focal point roughly stable
        var vr = viewport.getBoundingClientRect();
        var ox = cx - (vr.left + vr.width / 2) - tx;
        var oy = cy - (vr.top + vr.height / 2) - ty;
        var ratio = scale / prev;
        tx -= ox * (ratio - 1);
        ty -= oy * (ratio - 1);
      }
      if (scale <= 1.02) {
        scale = 1;
        tx = 0;
        ty = 0;
      }
      apply();
    }

    function distance(a, b) {
      var dx = a.clientX - b.clientX;
      var dy = a.clientY - b.clientY;
      return Math.sqrt(dx * dx + dy * dy);
    }

    function onPointerDown(e) {
      if (!active) return;
      pointers.set(e.pointerId, e);
      viewport.setPointerCapture && viewport.setPointerCapture(e.pointerId);
      if (pointers.size === 2) {
        var pts = Array.from(pointers.values());
        pinchStartDist = distance(pts[0], pts[1]) || 1;
        pinchStartScale = scale;
        panStart = null;
      } else if (pointers.size === 1 && scale > 1.02) {
        panStart = { x: e.clientX - tx, y: e.clientY - ty };
      }
    }

    function onPointerMove(e) {
      if (!active || !pointers.has(e.pointerId)) return;
      pointers.set(e.pointerId, e);
      if (pointers.size === 2) {
        var pts = Array.from(pointers.values());
        var dist = distance(pts[0], pts[1]) || 1;
        var midX = (pts[0].clientX + pts[1].clientX) / 2;
        var midY = (pts[0].clientY + pts[1].clientY) / 2;
        setScale(pinchStartScale * (dist / pinchStartDist), midX, midY);
        if (e.cancelable) e.preventDefault();
      } else if (pointers.size === 1 && panStart && scale > 1.02) {
        tx = e.clientX - panStart.x;
        ty = e.clientY - panStart.y;
        apply();
        if (e.cancelable) e.preventDefault();
      }
    }

    function onPointerUp(e) {
      pointers.delete(e.pointerId);
      if (pointers.size < 2) {
        pinchStartDist = 0;
      }
      if (pointers.size === 0) panStart = null;
      if (pointers.size === 1) {
        var only = Array.from(pointers.values())[0];
        panStart = { x: only.clientX - tx, y: only.clientY - ty };
      }
    }

    viewport.style.touchAction = 'none';
    viewport.addEventListener('pointerdown', onPointerDown);
    viewport.addEventListener('pointermove', onPointerMove, { passive: false });
    viewport.addEventListener('pointerup', onPointerUp);
    viewport.addEventListener('pointercancel', onPointerUp);
    viewport.addEventListener('lostpointercapture', onPointerUp);

    if (hud) {
      var zin = hud.querySelector('[data-zoom-in]');
      var zout = hud.querySelector('[data-zoom-out]');
      var zreset = hud.querySelector('[data-zoom-reset]');
      if (zin) zin.addEventListener('click', function () { setScale(scale + 0.35); });
      if (zout) zout.addEventListener('click', function () { setScale(scale - 0.35); });
      if (zreset) zreset.addEventListener('click', function () { reset(); onToast('تمت إعادة العرض'); });
    }

    function setActive(on) {
      active = !!on;
      if (hud) {
        if (active) hud.classList.add('is-on');
        else hud.classList.remove('is-on');
      }
      if (!active) reset();
      else apply();
    }

    // Useful on phones even before share detection
    var coarse = false;
    try {
      coarse = !!(global.matchMedia && global.matchMedia('(pointer: coarse)').matches);
    } catch (e) {}
    if (coarse) setActive(true);

    return {
      setActive: setActive,
      isActive: function () { return active; },
      reset: reset,
      setScale: setScale,
      getScale: function () { return scale; },
    };
  }

  global.MxClassroomShareZoom = { bind: bind };
})(typeof window !== 'undefined' ? window : this);
