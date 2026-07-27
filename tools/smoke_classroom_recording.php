<?php

$blade = file_get_contents(__DIR__.'/../resources/views/student/classroom/room.blade.php');
$css = file_get_contents(__DIR__.'/../public/css/classroom-meetline.css');
$webhook = file_get_contents(__DIR__.'/../app/Http/Controllers/Api/ClassroomRecordingWebhookController.php');
$docs = file_get_contents(__DIR__.'/../docs/classroom-jibri-auto-recording.md');

$checks = [
    'startLectureRecording' => preg_match('/function startLectureRecording\s*\(/', $blade) ? 1 : 0,
    'mxStartJitsiFileRecording' => substr_count($blade, 'function mxStartJitsiFileRecording'),
    'mxStopJitsiFileRecording' => substr_count($blade, 'function mxStopJitsiFileRecording'),
    'startRecording_file' => substr_count($blade, "mode: 'file'"),
    'lectureCaptureMode_jitsi' => substr_count($blade, "lectureCaptureMode = 'jitsi'"),
    'no_share_tab_alert' => substr_count($blade, 'Share tab audio') === 0 ? 1 : 0,
    'auto_copy' => substr_count($blade, 'تسجيل تلقائي'),
    'fileRecordingsServiceEnabled' => substr_count($blade, 'fileRecordingsServiceEnabled: true'),
    'attachLectureDisplayStream' => substr_count($blade, 'function attachLectureDisplayStream'),
    'browser_fallback' => substr_count($blade, 'function startLectureRecordingBrowserFallback'),
    'uploadRecordedBlob' => substr_count($blade, 'function uploadRecordedBlob'),
    'mxQueueBlobUpload' => substr_count($blade, 'function mxQueueBlobUpload'),
    'mx_rec_live_badge' => substr_count($blade, 'mx-rec-live-badge'),
    'inline_meetline_css' => substr_count($blade, 'mx-classroom-meetline-css'),
    'meetline_fixed_shell' => substr_count($css, 'position: fixed'),
    'webhook_controller' => substr_count($webhook, 'class ClassroomRecordingWebhookController'),
    'docs_jibri' => substr_count($docs, 'classroom-recordings/register'),
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
if ($open !== $close) {
    $fail++;
}

exit($fail > 0 ? 1 : 0);
