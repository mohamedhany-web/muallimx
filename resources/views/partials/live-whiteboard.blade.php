<style>
    .mx-whiteboard-toggle {
        position: fixed;
        left: 16px;
        bottom: 16px;
        z-index: 65;
    }
    .mx-whiteboard-panel {
        position: fixed;
        inset-inline-start: 16px;
        bottom: 76px;
        width: min(94vw, 980px);
        height: min(76vh, 680px);
        background: rgba(15, 23, 42, 0.96);
        border: 1px solid rgba(148, 163, 184, 0.3);
        border-radius: 16px;
        box-shadow: 0 24px 60px rgba(2, 6, 23, 0.55);
        display: none;
        flex-direction: column;
        overflow: hidden;
        z-index: 66;
        backdrop-filter: blur(4px);
    }
    .mx-whiteboard-panel.is-open { display: flex; }
    .mx-whiteboard-toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        padding: 10px;
        border-bottom: 1px solid rgba(148, 163, 184, 0.22);
        background: rgba(15, 23, 42, 0.9);
    }
    .mx-whiteboard-toolbar button,
    .mx-whiteboard-toolbar select,
    .mx-whiteboard-toolbar input[type="color"],
    .mx-whiteboard-toolbar input[type="range"] {
        border-radius: 10px;
        border: 1px solid rgba(148, 163, 184, 0.35);
        background: rgba(30, 41, 59, 0.95);
        color: #e2e8f0;
        height: 36px;
        padding: 0 10px;
        font-size: 13px;
    }
    .mx-whiteboard-toolbar button { cursor: pointer; }
    .mx-whiteboard-toolbar button.is-active {
        background: rgba(14, 116, 144, 0.9);
        border-color: rgba(103, 232, 249, 0.55);
        color: #f0fdfa;
    }
    .mx-whiteboard-canvas-wrap {
        position: relative;
        flex: 1;
        min-height: 220px;
        background: #ffffff;
    }
    .mx-whiteboard-canvas-wrap canvas {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
    }
    .mx-whiteboard-meta {
        color: #cbd5e1;
        font-size: 12px;
        padding: 8px 12px;
        border-top: 1px solid rgba(148, 163, 184, 0.22);
        background: rgba(15, 23, 42, 0.85);
    }
</style>

<button id="mx-whiteboard-toggle" type="button" class="mx-whiteboard-toggle inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-cyan-600 hover:bg-cyan-700 text-white text-sm font-semibold shadow-lg shadow-cyan-700/30">
    <i class="fas fa-chalkboard"></i>
    <span>Whiteboard+</span>
</button>

<section id="mx-whiteboard-panel" class="mx-whiteboard-panel" aria-label="لوحة كتابة متقدمة">
    <div class="mx-whiteboard-toolbar">
        <button type="button" data-tool="select" class="is-active"><i class="fas fa-mouse-pointer"></i> تحديد</button>
        <button type="button" data-tool="draw"><i class="fas fa-pen"></i> رسم</button>
        <button type="button" data-tool="eraser"><i class="fas fa-eraser"></i> ممحاة</button>
        <button type="button" data-tool="line"><i class="fas fa-minus"></i> خط</button>
        <button type="button" data-tool="rect"><i class="far fa-square"></i> مستطيل</button>
        <button type="button" data-tool="circle"><i class="far fa-circle"></i> دائرة</button>
        <button type="button" data-tool="triangle"><i class="fas fa-play fa-rotate-270"></i> مثلث</button>
        <button type="button" data-tool="arrow"><i class="fas fa-arrow-right"></i> سهم</button>
        <button type="button" data-tool="text"><i class="fas fa-font"></i> نص</button>
        <input type="color" id="mx-stroke-color" value="#0f172a" title="لون الحدود">
        <input type="color" id="mx-fill-color" value="#00000000" title="لون التعبئة">
        <label class="inline-flex items-center gap-1 text-slate-200 text-xs px-2">الحجم
            <input id="mx-stroke-width" type="range" min="1" max="24" value="3">
        </label>
        <button type="button" id="mx-undo"><i class="fas fa-rotate-left"></i></button>
        <button type="button" id="mx-redo"><i class="fas fa-rotate-right"></i></button>
        <button type="button" id="mx-clear"><i class="fas fa-trash"></i> مسح</button>
        <button type="button" id="mx-download"><i class="fas fa-download"></i> حفظ</button>
        <button type="button" id="mx-close"><i class="fas fa-xmark"></i></button>
    </div>
    <div id="mx-whiteboard-canvas-wrap" class="mx-whiteboard-canvas-wrap">
        <canvas id="mx-whiteboard-canvas"></canvas>
    </div>
    <div class="mx-whiteboard-meta">لوحة محلية متقدمة: رسم حر، تحديد وتحريك، أشكال، نص، تراجع/إعادة، وحفظ صورة.</div>
