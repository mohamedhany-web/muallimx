<?php

$blade = file_get_contents(__DIR__.'/../resources/views/student/classroom/room.blade.php');
$css = file_get_contents(__DIR__.'/../public/css/classroom-meetline.css');

$checks = [
    'startLectureRecording' => substr_count($blade, 'function startLectureRecording'),
    'attachLectureDisplayStream' => substr_count($blade, 'function attachLectureDisplayStream'),
    'uploadRecordedBlob' => substr_count($blade, 'function uploadRecordedBlob'),
    'mxQueueBlobUpload' => substr_count($blade, 'function mxQueueBlobUpload'),
    'video_webm_default' => substr_count($blade, "blob.type || 'video/webm'"),
    'require_screen' => substr_count($blade, 'attachLectureDisplayStream(true)'),
    'mx_rec_live_badge' => substr_count($blade, 'mx-rec-live-badge'),
    'inline_meetline_css' => substr_count($blade, 'mx-classroom-meetline-css'),
    'meetline_fixed_shell' => substr_count($css, 'position: fixed'),
];

$fail = 0;
foreach ($checks as $k => $v) {
    $ok = $v > 0;
    echo ($ok ? 'OK' : 'FAIL')." {$k}={$v}\n";
    if (! $ok) {
        $fail++;
    }
}

$open = substr_count($blade, '{');
$close = substr_count($blade, '}');
echo "braces open={$open} close={$close} delta=".($open - $close)."\n";

exit($fail > 0 ? 1 : 0);
