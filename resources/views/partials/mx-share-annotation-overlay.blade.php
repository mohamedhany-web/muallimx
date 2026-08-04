{{--
  رسم فوق الشاشة المشتركة: قلم + ممحاة للطالب، ومشاهدة + مسح للكل للمضيف.
  mxAnnRole: student_emit | viewer_poll | classroom_guest_emit
--}}
@php
    $mxAnnRole = $mxAnnRole ?? 'student_emit';
    $mxAnnPostUrl = $mxAnnPostUrl ?? '';
    $mxAnnPollUrl = $mxAnnPollUrl ?? '';
    $mxAnnClearUrl = $mxAnnClearUrl ?? '';
@endphp
<style>
    #mx-share-ann-layer {
        pointer-events: none;
        position: absolute;
        inset: 0;
        z-index: 8;
    }
    #mx-share-ann-layer.mx-share-ann-drawing { pointer-events: auto; }
    #mx-share-ann-layer.mx-share-ann-drawing #mx-share-ann-canvas { touch-action: none; cursor: crosshair; }
    #mx-share-ann-toolbar { pointer-events: auto; }
    #mx-share-ann-canvas {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        display: block;
    }
</style>
<div id="mx-share-ann-layer" class="absolute inset-0 z-[8] hidden"
     data-role="{{ $mxAnnRole }}"
     data-post-url="{{ e($mxAnnPostUrl) }}"
     data-poll-url="{{ e($mxAnnPollUrl) }}"
     data-clear-url="{{ e($mxAnnClearUrl) }}"
     data-guest-token="">
    <canvas id="mx-share-ann-canvas" class="absolute inset-0 w-full h-full block"></canvas>
    <div id="mx-share-ann-toolbar" class="absolute bottom-3 left-1/2 -translate-x-1/2 flex flex-wrap items-center justify-center gap-2 px-3 py-2 rounded-2xl bg-slate-900/92 border border-slate-600 shadow-xl max-w-[95vw]">
        <span class="text-slate-400 text-[11px] px-1 hidden sm:inline" id="mx-share-ann-hint">فوق عرض البث</span>
        <button type="button" data-mx-ann-tool="pen" class="mx-ann-tool-btn mx-ann-emit-only inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-amber-600/30 text-amber-100 text-xs font-semibold border border-amber-500/50 ring-2 ring-amber-400/60">
            <i class="fas fa-pen"></i> قلم
        </button>
        <button type="button" data-mx-ann-tool="eraser" class="mx-ann-tool-btn mx-ann-emit-only inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-slate-700 text-slate-200 text-xs font-semibold border border-slate-600">
            <i class="fas fa-eraser"></i> ممحاة
        </button>
        <button type="button" data-mx-ann-action="clear" class="mx-ann-emit-only inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-slate-700 text-slate-200 text-xs font-medium border border-slate-600">
            <i class="fas fa-trash-alt"></i> مسح كتابتي
        </button>
        <button type="button" data-mx-ann-action="clear-all" class="mx-ann-viewer-only hidden inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-rose-700/90 text-rose-50 text-xs font-semibold border border-rose-500/50">
            <i class="fas fa-broom"></i> مسح كل الرسومات
        </button>
        <button type="button" data-mx-ann-action="close" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-slate-800 text-slate-400 text-xs border border-slate-600" title="إخفاء الأدوات">
            <i class="fas fa-times"></i>
        </button>
    </div>
