<?php

$blade = file_get_contents(__DIR__.'/../resources/views/student/classroom/room.blade.php');
$show = file_get_contents(__DIR__.'/../resources/views/student/classroom/show.blade.php');
$ctrl = file_get_contents(__DIR__.'/../app/Http/Controllers/Student/ClassroomController.php');
$routes = file_get_contents(__DIR__.'/../routes/web.php');

$checks = [
    'room_pct_el' => substr_count($blade, 'mx-upload-modal-pct'),
    'room_end_overlay' => substr_count($blade, 'mx-end-upload-overlay'),
    'room_force_in_page' => substr_count($blade, 'forceInPage'),
    'room_submit_when_ready' => substr_count($blade, 'mxSubmitEndMeetingWhenReady'),
    'room_mark_processing' => substr_count($blade, 'mxMarkRecordingProcessing'),
    'room_block_unload' => substr_count($blade, 'mxBlockUnloadForUpload'),
    'show_poll' => substr_count($show, 'data-status-url'),
    'show_cloudflare' => substr_count($show, 'Cloudflare'),
    'ctrl_status' => substr_count($ctrl, 'function recordingStatus'),
    'ctrl_processing' => substr_count($ctrl, 'function markRecordingProcessing'),
    'route_status' => substr_count($routes, 'classroom.recording.status'),
    'route_processing' => substr_count($routes, 'classroom.recording.processing'),
];

$fail = 0;
foreach ($checks as $k => $v) {
    if ($v < 1) {
        echo "FAIL $k=$v\n";
        $fail++;
    } else {
        echo "OK $k=$v\n";
    }
}
exit($fail > 0 ? 1 : 0);
