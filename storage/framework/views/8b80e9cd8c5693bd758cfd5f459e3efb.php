<?php $isInstructor = ($whiteboardRole ?? 'student') === 'instructor'; ?>
<style>
    /* ─── Floating Tools Dropdown ─────────────────────────── */
    #mx-tools-fab {
        position: fixed;
        left: 16px;
        bottom: 16px;
        z-index: 200;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 6px;
    }
    #mx-tools-menu {
        display: none;
        flex-direction: column;
        gap: 4px;
        background: rgba(15,23,42,0.97);
        border: 1px solid rgba(148,163,184,0.2);
        border-radius: 14px;
        padding: 8px;
        box-shadow: 0 20px 50px rgba(0,0,0,0.6);
        backdrop-filter: blur(8px);
        min-width: 190px;
    }
    #mx-tools-menu.is-open { display: flex; }
    .mx-menu-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 9px 14px;
        border-radius: 10px;
        background: rgba(30,41,59,0.7);
        color: #e2e8f0;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        border: 1px solid rgba(148,163,184,0.12);
        transition: background 0.15s, border-color 0.15s;
        white-space: nowrap;
    }
    .mx-menu-item:hover { background: rgba(51,65,85,0.9); border-color: rgba(148,163,184,0.3); }
    .mx-menu-item .mx-menu-icon {
        width: 30px; height: 30px;
        border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        font-size: 14px;
    }
    #mx-fab-main {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 9px 16px;
        border-radius: 14px;
        background: linear-gradient(135deg, #0ea5e9, #6366f1);
        color: white;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        border: none;
        box-shadow: 0 4px 20px rgba(99,102,241,0.4);
        transition: transform 0.15s, box-shadow 0.15s;
    }
    #mx-fab-main:hover { transform: translateY(-1px); box-shadow: 0 6px 24px rgba(99,102,241,0.5); }
    #mx-fab-chevron { transition: transform 0.2s; font-size: 10px; }
    #mx-fab-main.is-open #mx-fab-chevron { transform: rotate(180deg); }

    /* ─── Annotation Overlay (رسم فوق الفيديو) ────────────── */
    #mx-annotation-overlay {
        position: fixed;
        inset: 72px 0 0 0; /* below header */
        z-index: 190;
        display: none;
    }
    #mx-annotation-overlay.is-open { display: block; }
    #mx-annotation-canvas {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        cursor: crosshair;
    }
    #mx-annotation-toolbar {
        position: absolute;
        top: 12px;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 7px 12px;
        background: rgba(15,23,42,0.95);
        border: 1px solid rgba(148,163,184,0.25);
        border-radius: 50px;
        box-shadow: 0 8px 30px rgba(0,0,0,0.5);
        backdrop-filter: blur(8px);
        z-index: 1;
        flex-wrap: nowrap;
    }
    .mx-ann-btn {
        width: 34px; height: 34px;
        border-radius: 8px;
        border: 1px solid rgba(148,163,184,0.2);
        background: rgba(30,41,59,0.9);
        color: #cbd5e1;
        font-size: 14px;
        cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        transition: background 0.15s, color 0.15s;
    }
    .mx-ann-btn:hover, .mx-ann-btn.active {
        background: rgba(14,165,233,0.25);
        border-color: rgba(14,165,233,0.5);
        color: #38bdf8;
    }
    .mx-ann-sep { width: 1px; height: 22px; background: rgba(148,163,184,0.2); margin: 0 2px; }
    .mx-ann-color { width: 28px; height: 28px; border-radius: 6px; cursor: pointer; border: 2px solid rgba(148,163,184,0.3); }
    #mx-ann-size { width: 80px; accent-color: #0ea5e9; }
    #mx-ann-close-btn {
        padding: 0 14px;
        height: 34px;
        background: rgba(220,38,38,0.2);
        border: 1px solid rgba(220,38,38,0.4);
        color: #f87171;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        white-space: nowrap;
    }
    #mx-ann-close-btn:hover { background: rgba(220,38,38,0.4); }

    /* ─── Full Whiteboard Panel (السبورة) ─────────────────── */
    #mx-whiteboard-panel {
        position: fixed;
        left: 12px;
        bottom: 74px;
        width: min(96vw, 1060px);
        height: min(78vh, 700px);
        background: rgba(10,17,32,0.98);
        border: 1px solid rgba(148,163,184,0.18);
        border-radius: 18px;
        box-shadow: 0 32px 80px rgba(0,0,0,0.7);
        display: none;
        flex-direction: column;
        overflow: hidden;
        z-index: 195;
        backdrop-filter: blur(10px);
    }
    #mx-whiteboard-panel.is-open { display: flex; }

    /* Panel header */
    .mx-panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 14px;
        border-bottom: 1px solid rgba(148,163,184,0.15);
        background: rgba(15,23,42,0.95);
        flex-shrink: 0;
    }
    .mx-panel-title {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #e2e8f0;
        font-size: 13px;
        font-weight: 600;
    }
    .mx-panel-title i { color: #38bdf8; }

    /* Toolbar */
    .mx-wb-toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
        padding: 8px 12px;
        border-bottom: 1px solid rgba(148,163,184,0.12);
        background: rgba(15,23,42,0.9);
        flex-shrink: 0;
        align-items: center;
    }
    .mx-tool-group {
        display: flex;
        align-items: center;
        gap: 3px;
        padding: 0 6px;
        border-right: 1px solid rgba(148,163,184,0.15);
    }
    .mx-tool-group:last-child { border-right: none; }
    .mx-tool-btn {
        width: 34px; height: 34px;
        border-radius: 8px;
        border: 1px solid rgba(148,163,184,0.15);
        background: rgba(30,41,59,0.8);
        color: #94a3b8;
        font-size: 13px;
        cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        transition: all 0.15s;
        position: relative;
    }
    .mx-tool-btn:hover { background: rgba(51,65,85,0.9); color: #e2e8f0; border-color: rgba(148,163,184,0.3); }
    .mx-tool-btn.is-active { background: rgba(14,165,233,0.25); border-color: rgba(56,189,248,0.5); color: #38bdf8; }
    .mx-tool-btn[title]:hover::after {
        content: attr(title);
        position: absolute;
        bottom: -28px;
        left: 50%;
        transform: translateX(-50%);
        background: #1e293b;
        color: #e2e8f0;
        font-size: 10px;
        padding: 3px 7px;
        border-radius: 5px;
        white-space: nowrap;
        z-index: 10;
        pointer-events: none;
    }
    .mx-color-input {
        width: 30px; height: 30px;
        border-radius: 7px;
        border: 2px solid rgba(148,163,184,0.25);
        cursor: pointer;
        padding: 2px;
        background: transparent;
    }
    .mx-size-wrap {
        display: flex;
        align-items: center;
        gap: 5px;
        color: #94a3b8;
        font-size: 11px;
    }
    .mx-size-wrap input[type="range"] {
        width: 75px;
        accent-color: #0ea5e9;
    }
    .mx-bg-switcher {
        display: flex;
        align-items: center;
        gap: 3px;
    }
    .mx-bg-btn {
        width: 28px; height: 28px;
        border-radius: 6px;
        border: 2px solid rgba(148,163,184,0.2);
        cursor: pointer;
        transition: border-color 0.15s;
    }
    .mx-bg-btn:hover { border-color: rgba(148,163,184,0.5); }
    .mx-bg-btn.is-active { border-color: #38bdf8; }

    /* Canvas area */
    #mx-wb-canvas-wrap {
        flex: 1;
        min-height: 200px;
        position: relative;
        background: #ffffff;
        overflow: hidden;
    }
    #mx-wb-canvas-wrap canvas { position: absolute; inset: 0; width: 100%; height: 100%; }

    /* Resize handle */
    #mx-wb-resize {
        position: absolute;
        right: 8px;
        top: 50%;
        transform: translateY(-50%);
        cursor: ns-resize;
        color: rgba(148,163,184,0.4);
        font-size: 12px;
        user-select: none;
    }

    /* Text input popup */
    #mx-text-popup {
        display: none;
        position: absolute;
        background: rgba(15,23,42,0.97);
        border: 1px solid rgba(56,189,248,0.4);
        border-radius: 10px;
        padding: 10px;
        z-index: 300;
        box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        min-width: 220px;
    }
    #mx-text-popup input {
        width: 100%;
        background: rgba(30,41,59,0.9);
        border: 1px solid rgba(148,163,184,0.25);
        border-radius: 7px;
        color: #e2e8f0;
        padding: 7px 10px;
        font-size: 14px;
        outline: none;
    }
    #mx-text-popup input:focus { border-color: rgba(56,189,248,0.5); }
    #mx-text-popup .mx-text-btns {
        display: flex;
        gap: 6px;
        margin-top: 7px;
    }
    #mx-text-popup .mx-text-btns button {
        flex: 1;
        padding: 5px;
        border-radius: 7px;
        font-size: 12px;
        cursor: pointer;
        border: none;
    }
    #mx-text-add { background: rgba(14,165,233,0.3); color: #38bdf8; }
    #mx-text-cancel { background: rgba(71,85,105,0.4); color: #94a3b8; }

    /* Grid / Lined background */
    .mx-bg-grid { background: #ffffff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='40' height='40'%3E%3Cpath d='M 40 0 L 0 0 0 40' fill='none' stroke='%23e2e8f0' stroke-width='0.7'/%3E%3C/svg%3E"); }
    .mx-bg-lined { background: #ffffff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100%25' height='40'%3E%3Cline x1='0' y1='39' x2='100%25' y2='39' stroke='%23bfdbfe' stroke-width='1'/%3E%3C/svg%3E"); }
    .mx-bg-dark { background: #1e293b; }
    .mx-bg-green { background: #166534; }
</style>


<div id="mx-tools-fab">
    <div id="mx-tools-menu">
        <button class="mx-menu-item" id="mx-btn-annotate">
            <span class="mx-menu-icon" style="background:rgba(234,179,8,0.15); color:#fbbf24;">
                <i class="fas fa-pen-nib"></i>
            </span>
            <span>رسم على الفيديو</span>
        </button>
        <button class="mx-menu-item" id="mx-btn-board">
            <span class="mx-menu-icon" style="background:rgba(14,165,233,0.15); color:#38bdf8;">
                <i class="fas fa-chalkboard"></i>
            </span>
            <span>السبورة البيضاء</span>
        </button>
    </div>
    <button id="mx-fab-main" type="button">
        <i class="fas fa-paintbrush"></i>
        <span>أدوات</span>
        <i class="fas fa-chevron-up" id="mx-fab-chevron"></i>
    </button>
</div>


<div id="mx-annotation-overlay">
    <canvas id="mx-annotation-canvas"></canvas>
    <div id="mx-annotation-toolbar">
        <button class="mx-ann-btn active" id="mx-ann-pen" title="قلم"><i class="fas fa-pen"></i></button>
        <button class="mx-ann-btn" id="mx-ann-line" title="خط"><i class="fas fa-minus"></i></button>
        <button class="mx-ann-btn" id="mx-ann-arrow" title="سهم"><i class="fas fa-arrow-right"></i></button>
        <button class="mx-ann-btn" id="mx-ann-rect" title="مستطيل"><i class="far fa-square"></i></button>
        <button class="mx-ann-btn" id="mx-ann-circle" title="دائرة"><i class="far fa-circle"></i></button>
        <button class="mx-ann-btn" id="mx-ann-eraser" title="ممحاة"><i class="fas fa-eraser"></i></button>
        <div class="mx-ann-sep"></div>
        <input type="color" class="mx-ann-color" id="mx-ann-color" value="#ef4444" title="اللون">
        <input type="range" id="mx-ann-size" min="1" max="20" value="4" title="الحجم">
        <div class="mx-ann-sep"></div>
        <button class="mx-ann-btn" id="mx-ann-undo" title="تراجع"><i class="fas fa-rotate-left"></i></button>
        <button class="mx-ann-btn" id="mx-ann-clear" title="مسح الكل"><i class="fas fa-trash"></i></button>
        <div class="mx-ann-sep"></div>
        <button id="mx-ann-close-btn"><i class="fas fa-xmark"></i> إغلاق</button>
    </div>
</div>


<div id="mx-whiteboard-panel">
    <div class="mx-panel-header">
        <div class="mx-panel-title">
            <i class="fas fa-chalkboard"></i>
            <span>السبورة التفاعلية</span>
        </div>
        <div style="display:flex; gap:6px; align-items:center;">
            <div id="mx-wb-resize" title="تغيير الحجم"><i class="fas fa-arrows-up-down"></i></div>
            <button id="mx-wb-minimize" class="mx-tool-btn" title="تصغير" style="width:28px;height:28px;font-size:11px;"><i class="fas fa-minus"></i></button>
            <button id="mx-wb-close" class="mx-tool-btn" title="إغلاق" style="width:28px;height:28px;font-size:11px;color:#f87171;"><i class="fas fa-xmark"></i></button>
        </div>
    </div>

    <div class="mx-wb-toolbar">
        
        <div class="mx-tool-group">
            <button class="mx-tool-btn is-active" data-tool="select" title="تحديد"><i class="fas fa-mouse-pointer"></i></button>
            <button class="mx-tool-btn" data-tool="draw" title="قلم حر"><i class="fas fa-pen"></i></button>
            <button class="mx-tool-btn" data-tool="eraser" title="ممحاة"><i class="fas fa-eraser"></i></button>
        </div>
        
        <div class="mx-tool-group">
            <button class="mx-tool-btn" data-tool="line" title="خط مستقيم"><i class="fas fa-minus"></i></button>
            <button class="mx-tool-btn" data-tool="rect" title="مستطيل"><i class="far fa-square"></i></button>
            <button class="mx-tool-btn" data-tool="circle" title="دائرة / بيضاوي"><i class="far fa-circle"></i></button>
            <button class="mx-tool-btn" data-tool="triangle" title="مثلث"><i class="fas fa-play fa-rotate-270"></i></button>
            <button class="mx-tool-btn" data-tool="arrow" title="سهم"><i class="fas fa-arrow-right"></i></button>
        </div>
        
        <div class="mx-tool-group">
            <button class="mx-tool-btn" data-tool="text" title="إضافة نص"><i class="fas fa-font"></i></button>
        </div>
        
        <div class="mx-tool-group">
            <input type="color" class="mx-color-input" id="mx-stroke-color" value="#0f172a" title="لون الخط / النص">
            <input type="color" class="mx-color-input" id="mx-fill-color" value="#ffffff" title="لون التعبئة">
            <div class="mx-size-wrap">
                <i class="fas fa-circle-dot" style="font-size:9px;"></i>
                <input type="range" id="mx-stroke-width" min="1" max="24" value="3">
                <i class="fas fa-circle" style="font-size:13px;"></i>
            </div>
        </div>
        
        <div class="mx-tool-group mx-bg-switcher">
            <button class="mx-bg-btn is-active" data-bg="white" style="background:#fff;" title="أبيض"></button>
            <button class="mx-bg-btn" data-bg="grid" style="background:linear-gradient(#e2e8f0 1px,transparent 1px),linear-gradient(90deg,#e2e8f0 1px,transparent 1px),#fff;background-size:20px 20px;" title="شبكة"></button>
            <button class="mx-bg-btn" data-bg="lined" style="background:repeating-linear-gradient(#fff,#fff 34px,#bfdbfe 35px,#bfdbfe 35px);" title="مسطرة"></button>
            <button class="mx-bg-btn" data-bg="dark" style="background:#1e293b;" title="داكن"></button>
            <button class="mx-bg-btn" data-bg="green" style="background:#166534;" title="سبورة خضراء"></button>
        </div>
        
        <div class="mx-tool-group">
            <button class="mx-tool-btn" id="mx-undo" title="تراجع"><i class="fas fa-rotate-left"></i></button>
            <button class="mx-tool-btn" id="mx-redo" title="إعادة"><i class="fas fa-rotate-right"></i></button>
            <button class="mx-tool-btn" id="mx-clear" title="مسح الكل" style="color:#f87171;"><i class="fas fa-trash-alt"></i></button>
            <button class="mx-tool-btn" id="mx-download" title="حفظ كصورة"><i class="fas fa-download"></i></button>
        </div>
    </div>

    <div id="mx-wb-canvas-wrap">
        <canvas id="mx-whiteboard-canvas"></canvas>
        
        <div id="mx-text-popup">
            <input type="text" id="mx-text-input" placeholder="اكتب النص هنا..." autofocus>
            <div class="mx-text-btns">
                <button id="mx-text-add"><i class="fas fa-check"></i> إضافة</button>
                <button id="mx-text-cancel"><i class="fas fa-xmark"></i> إلغاء</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.1/fabric.min.js"></script>
<script>
(function () {
    /* ══════════════════════════════════════════════════════════
       FAB DROPDOWN
    ══════════════════════════════════════════════════════════ */
    var fabMain   = document.getElementById('mx-fab-main');
    var toolsMenu = document.getElementById('mx-tools-menu');
    var btnAnnotate = document.getElementById('mx-btn-annotate');
    var btnBoard    = document.getElementById('mx-btn-board');

    fabMain.addEventListener('click', function(e) {
        e.stopPropagation();
        toolsMenu.classList.toggle('is-open');
        fabMain.classList.toggle('is-open');
    });
    document.addEventListener('click', function() {
        toolsMenu.classList.remove('is-open');
        fabMain.classList.remove('is-open');
    });

    /* ══════════════════════════════════════════════════════════
       ANNOTATION OVERLAY  (رسم على الفيديو)
    ══════════════════════════════════════════════════════════ */
    var annOverlay   = document.getElementById('mx-annotation-overlay');
    var annCanvas    = document.getElementById('mx-annotation-canvas');
    var annCtx       = annCanvas.getContext('2d');
    var annTool      = 'pen';
    var annDrawing   = false;
    var annLastX     = 0, annLastY = 0;
    var annStartX    = 0, annStartY = 0;
    var annSnapshot  = null;
    var annHistory   = [];

    function resizeAnnCanvas() {
        var rect = annOverlay.getBoundingClientRect();
        var imageData = annCtx.getImageData(0, 0, annCanvas.width, annCanvas.height);
        annCanvas.width  = rect.width;
        annCanvas.height = rect.height;
        annCtx.putImageData(imageData, 0, 0);
    }

    function annColor() { return document.getElementById('mx-ann-color').value; }
    function annSize()  { return parseInt(document.getElementById('mx-ann-size').value, 10); }

    function annSaveHistory() {
        annHistory.push(annCtx.getImageData(0, 0, annCanvas.width, annCanvas.height));
        if (annHistory.length > 30) annHistory.shift();
    }

    annCanvas.addEventListener('mousedown', function(e) {
        if (annTool === 'eraser' || annTool === 'pen') {
            annDrawing = true;
            annLastX = e.offsetX; annLastY = e.offsetY;
            annCtx.beginPath();
            annCtx.moveTo(annLastX, annLastY);
            annSaveHistory();
        } else {
            annDrawing = true;
            annStartX = e.offsetX; annStartY = e.offsetY;
            annSnapshot = annCtx.getImageData(0, 0, annCanvas.width, annCanvas.height);
        }
    });

    annCanvas.addEventListener('mousemove', function(e) {
        if (!annDrawing) return;
        var x = e.offsetX, y = e.offsetY;
        if (annTool === 'pen') {
            annCtx.strokeStyle = annColor();
            annCtx.lineWidth   = annSize();
            annCtx.lineCap     = 'round';
            annCtx.lineJoin    = 'round';
            annCtx.globalCompositeOperation = 'source-over';
            annCtx.lineTo(x, y);
            annCtx.stroke();
            annLastX = x; annLastY = y;
        } else if (annTool === 'eraser') {
            annCtx.globalCompositeOperation = 'destination-out';
            annCtx.lineWidth = annSize() * 4;
            annCtx.lineCap   = 'round';
            annCtx.lineTo(x, y);
            annCtx.stroke();
            annLastX = x; annLastY = y;
        } else {
            annCtx.putImageData(annSnapshot, 0, 0);
            annCtx.strokeStyle = annColor();
            annCtx.lineWidth   = annSize();
            annCtx.globalCompositeOperation = 'source-over';
            if (annTool === 'line') {
                annCtx.beginPath();
                annCtx.moveTo(annStartX, annStartY);
                annCtx.lineTo(x, y);
                annCtx.stroke();
            } else if (annTool === 'arrow') {
                drawAnnArrow(annStartX, annStartY, x, y);
            } else if (annTool === 'rect') {
                annCtx.beginPath();
                annCtx.strokeRect(annStartX, annStartY, x - annStartX, y - annStartY);
            } else if (annTool === 'circle') {
                var rx = Math.abs(x - annStartX) / 2, ry = Math.abs(y - annStartY) / 2;
                var cx = Math.min(annStartX, x) + rx, cy = Math.min(annStartY, y) + ry;
                annCtx.beginPath();
                annCtx.ellipse(cx, cy, rx, ry, 0, 0, 2 * Math.PI);
                annCtx.stroke();
            }
        }
    });

    annCanvas.addEventListener('mouseup', function() {
        if (annDrawing) { annDrawing = false; annCtx.beginPath(); annSaveHistory(); }
    });
    annCanvas.addEventListener('mouseleave', function() { annDrawing = false; annCtx.beginPath(); });

    function drawAnnArrow(x1, y1, x2, y2) {
        var headlen = 16 + annSize();
        var angle = Math.atan2(y2 - y1, x2 - x1);
        annCtx.beginPath();
        annCtx.moveTo(x1, y1);
        annCtx.lineTo(x2, y2);
        annCtx.stroke();
        annCtx.beginPath();
        annCtx.moveTo(x2, y2);
        annCtx.lineTo(x2 - headlen * Math.cos(angle - Math.PI/6), y2 - headlen * Math.sin(angle - Math.PI/6));
        annCtx.moveTo(x2, y2);
        annCtx.lineTo(x2 - headlen * Math.cos(angle + Math.PI/6), y2 - headlen * Math.sin(angle + Math.PI/6));
        annCtx.stroke();
    }

    function setAnnTool(tool) {
        annTool = tool;
        annCtx.globalCompositeOperation = 'source-over';
        document.querySelectorAll('#mx-annotation-toolbar .mx-ann-btn[id^="mx-ann-"]').forEach(function(b) {
            b.classList.remove('active');
        });
        var active = document.getElementById('mx-ann-' + tool);
        if (active) active.classList.add('active');
        annCanvas.style.cursor = (tool === 'eraser') ? 'cell' : 'crosshair';
    }

    ['pen','line','arrow','rect','circle','eraser'].forEach(function(t) {
        var btn = document.getElementById('mx-ann-' + t);
        if (btn) btn.addEventListener('click', function() { setAnnTool(t); });
    });

    document.getElementById('mx-ann-undo').addEventListener('click', function() {
        if (annHistory.length > 1) {
            annHistory.pop();
            annCtx.putImageData(annHistory[annHistory.length - 1], 0, 0);
        }
    });
    document.getElementById('mx-ann-clear').addEventListener('click', function() {
        annCtx.clearRect(0, 0, annCanvas.width, annCanvas.height);
        annHistory = [];
    });
    document.getElementById('mx-ann-close-btn').addEventListener('click', function() {
        annOverlay.classList.remove('is-open');
    });

    btnAnnotate && btnAnnotate.addEventListener('click', function() {
        toolsMenu.classList.remove('is-open');
        fabMain.classList.remove('is-open');
        annOverlay.classList.toggle('is-open');
        if (annOverlay.classList.contains('is-open')) {
            resizeAnnCanvas();
            if (!annHistory.length) annSaveHistory();
        }
    });

    /* ══════════════════════════════════════════════════════════
       FULL WHITEBOARD PANEL (fabric.js)
    ══════════════════════════════════════════════════════════ */
    var wbPanel   = document.getElementById('mx-whiteboard-panel');
    var wbClose   = document.getElementById('mx-wb-close');
    var wbCanvasEl = document.getElementById('mx-whiteboard-canvas');
    var wbWrap    = document.getElementById('mx-wb-canvas-wrap');
    if (!wbCanvasEl || !wbWrap || typeof fabric === 'undefined') return;

    var wbCanvas  = new fabric.Canvas(wbCanvasEl, { selection: true, preserveObjectStacking: true });
    var wbTool    = 'select';
    var wbDrawObj = null;
    var wbStart   = null;
    var wbUndo    = [];
    var wbRedo    = [];
    var wbRestoring = false;
    var wbBg      = 'white';
    var wbTextPending = null; // {x, y}

    var bgPatterns = {
        white: '#ffffff',
        grid:  '#ffffff',
        lined: '#ffffff',
        dark:  '#1e293b',
        green: '#166534'
    };

    function resizeWbCanvas() {
        var rect = wbWrap.getBoundingClientRect();
        wbCanvas.setDimensions({ width: Math.max(200, rect.width), height: Math.max(180, rect.height) });
        wbCanvas.requestRenderAll();
    }

    function saveWbState() {
        if (wbRestoring) return;
        wbUndo.push(JSON.stringify(wbCanvas.toJSON(['bgPattern'])));
        if (wbUndo.length > 60) wbUndo.shift();
        wbRedo = [];
    }

    function restoreWbState(from, to) {
        if (!from.length) return;
        wbRestoring = true;
        to.push(JSON.stringify(wbCanvas.toJSON(['bgPattern'])));
        var json = from.pop();
        wbCanvas.loadFromJSON(json, function() { wbCanvas.renderAll(); wbRestoring = false; });
    }

    function wbStrokeColor() { return document.getElementById('mx-stroke-color').value || '#0f172a'; }
    function wbFillColor()   { return document.getElementById('mx-fill-color').value || 'transparent'; }
    function wbStrokeWidth() { return parseInt(document.getElementById('mx-stroke-width').value || '3', 10); }

    function applyBg(bg) {
        wbBg = bg;
        var wrap = document.getElementById('mx-wb-canvas-wrap');
        wrap.className = 'mx-wb-canvas-wrap';
        if (bg === 'grid')  wrap.classList.add('mx-bg-grid');
        else if (bg === 'lined') wrap.classList.add('mx-bg-lined');
        else if (bg === 'dark')  wrap.classList.add('mx-bg-dark');
        else if (bg === 'green') wrap.classList.add('mx-bg-green');
        wbCanvas.setBackgroundColor(bgPatterns[bg] || '#ffffff', wbCanvas.renderAll.bind(wbCanvas));
        document.querySelectorAll('.mx-bg-btn').forEach(function(b) {
            b.classList.toggle('is-active', b.getAttribute('data-bg') === bg);
        });
        // Adjust default stroke for dark/green boards
        if (bg === 'dark' || bg === 'green') {
            document.getElementById('mx-stroke-color').value = '#ffffff';
        }
    }

    function activateWbTool(tool) {
        wbTool = tool;
        wbCanvas.isDrawingMode = (tool === 'draw' || tool === 'eraser');
        wbCanvas.selection = (tool === 'select');
        wbCanvas.forEachObject(function(o) { o.selectable = (tool === 'select'); o.evented = (tool === 'select'); });
        if (tool === 'draw' || tool === 'eraser') {
            wbCanvas.freeDrawingBrush = new fabric.PencilBrush(wbCanvas);
            wbCanvas.freeDrawingBrush.width = tool === 'eraser' ? Math.max(14, wbStrokeWidth() * 4) : wbStrokeWidth();
            wbCanvas.freeDrawingBrush.color = tool === 'eraser' ? (bgPatterns[wbBg] || '#ffffff') : wbStrokeColor();
        }
        document.querySelectorAll('#mx-whiteboard-panel [data-tool]').forEach(function(b) {
            b.classList.toggle('is-active', b.getAttribute('data-tool') === tool);
        });
        // Cursor
        wbCanvasEl.style.cursor = (tool === 'text') ? 'text' : 'default';
    }

    function createWbShape(tool, x, y) {
        var opts = { left: x, top: y, stroke: wbStrokeColor(), strokeWidth: wbStrokeWidth(), fill: wbFillColor(), selectable: false, evented: false };
        if (tool === 'line' || tool === 'arrow') return new fabric.Line([x, y, x, y], Object.assign({}, opts, { fill: 'transparent' }));
        if (tool === 'rect')     return new fabric.Rect(Object.assign({}, opts, { width: 1, height: 1 }));
        if (tool === 'circle')   return new fabric.Ellipse(Object.assign({}, opts, { rx: 1, ry: 1 }));
        if (tool === 'triangle') return new fabric.Triangle(Object.assign({}, opts, { width: 1, height: 1 }));
        return null;
    }

    /* Text popup */
    var textPopup  = document.getElementById('mx-text-popup');
    var textInput  = document.getElementById('mx-text-input');
    var textAddBtn = document.getElementById('mx-text-add');
    var textCancel = document.getElementById('mx-text-cancel');

    function showTextPopup(x, y) {
        wbTextPending = { x: x, y: y };
        textInput.value = '';
        textPopup.style.left = Math.min(x, wbWrap.clientWidth - 250) + 'px';
        textPopup.style.top  = Math.max(0, y - 10) + 'px';
        textPopup.style.display = 'block';
        setTimeout(function() { textInput.focus(); }, 50);
    }

    function addTextObject() {
        if (!wbTextPending || !textInput.value.trim()) { textPopup.style.display = 'none'; return; }
        var t = new fabric.IText(textInput.value.trim(), {
            left: wbTextPending.x, top: wbTextPending.y,
            fontSize: 22, fill: wbStrokeColor(),
            fontFamily: 'IBM Plex Sans Arabic, sans-serif',
            selectable: (wbTool === 'select'), editable: true
        });
        wbCanvas.add(t).setActiveObject(t);
        textPopup.style.display = 'none';
        wbTextPending = null;
        saveWbState();
    }

    textAddBtn && textAddBtn.addEventListener('click', addTextObject);
    textCancel && textCancel.addEventListener('click', function() {
        textPopup.style.display = 'none';
        wbTextPending = null;
    });
    textInput && textInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') addTextObject();
        if (e.key === 'Escape') { textPopup.style.display = 'none'; wbTextPending = null; }
    });

    wbCanvas.on('mouse:down', function(opt) {
        var p = wbCanvas.getPointer(opt.e);
        if (wbTool === 'text') { showTextPopup(p.x, p.y); return; }
        if (!['line','rect','circle','triangle','arrow'].includes(wbTool)) return;
        wbStart = p;
        wbDrawObj = createWbShape(wbTool, p.x, p.y);
        if (wbDrawObj) wbCanvas.add(wbDrawObj);
    });

    wbCanvas.on('mouse:move', function(opt) {
        if (!wbDrawObj || !wbStart) return;
        var p = wbCanvas.getPointer(opt.e);
        if (wbTool === 'line' || wbTool === 'arrow') {
            wbDrawObj.set({ x2: p.x, y2: p.y });
        } else if (wbTool === 'rect' || wbTool === 'triangle') {
            wbDrawObj.set({ left: Math.min(wbStart.x, p.x), top: Math.min(wbStart.y, p.y), width: Math.abs(wbStart.x - p.x), height: Math.abs(wbStart.y - p.y) });
        } else if (wbTool === 'circle') {
            var rx = Math.abs(p.x - wbStart.x) / 2, ry = Math.abs(p.y - wbStart.y) / 2;
            wbDrawObj.set({ left: Math.min(wbStart.x, p.x), top: Math.min(wbStart.y, p.y), rx: rx, ry: ry });
        }
        wbCanvas.requestRenderAll();
    });

    wbCanvas.on('mouse:up', function() {
        if (!wbDrawObj) return;
        if (wbTool === 'arrow') {
            var line = wbDrawObj;
            var angle = Math.atan2(line.y2 - line.y1, line.x2 - line.x1);
            var sz = 14 + wbStrokeWidth();
            var head = new fabric.Triangle({ left: line.x2, top: line.y2, originX: 'center', originY: 'center', width: sz, height: sz + 4, fill: wbStrokeColor(), angle: (angle * 180 / Math.PI) + 90, selectable: false, evented: false });
            var grp = new fabric.Group([line, head], { selectable: false, evented: false });
            wbCanvas.remove(line);
            wbCanvas.add(grp);
        }
        wbDrawObj = null; wbStart = null;
        saveWbState();
    });

    wbCanvas.on('path:created', saveWbState);
    wbCanvas.on('object:modified', saveWbState);

    /* Tool buttons */
    document.querySelectorAll('#mx-whiteboard-panel [data-tool]').forEach(function(btn) {
        btn.addEventListener('click', function() { activateWbTool(btn.getAttribute('data-tool')); });
    });

    /* Background switcher */
    document.querySelectorAll('.mx-bg-btn').forEach(function(btn) {
        btn.addEventListener('click', function() { applyBg(btn.getAttribute('data-bg')); });
    });

    /* Color/size live update */
    document.getElementById('mx-stroke-color').addEventListener('input', function() {
        if (wbTool === 'draw' || wbTool === 'eraser') activateWbTool(wbTool);
        var obj = wbCanvas.getActiveObject();
        if (obj && wbTool === 'select') {
            obj.set({ stroke: wbStrokeColor() });
            if (obj.type === 'i-text') obj.set('fill', wbStrokeColor());
            wbCanvas.requestRenderAll(); saveWbState();
        }
    });
    document.getElementById('mx-fill-color').addEventListener('input', function() {
        var obj = wbCanvas.getActiveObject();
        if (obj && wbTool === 'select' && obj.type !== 'line' && obj.type !== 'i-text') {
            obj.set('fill', wbFillColor());
            wbCanvas.requestRenderAll(); saveWbState();
        }
    });
    document.getElementById('mx-stroke-width').addEventListener('input', function() {
        if (wbTool === 'draw' || wbTool === 'eraser') activateWbTool(wbTool);
        var obj = wbCanvas.getActiveObject();
        if (obj && wbTool === 'select') {
            obj.set('strokeWidth', wbStrokeWidth());
            wbCanvas.requestRenderAll(); saveWbState();
        }
    });

    /* Undo / Redo / Clear / Download */
    document.getElementById('mx-undo').addEventListener('click', function() { restoreWbState(wbUndo, wbRedo); });
    document.getElementById('mx-redo').addEventListener('click', function() { restoreWbState(wbRedo, wbUndo); });
    document.getElementById('mx-clear').addEventListener('click', function() {
        if (!confirm('هل تريد مسح اللوحة كاملاً؟')) return;
        wbCanvas.clear();
        applyBg(wbBg);
        saveWbState();
    });
    document.getElementById('mx-download').addEventListener('click', function() {
        var link = document.createElement('a');
        link.href = wbCanvas.toDataURL({ format: 'png', multiplier: 2 });
        link.download = 'muallimx-board-' + Date.now() + '.png';
        link.click();
    });

    /* Open / Close panel */
    function openWbPanel() {
        wbPanel.classList.add('is-open');
        resizeWbCanvas();
        applyBg(wbBg);
        if (!wbUndo.length) { saveWbState(); }
    }
    function closeWbPanel() {
        wbPanel.classList.remove('is-open');
        textPopup.style.display = 'none';
    }

    btnBoard && btnBoard.addEventListener('click', function() {
        toolsMenu.classList.remove('is-open');
        fabMain.classList.remove('is-open');
        if (wbPanel.classList.contains('is-open')) closeWbPanel();
        else openWbPanel();
    });
    wbClose && wbClose.addEventListener('click', closeWbPanel);
    document.getElementById('mx-wb-minimize') && document.getElementById('mx-wb-minimize').addEventListener('click', function() {
        wbPanel.style.height = wbPanel.style.height === '42px' ? '' : '42px';
    });

    /* Resize panel by dragging the resize handle */
    (function() {
        var resizeHandle = document.getElementById('mx-wb-resize');
        if (!resizeHandle) return;
        var dragging = false, startY = 0, startH = 0;
        resizeHandle.addEventListener('mousedown', function(e) {
            dragging = true; startY = e.clientY;
            startH = wbPanel.offsetHeight;
            e.preventDefault();
        });
        document.addEventListener('mousemove', function(e) {
            if (!dragging) return;
            var newH = Math.max(300, Math.min(window.innerHeight - 100, startH - (e.clientY - startY)));
            wbPanel.style.height = newH + 'px';
            resizeWbCanvas();
        });
        document.addEventListener('mouseup', function() { dragging = false; });
    })();

    window.addEventListener('resize', function() { resizeAnnCanvas(); if (wbPanel.classList.contains('is-open')) resizeWbCanvas(); });
    activateWbTool('select');
    applyBg('white');
    saveWbState();
})();
</script>
<?php /**PATH C:\xampp\htdocs\Muallimx\resources\views\partials\live-whiteboard.blade.php ENDPATH**/ ?>