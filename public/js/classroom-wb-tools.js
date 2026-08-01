/**
 * Muallimx Classroom — Permanent whiteboard tools bar (Excalidraw API)
 * Tools: select, pen (freedraw), text, eraser (partial), clear-all
 */
(function (global) {
  'use strict';

  var TOOLS = [
    { id: 'selection', type: 'selection', label: 'تحديد', icon: 'fa-hand-pointer', title: 'تحديد وتحريك العناصر' },
    { id: 'freedraw', type: 'freedraw', label: 'قلم', icon: 'fa-pen', title: 'قلم للكتابة والرسم' },
    { id: 'text', type: 'text', label: 'نص', icon: 'fa-font', title: 'إضافة نص' },
    { id: 'eraser', type: 'eraser', label: 'ممحاة', icon: 'fa-eraser', title: 'مسح جزء مما كُتب (استيكة)' },
  ];

  function setTool(api, type) {
    if (!api || typeof api.setActiveTool !== 'function') return false;
    try {
      api.setActiveTool({ type: type });
      return true;
    } catch (e1) {
      try {
        api.setActiveTool(type);
        return true;
      } catch (e2) {
        console.warn('MxWbTools setActiveTool', e2);
        return false;
      }
    }
  }

  function clearAll(api) {
    if (!api) return false;
    try {
      if (typeof api.resetScene === 'function') {
        api.resetScene({ resetLoadingState: true });
        return true;
      }
    } catch (e0) {}
    try {
      if (typeof api.updateScene === 'function') {
        api.updateScene({
          elements: [],
          commitToHistory: true,
        });
        return true;
      }
    } catch (e1) {
      console.warn('MxWbTools clearAll', e1);
    }
    return false;
  }

  function markActive(bar, toolId) {
    if (!bar) return;
    bar.querySelectorAll('[data-mx-wb-tool]').forEach(function (btn) {
      var on = btn.getAttribute('data-mx-wb-tool') === toolId;
      btn.classList.toggle('is-active', on);
      btn.setAttribute('aria-pressed', on ? 'true' : 'false');
    });
  }

  /**
   * @param {object} opts
   * @param {HTMLElement} opts.mountEl - container for the tools bar
   * @param {function(): any} opts.getApi - Excalidraw imperative API
   * @param {function(): boolean=} opts.canWrite
   * @param {function()=} opts.onAfterChange - e.g. sync push
   * @param {string=} opts.theme 'light' | 'dark'
   * @param {string=} opts.hintText
   */
  function bindToolbar(opts) {
    opts = opts || {};
    var mount = opts.mountEl;
    var getApi = opts.getApi || function () { return null; };
    var canWrite = typeof opts.canWrite === 'function' ? opts.canWrite : function () { return true; };
    var onAfterChange = typeof opts.onAfterChange === 'function' ? opts.onAfterChange : function () {};
    var theme = opts.theme === 'dark' ? 'dark' : 'light';
    var hintText = opts.hintText || 'سبورة مشتركة حية — نفس اللوح للمعلم والطلاب';

    if (!mount) return null;

    var bar = document.createElement('div');
    bar.className = 'mx-wb-tools' + (theme === 'dark' ? ' mx-wb-tools--dark' : '');
    bar.setAttribute('role', 'toolbar');
    bar.setAttribute('aria-label', 'أدوات السبورة');

    var hint = document.createElement('p');
    hint.className = 'mx-wb-tools-hint';
    hint.textContent = hintText;
    bar.appendChild(hint);

    var group = document.createElement('div');
    group.className = 'mx-wb-tools-group';
    bar.appendChild(group);

    TOOLS.forEach(function (t) {
      var btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'mx-wb-tool-btn' + (t.id === 'freedraw' ? ' is-active' : '');
      btn.setAttribute('data-mx-wb-tool', t.id);
      btn.setAttribute('aria-pressed', t.id === 'freedraw' ? 'true' : 'false');
      btn.title = t.title;
      btn.innerHTML = '<i class="fas ' + t.icon + '" aria-hidden="true"></i><span>' + t.label + '</span>';
      btn.addEventListener('click', function () {
        if (!canWrite()) return;
        var api = getApi();
        if (!api) return;
        if (setTool(api, t.type)) {
          markActive(bar, t.id);
        }
      });
      group.appendChild(btn);
    });

    var clearBtn = document.createElement('button');
    clearBtn.type = 'button';
    clearBtn.className = 'mx-wb-tool-btn mx-wb-tool-btn--danger';
    clearBtn.setAttribute('data-mx-wb-tool', 'clear');
    clearBtn.title = 'مسح كل ما على السبورة';
    clearBtn.innerHTML = '<i class="fas fa-trash" aria-hidden="true"></i><span>مسح الكل</span>';
    clearBtn.addEventListener('click', function () {
      if (!canWrite()) return;
      if (!confirm('مسح كل محتويات السبورة المشتركة؟ لا يمكن التراجع بسهولة.')) return;
      var api = getApi();
      if (!api) return;
      if (clearAll(api)) {
        markActive(bar, 'freedraw');
        setTool(api, 'freedraw');
        try { onAfterChange(); } catch (e) {}
      }
    });
    group.appendChild(clearBtn);

    mount.appendChild(bar);

    // Default to pen when board opens
    setTimeout(function () {
      var api = getApi();
      if (api && canWrite()) {
        setTool(api, 'freedraw');
        markActive(bar, 'freedraw');
      }
    }, 400);

    return {
      el: bar,
      setTool: function (type) {
        var api = getApi();
        if (!api || !canWrite()) return false;
        var ok = setTool(api, type);
        if (ok) markActive(bar, type === 'freedraw' ? 'freedraw' : type);
        return ok;
      },
      setEnabled: function (on) {
        bar.classList.toggle('is-disabled', !on);
        bar.querySelectorAll('button').forEach(function (b) {
          b.disabled = !on;
        });
      },
      destroy: function () {
        try { bar.remove(); } catch (e) {}
      },
    };
  }

  /**
   * Bind toolbar immediately (DOM does not need API). Retries default pen until API is ready.
   */
  function bindToolbarWhenReady(opts) {
    opts = opts || {};
    var onFail = typeof opts.onFail === 'function' ? opts.onFail : null;
    var handle = null;
    try {
      handle = bindToolbar(opts);
    } catch (err) {
      if (onFail) onFail(err);
      return null;
    }
    if (!handle) {
      if (onFail) onFail(new Error('bindToolbar returned null'));
      return null;
    }
    var attempts = 0;
    var maxAttempts = typeof opts.maxAttempts === 'number' ? opts.maxAttempts : 16;
    var delayMs = typeof opts.retryMs === 'number' ? opts.retryMs : 200;
    function nudgePen() {
      attempts += 1;
      var api = typeof opts.getApi === 'function' ? opts.getApi() : null;
      if (api) {
        setTool(api, 'freedraw');
        return;
      }
      if (attempts < maxAttempts) setTimeout(nudgePen, delayMs);
    }
    setTimeout(nudgePen, 100);
    return handle;
  }

  global.MxClassroomWbTools = {
    bindToolbar: bindToolbar,
    bindToolbarWhenReady: bindToolbarWhenReady,
    setTool: setTool,
    clearAll: clearAll,
    TOOLS: TOOLS,
  };
})(typeof window !== 'undefined' ? window : this);
