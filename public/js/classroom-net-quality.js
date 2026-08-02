/**
 * Muallimx Classroom — local network quality banner.
 * Shows a clear Arabic warning only when evidence points to the user's network
 * (Jitsi connectionQuality + Network Information API), after a short sustained dip.
 */
(function (global) {
  'use strict';

  var MSG =
    'اتصال الإنترنت غير مستقر — قد تنخفض جودة الصوت/الفيديو أو مشاركة الشاشة. تحقق من الشبكة أو جرّب شبكة أخرى.';

  function browserNetHint() {
    try {
      if (typeof navigator !== 'undefined' && navigator.onLine === false) return 'weak';
      var c = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
      if (!c) return 'unknown';
      if (c.saveData) return 'weak';
      var et = String(c.effectiveType || '');
      if (et === '2g' || et === 'slow-2g' || et === '3g') return 'weak';
      if (typeof c.rtt === 'number' && c.rtt >= 350) return 'weak';
      if (typeof c.downlink === 'number' && c.downlink > 0 && c.downlink < 1.2) return 'weak';
      // Strong local link signals — avoid blaming the user's network.
      if (
        typeof c.downlink === 'number' &&
        c.downlink >= 5 &&
        typeof c.rtt === 'number' &&
        c.rtt > 0 &&
        c.rtt < 120 &&
        (et === '4g' || et === '')
      ) {
        return 'strong';
      }
      return 'unknown';
    } catch (e) {
      return 'unknown';
    }
  }

  /**
   * @param {number} cq Jitsi connectionQuality 0–100
   * @returns {boolean}
   */
  function shouldBlameNetwork(cq) {
    var q = Number(cq);
    if (!isFinite(q) || q <= 0) return false;
    var hint = browserNetHint();
    if (hint === 'weak') return true;
    // Very poor Jitsi CQ is almost always local uplink/downlink.
    if (q < 25) return true;
    // Mid-poor + no strong-net evidence.
    if (q < 40 && hint !== 'strong') return true;
    // Strong browser net + only mild CQ dip → likely not "your Wi‑Fi" (skip blame).
    if (hint === 'strong' && q >= 25) return false;
    return false;
  }

  function levelFromCq(cq) {
    var q = Number(cq);
    if (!isFinite(q) || q <= 0) return 0;
    if (q >= 70) return 4;
    if (q >= 50) return 3;
    if (q >= 30) return 2;
    return 1;
  }

  function labelFromLevel(level) {
    if (level >= 4) return 'ممتاز';
    if (level === 3) return 'جيد';
    if (level === 2) return 'متوسط';
    if (level === 1) return 'ضعيف';
    return '—';
  }

  /**
   * @param {object} opts
   * @param {HTMLElement=} opts.bannerEl
   * @param {HTMLElement=} opts.qualityWrap
   * @param {HTMLElement=} opts.qualityLabel
   * @param {function(string)=} opts.onToast
   * @param {number=} opts.poorMs
   * @param {number=} opts.recoverMs
   * @param {number=} opts.toastCooldownMs
   */
  function createMonitor(opts) {
    opts = opts || {};
    var bannerEl = opts.bannerEl || null;
    var qualityWrap = opts.qualityWrap || null;
    var qualityLabel = opts.qualityLabel || null;
    var onToast = typeof opts.onToast === 'function' ? opts.onToast : null;
    var poorMs = opts.poorMs || 8000;
    var recoverMs = opts.recoverMs || 5000;
    var toastCooldownMs = opts.toastCooldownMs || 90000;

    var lastCq = 0;
    var poorSince = 0;
    var goodSince = 0;
    var bannerVisible = false;
    var lastToastAt = 0;

    function setBanner(on) {
      bannerVisible = !!on;
      if (!bannerEl) return;
      if (on) {
        bannerEl.hidden = false;
        bannerEl.setAttribute('aria-hidden', 'false');
        bannerEl.classList.add('is-visible');
        var text = bannerEl.querySelector('[data-mx-net-msg]');
        if (text) text.textContent = MSG;
      } else {
        bannerEl.hidden = true;
        bannerEl.setAttribute('aria-hidden', 'true');
        bannerEl.classList.remove('is-visible');
      }
    }

    function updateQualityUi(cq) {
      var level = levelFromCq(cq);
      if (qualityWrap) qualityWrap.setAttribute('data-level', String(level));
      if (qualityLabel) qualityLabel.textContent = labelFromLevel(level);
      if (qualityWrap) {
        qualityWrap.title =
          level <= 1
            ? 'جودة الاتصال ضعيفة — غالبًا بسبب الشبكة المحلية'
            : 'جودة الاتصال';
      }
    }

    function onConnectionQuality(e) {
      var cq = 0;
      try {
        if (e && typeof e.connectionQuality === 'number') cq = e.connectionQuality;
        else if (typeof e === 'number') cq = e;
      } catch (err) {}
      lastCq = cq;
      updateQualityUi(cq);

      var now = Date.now();
      var blame = shouldBlameNetwork(cq);

      if (blame) {
        goodSince = 0;
        if (!poorSince) poorSince = now;
        if (!bannerVisible && now - poorSince >= poorMs) {
          setBanner(true);
          if (onToast && now - lastToastAt >= toastCooldownMs) {
            lastToastAt = now;
            onToast(MSG);
          }
        }
      } else {
        poorSince = 0;
        if (bannerVisible) {
          if (!goodSince) goodSince = now;
          if (now - goodSince >= recoverMs) {
            setBanner(false);
            goodSince = 0;
          }
        } else {
          goodSince = 0;
        }
      }
    }

    return {
      onConnectionQuality: onConnectionQuality,
      shouldBlameNetwork: shouldBlameNetwork,
      browserNetHint: browserNetHint,
      message: MSG,
      getLastCq: function () { return lastCq; },
      isBannerVisible: function () { return bannerVisible; },
      hide: function () { setBanner(false); poorSince = 0; goodSince = 0; },
    };
  }

  global.MxClassroomNetQuality = {
    createMonitor: createMonitor,
    shouldBlameNetwork: shouldBlameNetwork,
    browserNetHint: browserNetHint,
    levelFromCq: levelFromCq,
    labelFromLevel: labelFromLevel,
    MESSAGE: MSG,
  };
})(typeof window !== 'undefined' ? window : globalThis);
