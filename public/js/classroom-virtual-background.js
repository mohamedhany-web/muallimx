/**
 * Muallimx Classroom — Virtual backgrounds via Jitsi External API
 * Uses setBlurredBackground / setVirtualBackground (server-side ML tracking).
 */
(function (global) {
  'use strict';

  var STORAGE_KEY = 'mx_classroom_virtual_bg_v1';

  function svgDataUrl(svg) {
    return 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(String(svg).trim());
  }

  // خلفيات مضمّنة (data URL) حتى تعمل حتى لو /images أعطى 404 على السيرفر
  var PRESETS = [
    {
      id: 'soft-blue',
      label: 'أزرق هادئ',
      file: 'soft-blue.svg',
      svg: '<svg xmlns="http://www.w3.org/2000/svg" width="1280" height="720" viewBox="0 0 1280 720"><defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#0f172a"/><stop offset="55%" stop-color="#1e3a5f"/><stop offset="100%" stop-color="#0ea5e9"/></linearGradient></defs><rect width="1280" height="720" fill="url(#g)"/><circle cx="1040" cy="120" r="180" fill="#38bdf8" opacity="0.18"/><circle cx="180" cy="560" r="220" fill="#6366f1" opacity="0.2"/></svg>',
    },
    {
      id: 'fresh-green',
      label: 'أخضر منعش',
      file: 'fresh-green.svg',
      svg: '<svg xmlns="http://www.w3.org/2000/svg" width="1280" height="720" viewBox="0 0 1280 720"><defs><linearGradient id="g" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#ecfdf5"/><stop offset="45%" stop-color="#a7f3d0"/><stop offset="100%" stop-color="#065f46"/></linearGradient></defs><rect width="1280" height="720" fill="url(#g)"/><ellipse cx="640" cy="620" rx="520" ry="120" fill="#047857" opacity="0.35"/><circle cx="220" cy="180" r="90" fill="#fef08a" opacity="0.45"/></svg>',
    },
    {
      id: 'violet-dusk',
      label: 'بنفسجي',
      file: 'violet-dusk.svg',
      svg: '<svg xmlns="http://www.w3.org/2000/svg" width="1280" height="720" viewBox="0 0 1280 720"><defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#1e1b4b"/><stop offset="50%" stop-color="#4c1d95"/><stop offset="100%" stop-color="#db2777"/></linearGradient></defs><rect width="1280" height="720" fill="url(#g)"/><circle cx="200" cy="160" r="140" fill="#c084fc" opacity="0.25"/><circle cx="1100" cy="520" r="200" fill="#f472b6" opacity="0.22"/></svg>',
    },
    {
      id: 'warm-sunset',
      label: 'غروب',
      file: 'warm-sunset.svg',
      svg: '<svg xmlns="http://www.w3.org/2000/svg" width="1280" height="720" viewBox="0 0 1280 720"><defs><linearGradient id="g" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#fff7ed"/><stop offset="40%" stop-color="#fdba74"/><stop offset="100%" stop-color="#9a3412"/></linearGradient></defs><rect width="1280" height="720" fill="url(#g)"/><circle cx="980" cy="140" r="110" fill="#fde68a" opacity="0.55"/><rect x="0" y="520" width="1280" height="200" fill="#7c2d12" opacity="0.28"/></svg>',
    },
    {
      id: 'classroom-board',
      label: 'فصل دراسي',
      file: 'classroom-board.svg',
      svg: '<svg xmlns="http://www.w3.org/2000/svg" width="1280" height="720" viewBox="0 0 1280 720"><defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="0"><stop offset="0%" stop-color="#111827"/><stop offset="100%" stop-color="#374151"/></linearGradient></defs><rect width="1280" height="720" fill="url(#g)"/><rect x="80" y="80" width="1120" height="560" rx="28" fill="#1f2937" stroke="#4b5563" stroke-width="4"/><rect x="140" y="140" width="640" height="360" rx="12" fill="#0ea5e9" opacity="0.25"/><rect x="820" y="140" width="320" height="160" rx="12" fill="#22c55e" opacity="0.2"/><rect x="820" y="340" width="320" height="160" rx="12" fill="#f59e0b" opacity="0.18"/></svg>',
    },
    {
      id: 'office-light',
      label: 'مكتب',
      file: 'office-light.svg',
      svg: '<svg xmlns="http://www.w3.org/2000/svg" width="1280" height="720" viewBox="0 0 1280 720"><defs><linearGradient id="g" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#f8fafc"/><stop offset="100%" stop-color="#cbd5e1"/></linearGradient></defs><rect width="1280" height="720" fill="url(#g)"/><rect x="0" y="0" width="1280" height="90" fill="#e2e8f0"/><rect x="60" y="160" width="360" height="480" rx="8" fill="#fff" stroke="#94a3b8" stroke-width="3"/><rect x="460" y="160" width="360" height="480" rx="8" fill="#fff" stroke="#94a3b8" stroke-width="3"/><rect x="860" y="160" width="360" height="480" rx="8" fill="#fff" stroke="#94a3b8" stroke-width="3"/><rect x="0" y="640" width="1280" height="80" fill="#64748b" opacity="0.35"/></svg>',
    },
    {
      id: 'ocean-wave',
      label: 'محيط',
      file: 'ocean-wave.svg',
      svg: '<svg xmlns="http://www.w3.org/2000/svg" width="1280" height="720" viewBox="0 0 1280 720"><defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#082f49"/><stop offset="50%" stop-color="#0e7490"/><stop offset="100%" stop-color="#67e8f9"/></linearGradient></defs><rect width="1280" height="720" fill="url(#g)"/><path d="M0 480 Q320 400 640 480 T1280 480 V720 H0 Z" fill="#155e75" opacity="0.45"/><path d="M0 540 Q320 480 640 540 T1280 540 V720 H0 Z" fill="#0891b2" opacity="0.35"/></svg>',
    },
    {
      id: 'soft-rose',
      label: 'وردي',
      file: 'soft-rose.svg',
      svg: '<svg xmlns="http://www.w3.org/2000/svg" width="1280" height="720" viewBox="0 0 1280 720"><defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#fdf2f8"/><stop offset="40%" stop-color="#fbcfe8"/><stop offset="100%" stop-color="#9d174d"/></linearGradient></defs><rect width="1280" height="720" fill="url(#g)"/><circle cx="240" cy="200" r="160" fill="#fb7185" opacity="0.25"/><circle cx="980" cy="480" r="220" fill="#be185d" opacity="0.22"/></svg>',
    },
  ];

  function assetBase() {
    var meta = document.querySelector('meta[name="mx-asset-base"]');
    if (meta && meta.content) return meta.content.replace(/\/$/, '');
    if (typeof global.MX_ASSET_BASE === 'string' && global.MX_ASSET_BASE) {
      return global.MX_ASSET_BASE.replace(/\/$/, '');
    }
    return '';
  }

  function presetPreviewUrl(preset) {
    if (preset && preset.svg) return svgDataUrl(preset.svg);
    if (preset && preset.file) return assetBase() + '/images/classroom-backgrounds/' + preset.file;
    return '';
  }

  function loadState() {
    try {
      var raw = localStorage.getItem(STORAGE_KEY);
      if (!raw) return { type: 'none' };
      var parsed = JSON.parse(raw);
      if (!parsed || typeof parsed !== 'object') return { type: 'none' };
      return parsed;
    } catch (e) {
      return { type: 'none' };
    }
  }

  function saveState(state) {
    try {
      localStorage.setItem(STORAGE_KEY, JSON.stringify(state || { type: 'none' }));
    } catch (e) {}
  }

  function getApi(api) {
    return api || global.__mxClassroomJitsiApi || null;
  }

  function exec(api, cmd, a, b) {
    var j = getApi(api);
    if (!j || typeof j.executeCommand !== 'function') {
      throw new Error('Jitsi API غير جاهز');
    }
    if (typeof b === 'undefined') {
      j.executeCommand(cmd, a);
    } else {
      j.executeCommand(cmd, a, b);
    }
  }

  function urlToJpegDataUrl(url, maxW, maxH) {
    maxW = maxW || 1280;
    maxH = maxH || 720;
    return new Promise(function (resolve, reject) {
      var img = new Image();
      if (String(url).indexOf('http') === 0) {
        img.crossOrigin = 'anonymous';
      }
      img.onload = function () {
        try {
          var scale = Math.min(maxW / img.naturalWidth, maxH / img.naturalHeight, 1);
          var w = Math.max(2, Math.round((img.naturalWidth || maxW) * scale));
          var h = Math.max(2, Math.round((img.naturalHeight || maxH) * scale));
          if (!img.naturalWidth) {
            w = maxW;
            h = maxH;
          }
          var canvas = document.createElement('canvas');
          canvas.width = w;
          canvas.height = h;
          var ctx = canvas.getContext('2d');
          ctx.fillStyle = '#111827';
          ctx.fillRect(0, 0, w, h);
          ctx.drawImage(img, 0, 0, w, h);
          resolve(canvas.toDataURL('image/jpeg', 0.9));
        } catch (err) {
          reject(err);
        }
      };
      img.onerror = function () {
        reject(new Error('تعذر تحميل صورة الخلفية'));
      };
      img.src = url;
    });
  }

  function fileToJpegDataUrl(file) {
    return new Promise(function (resolve, reject) {
      if (!file || !/^image\//.test(file.type || '')) {
        reject(new Error('اختر ملف صورة صالحاً'));
        return;
      }
      if (file.size > 8 * 1024 * 1024) {
        reject(new Error('حجم الصورة كبير جداً (الحد 8 ميجابايت)'));
        return;
      }
      var reader = new FileReader();
      reader.onload = function () {
        var dataUrl = String(reader.result || '');
        var img = new Image();
        img.onload = function () {
          try {
            var scale = Math.min(1280 / img.naturalWidth, 720 / img.naturalHeight, 1);
            var w = Math.max(2, Math.round(img.naturalWidth * scale));
            var h = Math.max(2, Math.round(img.naturalHeight * scale));
            var canvas = document.createElement('canvas');
            canvas.width = w;
            canvas.height = h;
            var ctx = canvas.getContext('2d');
            ctx.drawImage(img, 0, 0, w, h);
            resolve(canvas.toDataURL('image/jpeg', 0.9));
          } catch (err) {
            reject(err);
          }
        };
        img.onerror = function () {
          reject(new Error('تعذر قراءة الصورة'));
        };
        img.src = dataUrl;
      };
      reader.onerror = function () {
        reject(new Error('تعذر قراءة الملف'));
      };
      reader.readAsDataURL(file);
    });
  }

  async function clearBackground(api) {
    var j = getApi(api);
    try {
      exec(j, 'setBlurredBackground', 'none');
    } catch (e1) {}
    try {
      exec(j, 'setVirtualBackground', false, '');
    } catch (e2) {
      try {
        exec(j, 'setVirtualBackground', false);
      } catch (e3) {}
    }
    saveState({ type: 'none' });
  }

  async function applyBlur(api, blurType) {
    var type = blurType === 'slight-blur' ? 'slight-blur' : 'blur';
    try {
      try {
        exec(api, 'setVirtualBackground', false, '');
      } catch (e0) {}
      exec(api, 'setBlurredBackground', type);
      saveState({ type: 'blur', blur: type });
    } catch (err) {
      try {
        exec(api, 'toggleVirtualBackgroundDialog');
      } catch (e2) {}
      throw err;
    }
  }

  async function applyImageDataUrl(api, dataUrl, meta) {
    try {
      try {
        exec(api, 'setBlurredBackground', 'none');
      } catch (e0) {}
      exec(api, 'setVirtualBackground', true, dataUrl);
      var toSave = { type: 'image' };
      if (meta && meta.presetId) toSave.presetId = meta.presetId;
      if (meta && meta.custom) toSave.custom = true;
      // لا نخزّن dataUrl الكامل في localStorage (حجم كبير)
      saveState(toSave);
    } catch (err) {
      try {
        exec(api, 'toggleVirtualBackgroundDialog');
      } catch (e2) {}
      throw err;
    }
  }

  async function applyPreset(api, presetId) {
    var preset = PRESETS.find(function (p) {
      return p.id === presetId;
    });
    if (!preset) throw new Error('خلفية غير معروفة');
    var src = presetPreviewUrl(preset);
    var dataUrl = await urlToJpegDataUrl(src);
    await applyImageDataUrl(api, dataUrl, { presetId: preset.id });
  }

  async function applyCustomFile(api, file) {
    var dataUrl = await fileToJpegDataUrl(file);
    await applyImageDataUrl(api, dataUrl, { custom: true });
  }

  async function restoreSaved(api) {
    var state = loadState();
    if (!state || state.type === 'none') return false;
    try {
      if (state.type === 'blur') {
        await applyBlur(api, state.blur || 'blur');
        return true;
      }
      if (state.type === 'image' && state.presetId) {
        await applyPreset(api, state.presetId);
        return true;
      }
    } catch (e) {
      console.warn('restore virtual background failed', e);
    }
    return false;
  }

  function isVideoLikelyMuted(api) {
    var j = getApi(api);
    if (!j) return true;
    try {
      if (typeof j.isVideoMuted === 'function') {
        var v = j.isVideoMuted();
        if (v && typeof v.then === 'function') return null; // async unknown
        return !!v;
      }
    } catch (e) {}
    return null;
  }

  function buildPanelHtml(theme) {
    theme = theme || 'light';
    var presetHtml = PRESETS.map(function (p) {
      return (
        '<button type="button" class="mx-vbg-preset" data-mx-vbg-preset="' +
        p.id +
        '" title="' +
        p.label +
        '">' +
        '<img src="' +
        presetPreviewUrl(p) +
        '" alt="' +
        p.label +
        '" loading="lazy">' +
        '<span>' +
        p.label +
        '</span></button>'
      );
    }).join('');

    return (
      '<div class="mx-vbg-panel' +
      (theme === 'dark' ? ' is-dark' : '') +
      '" id="mx-vbg-panel" role="dialog" aria-label="خلفيات الكاميرا" hidden>' +
      '<div class="mx-vbg-head">' +
      '<strong>خلفية الكاميرا</strong>' +
      '<button type="button" class="mx-vbg-close" id="mx-vbg-close" aria-label="إغلاق">&times;</button>' +
      '</div>' +
      '<p class="mx-vbg-hint">يعمل التتبع عبر Jitsi (أفضل على Chrome/Edge). افتح الكاميرا أولاً.</p>' +
      '<div class="mx-vbg-actions">' +
      '<button type="button" class="mx-vbg-chip" data-mx-vbg="none">بدون خلفية</button>' +
      '<button type="button" class="mx-vbg-chip" data-mx-vbg="slight-blur">تمويه خفيف</button>' +
      '<button type="button" class="mx-vbg-chip" data-mx-vbg="blur">تمويه قوي</button>' +
      '</div>' +
      '<div class="mx-vbg-grid">' +
      presetHtml +
      '</div>' +
      '<label class="mx-vbg-upload">' +
      '<input type="file" id="mx-vbg-file" accept="image/*" hidden>' +
      '<span><i class="fas fa-upload"></i> رفع صورة مخصصة</span>' +
      '</label>' +
      '<button type="button" class="mx-vbg-fallback" id="mx-vbg-fallback">فتح نافذة Jitsi للخلفيات</button>' +
      '</div>'
    );
  }

  /**
   * @param {object} opts
   * @param {function(): any} opts.getApi
   * @param {HTMLElement} opts.mountEl - parent to append panel
   * @param {HTMLElement} opts.toggleBtn
   * @param {string} [opts.theme] light|dark
   * @param {function(string): void} [opts.onToast]
   * @param {function(): Promise<boolean>|boolean} [opts.ensureCameraOn]
   */
  function bindUi(opts) {
    opts = opts || {};
    var mountEl = opts.mountEl || document.body;
    var toggleBtn = opts.toggleBtn;
    var theme = opts.theme || 'light';
    var toast = typeof opts.onToast === 'function' ? opts.onToast : function () {};
    var ensureCameraOn = opts.ensureCameraOn;

    if (!document.getElementById('mx-vbg-panel')) {
      var wrap = document.createElement('div');
      wrap.innerHTML = buildPanelHtml(theme);
      mountEl.appendChild(wrap.firstChild);
    }
    var panel = document.getElementById('mx-vbg-panel');
    var closeBtn = document.getElementById('mx-vbg-close');
    var fileInput = document.getElementById('mx-vbg-file');
    var fallbackBtn = document.getElementById('mx-vbg-fallback');
    var busy = false;

    function setOpen(open) {
      if (!panel) return;
      if (open) {
        panel.hidden = false;
        panel.classList.add('is-open');
      } else {
        panel.classList.remove('is-open');
        panel.hidden = true;
      }
      if (toggleBtn) {
        toggleBtn.classList.toggle('is-active', !!open);
        toggleBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
      }
    }

    function api() {
      return typeof opts.getApi === 'function' ? opts.getApi() : getApi();
    }

    async function beforeApply() {
      if (!api()) {
        toast('انضم للغرفة أولاً');
        return false;
      }
      if (typeof ensureCameraOn === 'function') {
        var ok = await ensureCameraOn();
        if (!ok) {
          toast('افتح الكاميرا أولاً ثم اختر الخلفية');
          return false;
        }
      } else {
        var muted = isVideoLikelyMuted(api());
        if (muted === true) {
          toast('افتح الكاميرا أولاً ثم اختر الخلفية');
          return false;
        }
      }
      return true;
    }

    async function run(fn) {
      if (busy) return;
      busy = true;
      try {
        if (!(await beforeApply())) return;
        await fn();
        toast('تم تطبيق الخلفية');
        highlightActive();
      } catch (err) {
        console.warn(err);
        toast((err && err.message) || 'تعذر تطبيق الخلفية على هذا السيرفر');
      } finally {
        busy = false;
      }
    }

    function highlightActive() {
      var state = loadState();
      panel.querySelectorAll('.mx-vbg-chip, .mx-vbg-preset').forEach(function (el) {
        el.classList.remove('is-selected');
      });
      if (!state || state.type === 'none') {
        var none = panel.querySelector('[data-mx-vbg="none"]');
        if (none) none.classList.add('is-selected');
        return;
      }
      if (state.type === 'blur') {
        var b = panel.querySelector('[data-mx-vbg="' + (state.blur || 'blur') + '"]');
        if (b) b.classList.add('is-selected');
        return;
      }
      if (state.presetId) {
        var p = panel.querySelector('[data-mx-vbg-preset="' + state.presetId + '"]');
        if (p) p.classList.add('is-selected');
      }
    }

    if (toggleBtn) {
      toggleBtn.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        setOpen(panel.hidden || !panel.classList.contains('is-open'));
        highlightActive();
      });
    }
    if (closeBtn) closeBtn.addEventListener('click', function () { setOpen(false); });

    panel.querySelectorAll('[data-mx-vbg]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var kind = btn.getAttribute('data-mx-vbg');
        run(async function () {
          if (kind === 'none') await clearBackground(api());
          else await applyBlur(api(), kind);
        });
      });
    });

    panel.querySelectorAll('[data-mx-vbg-preset]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var id = btn.getAttribute('data-mx-vbg-preset');
        run(async function () {
          await applyPreset(api(), id);
        });
      });
    });

    if (fileInput) {
      var uploadLabel = panel.querySelector('.mx-vbg-upload');
      if (uploadLabel) {
        uploadLabel.addEventListener('click', function () {
          fileInput.click();
        });
      }
      fileInput.addEventListener('change', function () {
        var file = fileInput.files && fileInput.files[0];
        fileInput.value = '';
        if (!file) return;
        run(async function () {
          await applyCustomFile(api(), file);
        });
      });
    }

    if (fallbackBtn) {
      fallbackBtn.addEventListener('click', function () {
        try {
          exec(api(), 'toggleVirtualBackgroundDialog');
          toast('تم فتح نافذة الخلفيات');
        } catch (err) {
          toast('الخلفية الافتراضية غير متاحة على خادم الاجتماع حالياً');
        }
      });
    }

    document.addEventListener('mousedown', function (e) {
      if (!panel || panel.hidden) return;
      if (panel.contains(e.target)) return;
      if (toggleBtn && toggleBtn.contains(e.target)) return;
      setOpen(false);
    }, true);

    highlightActive();

    return {
      setOpen: setOpen,
      restoreSaved: function () {
        return restoreSaved(api());
      },
      clear: function () {
        return clearBackground(api());
      },
    };
  }

  global.MxClassroomVirtualBackground = {
    PRESETS: PRESETS,
    loadState: loadState,
    saveState: saveState,
    clearBackground: clearBackground,
    applyBlur: applyBlur,
    applyPreset: applyPreset,
    applyCustomFile: applyCustomFile,
    restoreSaved: restoreSaved,
    bindUi: bindUi,
    presetPreviewUrl: presetPreviewUrl,
  };
})(typeof window !== 'undefined' ? window : this);