</section>

<script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.1/fabric.min.js"></script>
<script>
    (function () {
        var toggleBtn = document.getElementById('mx-whiteboard-toggle');
        var panel = document.getElementById('mx-whiteboard-panel');
        var closeBtn = document.getElementById('mx-close');
        var canvasEl = document.getElementById('mx-whiteboard-canvas');
        var wrap = document.getElementById('mx-whiteboard-canvas-wrap');
        if (!toggleBtn || !panel || !canvasEl || !wrap || typeof fabric === 'undefined') return;

        var canvas = new fabric.Canvas(canvasEl, { selection: true, preserveObjectStacking: true });
        var currentTool = 'select';
        var drawingObject = null;
        var startPoint = null;
        var undoStack = [];
        var redoStack = [];
        var isRestoring = false;

        function resizeCanvas() {
            var rect = wrap.getBoundingClientRect();
            canvas.setDimensions({ width: Math.max(200, rect.width), height: Math.max(180, rect.height) });
            canvas.requestRenderAll();
        }

        function saveState() {
            if (isRestoring) return;
            undoStack.push(JSON.stringify(canvas.toJSON()));
            if (undoStack.length > 80) undoStack.shift();
            redoStack = [];
        }

        function restoreState(fromStack, toStack) {
            if (!fromStack.length) return;
            isRestoring = true;
            toStack.push(JSON.stringify(canvas.toJSON()));
            var json = fromStack.pop();
            canvas.loadFromJSON(json, function() {
                canvas.renderAll();
                isRestoring = false;
            });
        }

        function hexToRgba(hex, alpha) {
            if (!hex || !/^#([A-Fa-f0-9]{6})$/.test(hex)) return 'rgba(0,0,0,' + alpha + ')';
            var n = parseInt(hex.slice(1), 16);
            var r = (n >> 16) & 255;
            var g = (n >> 8) & 255;
            var b = n & 255;
            return 'rgba(' + r + ',' + g + ',' + b + ',' + alpha + ')';
        }

        function strokeColor() { return document.getElementById('mx-stroke-color').value || '#0f172a'; }
        function fillColor() {
            var v = document.getElementById('mx-fill-color').value;
            return v === '#00000000' ? 'transparent' : v;
        }
        function strokeWidth() { return parseInt(document.getElementById('mx-stroke-width').value || '3', 10); }

        function activateTool(tool) {
            currentTool = tool;
            canvas.isDrawingMode = (tool === 'draw' || tool === 'eraser');
            canvas.selection = (tool === 'select');
            canvas.forEachObject(function(o) { o.selectable = (tool === 'select'); o.evented = (tool === 'select'); });
            if (tool === 'draw' || tool === 'eraser') {
                canvas.freeDrawingBrush = new fabric.PencilBrush(canvas);
                canvas.freeDrawingBrush.width = tool === 'eraser' ? Math.max(12, strokeWidth() * 3) : strokeWidth();
                canvas.freeDrawingBrush.color = tool === 'eraser' ? '#ffffff' : strokeColor();
            }
            document.querySelectorAll('#mx-whiteboard-panel [data-tool]').forEach(function (btn) {
                btn.classList.toggle('is-active', btn.getAttribute('data-tool') === tool);
            });
        }

        function createShape(tool, x, y) {
            var common = { left: x, top: y, stroke: strokeColor(), strokeWidth: strokeWidth(), fill: fillColor(), selectable: false, evented: false };
            if (tool === 'line' || tool === 'arrow') return new fabric.Line([x, y, x, y], common);
            if (tool === 'rect') return new fabric.Rect(Object.assign(common, { width: 1, height: 1 }));
            if (tool === 'circle') return new fabric.Ellipse(Object.assign(common, { rx: 1, ry: 1 }));
            if (tool === 'triangle') return new fabric.Triangle(Object.assign(common, { width: 1, height: 1 }));
            return null;
        }

        canvas.on('mouse:down', function (opt) {
            var p = canvas.getPointer(opt.e);
            if (currentTool === 'text') {
                var t = new fabric.IText('نص', {
                    left: p.x, top: p.y, fontSize: 24, fill: strokeColor(), fontFamily: 'IBM Plex Sans Arabic, sans-serif'
                });
                canvas.add(t).setActiveObject(t);
                saveState();
                return;
            }
            if (!['line', 'rect', 'circle', 'triangle', 'arrow'].includes(currentTool)) return;
            startPoint = p;
            drawingObject = createShape(currentTool, p.x, p.y);
            if (drawingObject) canvas.add(drawingObject);
        });

        canvas.on('mouse:move', function (opt) {
            if (!drawingObject || !startPoint) return;
            var p = canvas.getPointer(opt.e);
            if (currentTool === 'line' || currentTool === 'arrow') {
                drawingObject.set({ x2: p.x, y2: p.y });
            } else if (currentTool === 'rect' || currentTool === 'triangle') {
                drawingObject.set({
                    left: Math.min(startPoint.x, p.x),
                    top: Math.min(startPoint.y, p.y),
                    width: Math.abs(startPoint.x - p.x),
                    height: Math.abs(startPoint.y - p.y)
                });
            } else if (currentTool === 'circle') {
                drawingObject.set({
                    left: Math.min(startPoint.x, p.x),
                    top: Math.min(startPoint.y, p.y),
                    rx: Math.abs(startPoint.x - p.x) / 2,
                    ry: Math.abs(startPoint.y - p.y) / 2
                });
            }
            canvas.renderAll();
        });

        canvas.on('mouse:up', function () {
            if (!drawingObject) return;
            if (currentTool === 'arrow') {
                var line = drawingObject;
                var angle = Math.atan2(line.y2 - line.y1, line.x2 - line.x1);
                var head = new fabric.Triangle({
                    left: line.x2, top: line.y2, originX: 'center', originY: 'center',
                    width: 14 + strokeWidth(), height: 18 + strokeWidth(),
                    fill: strokeColor(), angle: (angle * 180 / Math.PI) + 90, selectable: false, evented: false
                });
                var group = new fabric.Group([line, head], { selectable: false, evented: false });
                canvas.remove(line);
                canvas.add(group);
            }
            drawingObject = null;
            startPoint = null;
            saveState();
        });

        canvas.on('path:created', saveState);
        canvas.on('object:modified', saveState);

        document.querySelectorAll('#mx-whiteboard-panel [data-tool]').forEach(function (btn) {
            btn.addEventListener('click', function () { activateTool(btn.getAttribute('data-tool')); });
        });

        document.getElementById('mx-stroke-color').addEventListener('input', function () {
            if (currentTool === 'draw' || currentTool === 'eraser') activateTool(currentTool);
            var active = canvas.getActiveObject();
            if (active && currentTool === 'select') {
                active.set('stroke', strokeColor());
                if (active.type === 'i-text') active.set('fill', strokeColor());
                canvas.requestRenderAll();
                saveState();
            }
        });

        document.getElementById('mx-fill-color').addEventListener('input', function () {
            var active = canvas.getActiveObject();
            if (active && currentTool === 'select' && active.type !== 'line' && active.type !== 'i-text') {
                active.set('fill', fillColor());
                canvas.requestRenderAll();
                saveState();
            }
        });

        document.getElementById('mx-stroke-width').addEventListener('input', function () {
            if (currentTool === 'draw' || currentTool === 'eraser') activateTool(currentTool);
            var active = canvas.getActiveObject();
            if (active && currentTool === 'select') {
                active.set('strokeWidth', strokeWidth());
                canvas.requestRenderAll();
                saveState();
            }
        });

        document.getElementById('mx-undo').addEventListener('click', function () { restoreState(undoStack, redoStack); });
        document.getElementById('mx-redo').addEventListener('click', function () { restoreState(redoStack, undoStack); });
        document.getElementById('mx-clear').addEventListener('click', function () {
            canvas.clear();
            canvas.setBackgroundColor('#ffffff', canvas.renderAll.bind(canvas));
            saveState();
        });
        document.getElementById('mx-download').addEventListener('click', function () {
            var link = document.createElement('a');
            link.href = canvas.toDataURL({ format: 'png', multiplier: 2 });
            link.download = 'muallimx-whiteboard.png';
            link.click();
        });

        function openBoard() {
            panel.classList.add('is-open');
            resizeCanvas();
            if (!canvas.backgroundColor) canvas.setBackgroundColor('#ffffff', canvas.renderAll.bind(canvas));
        }
        function closeBoard() { panel.classList.remove('is-open'); }
        toggleBtn.addEventListener('click', function () {
            if (panel.classList.contains('is-open')) closeBoard();
            else openBoard();
        });
        closeBtn.addEventListener('click', closeBoard);
        window.addEventListener('resize', resizeCanvas);

        activateTool('select');
        canvas.setBackgroundColor('#ffffff', canvas.renderAll.bind(canvas));
        saveState();
    })();
</script>