</div>
<script>
(function () {
    var layer = document.getElementById('mx-share-ann-layer');
    var canvas = document.getElementById('mx-share-ann-canvas');
    if (!layer || !canvas) return;

    var role = layer.getAttribute('data-role') || '';
    var postUrl = layer.getAttribute('data-post-url') || '';
    var pollUrl = layer.getAttribute('data-poll-url') || '';
    var clearUrl = layer.getAttribute('data-clear-url') || '';
    var ctx = canvas.getContext('2d');

    var polylines = [];
    var remoteLayers = {};
    var tool = 'pen';
    var drawing = false;
    var drawEnabled = false;
    var currentPts = null;
    var postTimer = null;
    var pollTimer = null;
    var allowed = false;
    var csrfMeta = document.querySelector('meta[name="csrf-token"]');
    var csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

    function isEmitter() {
        return role === 'student_emit' || role === 'classroom_guest_emit';
    }
    function isViewer() {
        return role === 'viewer_poll';
    }

    function resizeCanvas() {
        var rect = layer.getBoundingClientRect();
        var dpr = window.devicePixelRatio || 1;
        var w = Math.max(1, Math.floor(rect.width));
        var h = Math.max(1, Math.floor(rect.height));
        if (w < 2 || h < 2) return;
        canvas.width = Math.floor(w * dpr);
        canvas.height = Math.floor(h * dpr);
        canvas.style.width = w + 'px';
        canvas.style.height = h + 'px';
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
        paintAll();
    }

    function normToPx(nx, ny) {
        var rect = layer.getBoundingClientRect();
        return [nx * rect.width, ny * rect.height];
    }

    function pxToNorm(x, y) {
        var rect = layer.getBoundingClientRect();
        if (rect.width < 1 || rect.height < 1) return [0, 0];
        return [x / rect.width, y / rect.height];
    }

    function strokeLine(line, color, width) {
        if (!line || line.length < 2) return;
        ctx.beginPath();
        var p0 = normToPx(line[0][0], line[0][1]);
        ctx.moveTo(p0[0], p0[1]);
        for (var i = 1; i < line.length; i++) {
            var pi = normToPx(line[i][0], line[i][1]);
            ctx.lineTo(pi[0], pi[1]);
        }
        ctx.strokeStyle = color;
        ctx.lineWidth = width || 3;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
        ctx.stroke();
    }

    function hueFromKey(key) {
        var s = String(key);
        var h = 0;
        for (var i = 0; i < s.length; i++) h = (h * 31 + s.charCodeAt(i)) % 360;
        return h;
    }

    function paintAll() {
        var rect = layer.getBoundingClientRect();
        if (rect.width < 2 || rect.height < 2) return;
        ctx.clearRect(0, 0, rect.width, rect.height);
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';

        if (isViewer()) {
            Object.keys(remoteLayers).forEach(function (k) {
                var L = remoteLayers[k];
                if (!L || !L.polylines) return;
                var col = 'hsl(' + hueFromKey(k) + ', 82%, 62%)';
                L.polylines.forEach(function (line) {
                    strokeLine(line, col, 3.5);
                });
            });
            return;
        }

        // Emitter: local strokes (+ in-progress)
        polylines.forEach(function (line) {
            strokeLine(line, 'rgba(250, 204, 21, 0.95)', 3.5);
        });
        if (currentPts && currentPts.length > 1) {
            strokeLine(currentPts, 'rgba(250, 204, 21, 0.95)', 3.5);
        }
    }

    function distPointSeg(px, py, x1, y1, x2, y2) {
        var dx = x2 - x1, dy = y2 - y1;
        if (dx === 0 && dy === 0) return Math.hypot(px - x1, py - y1);
        var t = ((px - x1) * dx + (py - y1) * dy) / (dx * dx + dy * dy);
        t = Math.max(0, Math.min(1, t));
        var qx = x1 + t * dx, qy = y1 + t * dy;
        return Math.hypot(px - qx, py - qy);
    }

    function distPointPolyline(px, py, line) {
        var m = Infinity;
        for (var i = 1; i < line.length; i++) {
            var a = normToPx(line[i - 1][0], line[i - 1][1]);
            var b = normToPx(line[i][0], line[i][1]);
            var d = distPointSeg(px, py, a[0], a[1], b[0], b[1]);
            if (d < m) m = d;
        }
        return m;
    }

    function eraseAt(px, py, radius) {
        polylines = polylines.filter(function (line) {
            return distPointPolyline(px, py, line) > radius;
        });
    }

    function broadcastLocal() {
        if (typeof window.__mxShareAnnBroadcast === 'function') {
            try {
                window.__mxShareAnnBroadcast(polylines.slice());
            } catch (e) {}
        }
    }

    function schedulePost() {
        broadcastLocal();
        if (!isEmitter() || !postUrl || !allowed) return;
        if (postTimer) clearTimeout(postTimer);
        postTimer = setTimeout(function () {
            postTimer = null;
            var body = { polylines: polylines };
            var headers = {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            };
            if (role === 'classroom_guest_emit') {
                body.token = layer.getAttribute('data-guest-token') || '';
            }
            fetch(postUrl, { method: 'POST', headers: headers, body: JSON.stringify(body) }).catch(function () {});
        }, 220);
    }

    function setDrawActive(on) {
        drawEnabled = !!on;
        if (isEmitter()) {
            if (drawEnabled) layer.classList.add('mx-share-ann-drawing');
            else {
                layer.classList.remove('mx-share-ann-drawing');
                drawing = false;
                currentPts = null;
            }
        }
        // Viewer keeps toolbar interactive without drawing on canvas
        if (isViewer() && drawEnabled) {
            layer.classList.add('mx-share-ann-drawing');
            // Only toolbar should receive events — canvas stays non-blocking for laser unless drawing
            canvas.style.pointerEvents = 'none';
        }
    }

    function updateToolbarTools() {
        var btns = layer.querySelectorAll('.mx-ann-tool-btn');
        btns.forEach(function (b) {
            var t = b.getAttribute('data-mx-ann-tool');
            var on = t === tool;
            b.classList.toggle('ring-2', on);
            b.classList.toggle('ring-amber-400/60', on);
            b.classList.toggle('bg-amber-600/30', on && t === 'pen');
            b.classList.toggle('border-amber-500/50', on && t === 'pen');
            b.classList.toggle('bg-slate-700', !on || t === 'eraser');
        });
    }

    function configureRoleChrome() {
        layer.querySelectorAll('.mx-ann-emit-only').forEach(function (el) {
            el.classList.toggle('hidden', !isEmitter());
        });
        layer.querySelectorAll('.mx-ann-viewer-only').forEach(function (el) {
            el.classList.toggle('hidden', !isViewer());
        });
        var hint = document.getElementById('mx-share-ann-hint');
        if (hint) {
            hint.textContent = isViewer() ? 'رسومات الطلاب على الشاشة' : 'فوق عرض البث';
        }
    }

    function clearAllRemote(andPersist) {
        remoteLayers = {};
        paintAll();
        if (typeof window.__mxShareAnnBroadcastClear === 'function') {
            try { window.__mxShareAnnBroadcastClear(); } catch (e) {}
        }
        if (andPersist && clearUrl) {
            fetch(clearUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({})
            }).catch(function () {});
        }
    }

    function bindEmitter() {
        layer.querySelectorAll('[data-mx-ann-tool]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                tool = btn.getAttribute('data-mx-ann-tool') || 'pen';
                updateToolbarTools();
            });
        });
        var clearBtn = layer.querySelector('[data-mx-ann-action="clear"]');
        if (clearBtn) {
            clearBtn.addEventListener('click', function () {
                polylines = [];
                paintAll();
                schedulePost();
            });
        }
        layer.querySelector('[data-mx-ann-action="close"]').addEventListener('click', function () {
            setDrawActive(false);
            if (isEmitter()) {
                // keep layer visible strokes for host, but stop drawing
                layer.classList.remove('mx-share-ann-drawing');
            }
        });

        function pos(ev) {
            var rect = layer.getBoundingClientRect();
            var cx = ev.clientX, cy = ev.clientY;
            if (ev.touches && ev.touches[0]) {
                cx = ev.touches[0].clientX;
                cy = ev.touches[0].clientY;
            }
            return [cx - rect.left, cy - rect.top];
        }

        canvas.addEventListener('pointerdown', function (ev) {
            if (!drawEnabled || !allowed || !isEmitter()) return;
            ev.preventDefault();
            canvas.setPointerCapture(ev.pointerId);
            drawing = true;
            var p = pos(ev);
            if (tool === 'pen') {
                currentPts = [pxToNorm(p[0], p[1])];
            } else {
                eraseAt(p[0], p[1], 16);
                paintAll();
                schedulePost();
            }
        });
        canvas.addEventListener('pointermove', function (ev) {
            if (!drawEnabled || !allowed || !drawing || !isEmitter()) return;
            ev.preventDefault();
            var p = pos(ev);
            if (tool === 'pen' && currentPts) {
                currentPts.push(pxToNorm(p[0], p[1]));
                paintAll();
                // live preview to host while drawing
                if (typeof window.__mxShareAnnBroadcast === 'function') {
                    try {
                        window.__mxShareAnnBroadcast(polylines.concat([currentPts]));
                    } catch (e) {}
                }
            } else if (tool === 'eraser') {
                eraseAt(p[0], p[1], 16);
                paintAll();
                schedulePost();
            }
        });
        canvas.addEventListener('pointerup', function (ev) {
            if (!drawing) return;
            drawing = false;
            try { canvas.releasePointerCapture(ev.pointerId); } catch (e) {}
            if (tool === 'pen' && currentPts && currentPts.length > 1) {
                polylines.push(currentPts);
                if (polylines.length > 120) polylines.shift();
            }
            currentPts = null;
            paintAll();
            schedulePost();
        });
        updateToolbarTools();
    }

    function bindViewer() {
        var clearAllBtn = layer.querySelector('[data-mx-ann-action="clear-all"]');
        if (clearAllBtn) {
            clearAllBtn.addEventListener('click', function () {
                clearAllRemote(true);
            });
        }
        var closeBtn = layer.querySelector('[data-mx-ann-action="close"]');
        if (closeBtn) {
            closeBtn.addEventListener('click', function () {
                // Keep canvas visible; only hide toolbar chrome
                var tb = document.getElementById('mx-share-ann-toolbar');
                if (tb) tb.style.display = 'none';
                setDrawActive(false);
            });
        }
    }

    window.__mxShareAnnSetAllowed = function (on) {
        allowed = !!on;
        if (!allowed) {
            setDrawActive(false);
            layer.classList.add('hidden');
            if (isEmitter()) {
                polylines = [];
                paintAll();
                schedulePost();
            }
            return;
        }
        if (isEmitter()) {
            layer.classList.add('hidden');
            setDrawActive(false);
        } else if (isViewer()) {
            layer.classList.remove('hidden');
            var tb = document.getElementById('mx-share-ann-toolbar');
            if (tb) tb.style.display = '';
            requestAnimationFrame(function () {
                resizeCanvas();
                paintAll();
            });
        }
    };

    window.__mxShareAnnOpenToolbar = function () {
        if (!allowed) return;
        layer.classList.remove('hidden');
        var tb = document.getElementById('mx-share-ann-toolbar');
        if (tb) tb.style.display = '';
        setDrawActive(true);
        if (isViewer()) {
            canvas.style.pointerEvents = 'none';
        }
        requestAnimationFrame(function () {
            resizeCanvas();
            paintAll();
        });
    };

    window.__mxShareAnnSetGuestToken = function (tok) {
        layer.setAttribute('data-guest-token', tok || '');
    };

    /** LiveKit / poll: apply full layers map { key: { polylines } } */
    window.__mxShareAnnApplyRemoteLayers = function (layers) {
        if (!isViewer()) return;
        remoteLayers = layers && typeof layers === 'object' ? layers : {};
        paintAll();
    };

    /** LiveKit realtime: one participant layer */
    window.__mxShareAnnApplyRemoteLayer = function (key, polylinesIn) {
        if (!isViewer()) return;
        key = String(key || 'remote');
        if (!polylinesIn || !polylinesIn.length) {
            delete remoteLayers[key];
        } else {
            remoteLayers[key] = { polylines: polylinesIn, ts: Date.now() };
        }
        paintAll();
    };

    window.__mxShareAnnClearRemote = function () {
        if (!isViewer()) return;
        remoteLayers = {};
        paintAll();
    };

    window.__mxShareAnnForceLocalClear = function () {
        polylines = [];
        currentPts = null;
        paintAll();
        if (isEmitter() && allowed) {
            schedulePost();
        }
    };

    window.__mxShareAnnRemounted = function () {
        requestAnimationFrame(function () {
            resizeCanvas();
            paintAll();
        });
        setTimeout(function () {
            resizeCanvas();
            paintAll();
        }, 120);
    };

    configureRoleChrome();

    if (isEmitter()) {
        bindEmitter();
    } else if (isViewer()) {
        bindViewer();
        function poll() {
            if (!pollUrl || !allowed) return;
            fetch(pollUrl, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
                .then(function (r) { return r.ok ? r.json() : null; })
                .then(function (data) {
                    if (data && data.layers) {
                        remoteLayers = data.layers;
                        paintAll();
                    }
                })
                .catch(function () {});
        }
        poll();
        pollTimer = setInterval(poll, 900);
        window.addEventListener('beforeunload', function () {
            if (pollTimer) clearInterval(pollTimer);
        });
    }

    var ro = typeof ResizeObserver !== 'undefined' ? new ResizeObserver(function () { resizeCanvas(); }) : null;
    if (ro) ro.observe(layer);
    window.addEventListener('resize', function () { resizeCanvas(); });
    requestAnimationFrame(function () { resizeCanvas(); });
})();
</script>
