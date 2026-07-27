<?php

$blade = file_get_contents(__DIR__.'/../resources/views/student/classroom/room.blade.php');
$css = file_get_contents(__DIR__.'/../public/css/classroom-meetline.css');

$checks = [
    'startLectureRecording' => substr_count($blade, 'function startLectureRecording'),
    'attachLectureDisplayStream' => substr_count($blade, 'function attachLectureDisplayStream'),
    'mxBuildLectureMixedAudioTrack' => substr_count($blade, 'function mxBuildLectureMixedAudioTrack'),
    'mxReconnectLectureAudioSources' => substr_count($blade, 'function mxReconnectLectureAudioSources'),
    'share_tab_audio_hint' => substr_count($blade, 'Share tab audio'),
    'suppress_local_audio_playback' => substr_count($blade, 'suppressLocalAudioPlayback'),
    'display_audio_gain' => substr_count($blade, 'displayGain.gain.value = 1.25'),
    'mix_display_and_mic' => substr_count($blade, 'mxBuildLectureMixedAudioTrack(micStream, lectureDisplayStream)'),
    'reconnect_on_add_screen' => substr_count($blade, 'mxReconnectLectureAudioSources()'),
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
