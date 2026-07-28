<?php

$ctrl = file_get_contents(__DIR__.'/../public/js/classroom-share-controls.js');
$zoom = file_get_contents(__DIR__.'/../public/js/classroom-share-zoom.js');
$css = file_get_contents(__DIR__.'/../public/css/classroom-meetline.css');
$room = file_get_contents(__DIR__.'/../resources/views/student/classroom/room.blade.php');
$join = file_get_contents(__DIR__.'/../resources/views/classroom/join.blade.php');
$pip = file_get_contents(__DIR__.'/../resources/views/student/classroom/room-pip.blade.php');

$checks = [
    'ctrl_module' => str_contains($ctrl, 'MxClassroomShareControls'),
    'ctrl_float' => str_contains($ctrl, 'bindShareFloat'),
    'ctrl_preserve_audio' => str_contains($ctrl, 'preserveReceiveAudio'),
    'ctrl_pip_bind_parent' => str_contains($ctrl, 'querySelectorAll(\'[data-act]\')') || str_contains($ctrl, 'querySelectorAll("[data-act]")'),
    'zoom_module' => str_contains($zoom, 'MxClassroomShareZoom'),
    'zoom_pinch' => str_contains($zoom, 'pinchStartScale'),
    'css_share_float' => str_contains($css, '#mx-share-float'),
    'css_sharing_body' => str_contains($css, 'body.mx-sharing'),
    'room_inline_ctrl' => str_contains($room, 'mx-classroom-share-controls-js'),
    'room_float_html' => str_contains($room, 'id="mx-share-float"'),
    'room_show_float' => str_contains($room, '__mxShareFloatUi.show'),
    'room_no_auto_pip_share' => ! str_contains($room, "mxOpenParticipantsPip({ reason: 'share' })"),
    'room_no_auto_blur_pip' => ! str_contains($room, "mxOpenParticipantsPip({ reason: 'blur' })"),
    'room_preserve_audio' => str_contains($room, 'preserveReceiveAudio'),
    'pip_no_recorder' => ! str_contains($pip, 'iAmRecorder: true'),
    'join_zoom_inline' => str_contains($join, 'mx-classroom-share-zoom-js'),
    'join_zoom_viewport' => str_contains($join, 'mx-share-zoom-viewport'),
    'join_zoom_hud' => str_contains($join, 'mx-share-zoom-hud'),
    'join_bind_zoom' => str_contains($join, 'MxClassroomShareZoom.bind'),
];

$fail = 0;
foreach ($checks as $k => $ok) {
    echo ($ok ? 'OK' : 'FAIL')." {$k}\n";
    if (! $ok) {
        $fail++;
    }
}

exit($fail > 0 ? 1 : 0);
